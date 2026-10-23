<?php

declare(strict_types=1);

namespace App\Modules\Vistorias\Http\Controllers;

use App\Modules\Vistorias\Models\Vistoria;
use App\Modules\Vistorias\Models\VistoriaFoto;
use App\Shared\Contracts\ArmazenadorDeArquivo;
use Illuminate\Http\Response;

final readonly class VistoriaArquivoController
{
    public function __construct(private ArmazenadorDeArquivo $arquivos) {}

    public function __invoke(int $contratoId, int $vistoriaId, int $fotoId): Response
    {
        Vistoria::query()->where('contrato_id', $contratoId)->findOrFail($vistoriaId);
        $foto = VistoriaFoto::query()->where('vistoria_id', $vistoriaId)->findOrFail($fotoId);
        abort_if($foto->anexo === null, 404, 'Arquivo não disponível.');
        $conteudo = $this->arquivos->ler($foto->anexo);
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($conteudo);
        abort_unless(in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true), 422, 'Formato não permitido.');

        return response($conteudo, 200, [
            'Content-Type' => $mime, 'Content-Disposition' => 'attachment; filename="foto-vistoria-'.$foto->id.'"',
            'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store',
        ]);
    }
}
