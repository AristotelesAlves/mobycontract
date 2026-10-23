<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\Http\Controllers;

use App\Modules\Imoveis\Models\Imovel;
use App\Modules\Imoveis\Models\ImovelFoto;
use App\Shared\Application\Exceptions\RegraNegocioException;
use App\Shared\Contracts\ArmazenadorDeArquivo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Throwable;

final readonly class ImovelFotoController
{
    public function __construct(private ArmazenadorDeArquivo $arquivos) {}

    public function index(int $id): JsonResponse
    {
        Imovel::query()->findOrFail($id);

        return response()->json(['dados' => ImovelFoto::query()->where('imovel_id', $id)
            ->orderBy('id')->get(['id', 'nome'])->all()]);
    }

    public function store(Request $request, int $id): JsonResponse
    {
        Imovel::query()->findOrFail($id);
        $request->validate(['foto' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=10000,max_height=10000']]);
        $foto = $request->file('foto');
        assert($foto instanceof UploadedFile);
        $conteudo = $foto->get();
        if ($conteudo === false) {
            throw new RegraNegocioException('Não foi possível ler a imagem.');
        }
        $caminho = $this->arquivos->guardar('imoveis/'.$id, 'foto.'.$foto->extension(), $conteudo);
        try {
            $registro = ImovelFoto::query()->create([
                'imovel_id' => $id, 'caminho' => $caminho,
                'nome' => mb_substr(basename($foto->getClientOriginalName()), 0, 200),
                'mime' => $foto->getMimeType(),
            ]);
        } catch (Throwable $erro) {
            $this->arquivos->excluir($caminho);
            throw $erro;
        }

        return response()->json(['dados' => ['id' => $registro->id, 'nome' => $registro->nome]], 201);
    }

    public function show(int $id, int $fotoId): Response
    {
        $foto = ImovelFoto::query()->where('imovel_id', $id)->findOrFail($fotoId);

        return response($this->arquivos->ler($foto->caminho), 200, [
            'Content-Type' => $foto->mime,
            'Content-Disposition' => 'inline; filename="foto-'.$foto->id.'.'.pathinfo($foto->caminho, PATHINFO_EXTENSION).'"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function destroy(int $id, int $fotoId): Response
    {
        $foto = ImovelFoto::query()->where('imovel_id', $id)->findOrFail($fotoId);
        $this->arquivos->excluir($foto->caminho);
        $foto->delete();

        return response('', 204);
    }
}
