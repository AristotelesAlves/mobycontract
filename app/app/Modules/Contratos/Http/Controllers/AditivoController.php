<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Http\Controllers;

use App\Modules\Contratos\Contracts\AditivoServiceInterface;
use App\Modules\Contratos\DTOs\Input\AlterarAditivoData;
use App\Modules\Contratos\DTOs\Input\AlterarStatusAditivoData;
use App\Modules\Contratos\DTOs\Input\CriarAditivoData;
use App\Modules\Contratos\DTOs\Input\ListarAditivosFiltros;
use App\Modules\Contratos\Http\Requests\AlterarAditivoRequest;
use App\Modules\Contratos\Http\Requests\AlterarStatusAditivoRequest;
use App\Modules\Contratos\Http\Requests\AssinarAditivoRequest;
use App\Modules\Contratos\Http\Requests\ConfirmarDocumentoAditivoRequest;
use App\Modules\Contratos\Http\Requests\CriarAditivoRequest;
use App\Modules\Contratos\Http\Requests\ExcluirAditivoRequest;
use App\Modules\Contratos\Http\Requests\ListarAditivosRequest;
use App\Modules\Contratos\Http\Requests\MostrarAditivoRequest;
use App\Modules\Contratos\Http\Requests\RegistrarBlockchainAditivoRequest;
use App\Modules\Contratos\Http\Resources\AditivoResource;
use App\Shared\Application\Controllers\ApiController;
use App\Shared\Application\Exceptions\RecursoNaoEncontradoException;
use App\Shared\Application\Support\AnexoDeRequisicao;
use App\Shared\Contracts\ArmazenadorDeArquivo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Throwable;

final class AditivoController extends ApiController
{
    public function __construct(
        private readonly AditivoServiceInterface $aditivos,
        private readonly ArmazenadorDeArquivo $armazenador,
    ) {}

    public function index(ListarAditivosRequest $request): JsonResponse
    {
        try {
            $this->validarVinculo($request);
            $filtros = ListarAditivosFiltros::fromValidated((int) $request->route('contratoId'), $request->validated());

            return $this->respostaLista($request, $this->aditivos->listar($filtros), AditivoResource::class);
        } catch (Throwable $erro) {
            return $this->falha($erro, 'aditivos.listar');
        }
    }

    public function show(MostrarAditivoRequest $request): JsonResponse
    {
        try {
            $this->validarVinculo($request);

            return $this->resposta($request, new AditivoResource($this->aditivos->obter((int) $request->route('aditivoId'))));
        } catch (Throwable $erro) {
            return $this->falha($erro, 'aditivos.obter');
        }
    }

    public function store(CriarAditivoRequest $request): JsonResponse
    {
        try {
            $this->validarVinculo($request);
            $dados = CriarAditivoData::fromValidated((int) $request->route('contratoId'), $request->validated());
            $aditivo = $this->aditivos->armazenar($dados, $this->identificadorDoAutor());

            return $this->resposta($request, new AditivoResource($aditivo), HttpResponse::HTTP_CREATED);
        } catch (Throwable $erro) {
            return $this->falha($erro, 'aditivos.criar');
        }
    }

    public function update(AlterarAditivoRequest $request): JsonResponse
    {
        try {
            $this->validarVinculo($request);
            $dados = AlterarAditivoData::fromValidated([
                ...$request->validated(),
                'id' => (int) $request->route('aditivoId'),
            ]);
            $aditivo = $this->aditivos->alterar($dados, $this->identificadorDoAutor());

            return $this->resposta($request, new AditivoResource($aditivo));
        } catch (Throwable $erro) {
            return $this->falha($erro, 'aditivos.alterar');
        }
    }

    public function alterarStatus(AlterarStatusAditivoRequest $request): JsonResponse
    {
        try {
            $this->validarVinculo($request);
            $dados = AlterarStatusAditivoData::fromValidated($request->validated());
            $aditivo = $this->aditivos->alterarStatus((int) $request->route('aditivoId'), $dados, $this->identificadorDoAutor());

            return $this->resposta($request, new AditivoResource($aditivo));
        } catch (Throwable $erro) {
            return $this->falha($erro, 'aditivos.alterar_status');
        }
    }

    public function assinar(AssinarAditivoRequest $request): JsonResponse
    {
        try {
            $this->validarVinculo($request);
            $aditivo = $this->aditivos->assinar(
                (int) $request->route('aditivoId'),
                (int) $request->route('assinaturaId'),
                $this->identificadorDoAutor(),
            );

            return $this->resposta($request, new AditivoResource($aditivo));
        } catch (Throwable $erro) {
            return $this->falha($erro, 'aditivos.assinar');
        }
    }

    public function confirmarDocumento(ConfirmarDocumentoAditivoRequest $request): JsonResponse
    {
        $anexo = ['anexo' => null, 'nome' => null];
        try {
            $this->validarVinculo($request);
            $nomeAnexo = $request->input('nomeAnexo');
            $anexo = AnexoDeRequisicao::guardar($this->armazenador, 'aditivos', $request->file('anexo'));

            $aditivo = $this->aditivos->confirmarDocumento(
                (int) $request->route('aditivoId'),
                $anexo['anexo'],
                is_string($nomeAnexo) && $nomeAnexo !== '' ? $nomeAnexo : $anexo['nome'],
                $this->identificadorDoAutor(),
            );

            return $this->resposta($request, new AditivoResource($aditivo));
        } catch (Throwable $erro) {
            if ($anexo['anexo'] !== null) {
                $this->armazenador->excluir($anexo['anexo']);
            }

            return $this->falha($erro, 'aditivos.confirmar_documento');
        }
    }

    public function registrarBlockchain(RegistrarBlockchainAditivoRequest $request): JsonResponse
    {
        try {
            $this->validarVinculo($request);
            $aditivo = $this->aditivos->registrarBlockchain((int) $request->route('aditivoId'), $this->identificadorDoAutor());

            return $this->resposta($request, new AditivoResource($aditivo));
        } catch (Throwable $erro) {
            return $this->falha($erro, 'aditivos.registrar_blockchain');
        }
    }

    public function destroy(ExcluirAditivoRequest $request): JsonResponse
    {
        try {
            $this->validarVinculo($request);
            $this->aditivos->excluir((int) $request->route('aditivoId'), $this->identificadorDoAutor());

            return new JsonResponse(null, HttpResponse::HTTP_NO_CONTENT);
        } catch (Throwable $erro) {
            return $this->falha($erro, 'aditivos.excluir');
        }
    }

    private function identificadorDoAutor(): int
    {
        return (int) auth()->id();
    }

    private function validarVinculo(Request $request): void
    {
        if ($request->route('aditivoId') === null) {
            return;
        }
        $recurso = $this->aditivos->obter((int) $request->route('aditivoId'));
        if ($recurso->contratoId !== (int) $request->route('contratoId')) {
            throw new RecursoNaoEncontradoException;
        }
    }
}
