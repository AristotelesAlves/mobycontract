<?php

declare(strict_types=1);

namespace App\Modules\Vistorias\Http\Controllers;

use App\Modules\Vistorias\Contracts\VistoriaServiceInterface;
use App\Modules\Vistorias\DTOs\Input\AlterarStatusVistoriaData;
use App\Modules\Vistorias\DTOs\Input\AlterarVistoriaData;
use App\Modules\Vistorias\DTOs\Input\CriarVistoriaData;
use App\Modules\Vistorias\DTOs\Input\ListarVistoriasFiltros;
use App\Modules\Vistorias\Http\Requests\AlterarStatusVistoriaRequest;
use App\Modules\Vistorias\Http\Requests\AlterarVistoriaRequest;
use App\Modules\Vistorias\Http\Requests\CriarVistoriaRequest;
use App\Modules\Vistorias\Http\Requests\ExcluirVistoriaRequest;
use App\Modules\Vistorias\Http\Requests\ListarVistoriasRequest;
use App\Modules\Vistorias\Http\Requests\MostrarVistoriaRequest;
use App\Modules\Vistorias\Http\Requests\RegistrarFotoVistoriaRequest;
use App\Modules\Vistorias\Http\Resources\VistoriaFotoResource;
use App\Modules\Vistorias\Http\Resources\VistoriaResource;
use App\Shared\Application\Controllers\ApiController;
use App\Shared\Application\Exceptions\RecursoNaoEncontradoException;
use App\Shared\Application\Support\AnexoDeRequisicao;
use App\Shared\Contracts\ArmazenadorDeArquivo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Throwable;

final class VistoriaController extends ApiController
{
    public function __construct(
        private readonly VistoriaServiceInterface $vistorias,
        private readonly ArmazenadorDeArquivo $armazenador,
    ) {}

    public function index(ListarVistoriasRequest $request): JsonResponse
    {
        try {
            $this->validarVinculo($request);
            $filtros = ListarVistoriasFiltros::fromValidated(
                (int) $request->route('contratoId'),
                $request->validated(),
            );

            return $this->respostaLista($request, $this->vistorias->listar($filtros), VistoriaResource::class);
        } catch (Throwable $erro) {
            return $this->falha($erro, 'vistorias.listar');
        }
    }

    public function show(MostrarVistoriaRequest $request): JsonResponse
    {
        try {
            $this->validarVinculo($request);
            $vistoria = $this->vistorias->obter((int) $request->route('vistoriaId'));

            return $this->resposta($request, new VistoriaResource($vistoria));
        } catch (Throwable $erro) {
            return $this->falha($erro, 'vistorias.obter');
        }
    }

    public function porChaveDeAcesso(MostrarVistoriaRequest $request): JsonResponse
    {
        try {
            $this->validarVinculo($request);
            $vistoria = $this->vistorias->obterPorChaveDeAcesso(
                (string) $request->route('chave'),
            );

            return $this->resposta($request, new VistoriaResource($vistoria));
        } catch (Throwable $erro) {
            return $this->falha($erro, 'vistorias.acesso_publica');
        }
    }

    public function store(CriarVistoriaRequest $request): JsonResponse
    {
        try {
            $this->validarVinculo($request);
            $dados = CriarVistoriaData::fromValidated(
                (int) $request->route('contratoId'),
                $request->validated(),
            );

            $vistoria = $this->vistorias->armazenar($dados);

            return $this->resposta($request, new VistoriaResource($vistoria), HttpResponse::HTTP_CREATED);
        } catch (Throwable $erro) {
            return $this->falha($erro, 'vistorias.criar');
        }
    }

    public function update(AlterarVistoriaRequest $request): JsonResponse
    {
        try {
            $this->validarVinculo($request);
            $dados = AlterarVistoriaData::fromValidated($request->validated());

            $vistoria = $this->vistorias->alterar((int) $request->route('vistoriaId'), $dados);

            return $this->resposta($request, new VistoriaResource($vistoria));
        } catch (Throwable $erro) {
            return $this->falha($erro, 'vistorias.alterar');
        }
    }

    public function alterarStatus(AlterarStatusVistoriaRequest $request): JsonResponse
    {
        try {
            $this->validarVinculo($request);
            $dados = AlterarStatusVistoriaData::fromValidated($request->validated());

            $vistoria = $this->vistorias->alterarStatus((int) $request->route('vistoriaId'), $dados);

            return $this->resposta($request, new VistoriaResource($vistoria));
        } catch (Throwable $erro) {
            return $this->falha($erro, 'vistorias.alterar_status');
        }
    }

    public function destroy(ExcluirVistoriaRequest $request): JsonResponse
    {
        try {
            $this->validarVinculo($request);
            $this->vistorias->excluir((int) $request->route('vistoriaId'));

            return new JsonResponse(status: HttpResponse::HTTP_NO_CONTENT);
        } catch (Throwable $erro) {
            return $this->falha($erro, 'vistorias.excluir');
        }
    }

    public function registrarFoto(RegistrarFotoVistoriaRequest $request): JsonResponse
    {
        try {
            $this->validarVinculo($request);
            $anexo = AnexoDeRequisicao::guardar($this->armazenador, 'vistorias', $request->file('anexo'));
            $nomeAnexo = $request->input('nomeAnexo');

            $foto = $this->vistorias->registrarFoto(
                (int) $request->route('vistoriaId'),
                is_string($nomeAnexo) && $nomeAnexo !== '' ? $nomeAnexo : $anexo['nome'],
                $anexo['anexo'],
                $request->filled('descricao') ? $request->string('descricao')->toString() : null,
            );

            return $this->resposta($request, new VistoriaFotoResource($foto), HttpResponse::HTTP_CREATED);
        } catch (Throwable $erro) {
            return $this->falha($erro, 'vistorias.registrar_foto');
        }
    }

    public function excluirFoto(MostrarVistoriaRequest $request): JsonResponse
    {
        try {
            $this->validarVinculo($request);
            $this->vistorias->excluirFoto(
                (int) $request->route('vistoriaId'),
                (int) $request->route('fotoId'),
            );

            return new JsonResponse(status: HttpResponse::HTTP_NO_CONTENT);
        } catch (Throwable $erro) {
            return $this->falha($erro, 'vistorias.excluir_foto');
        }
    }

    private function validarVinculo(Request $request): void
    {
        if ($request->route('vistoriaId') === null) {
            return;
        }
        $recurso = $this->vistorias->obter((int) $request->route('vistoriaId'));
        if ($recurso->contratoId !== (int) $request->route('contratoId')) {
            throw new RecursoNaoEncontradoException;
        }
    }
}
