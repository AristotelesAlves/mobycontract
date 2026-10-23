<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Http\Controllers;

use App\Modules\Contratos\Contracts\ContratoServiceInterface;
use App\Modules\Contratos\DTOs\Input\AlterarContratoData;
use App\Modules\Contratos\DTOs\Input\AlterarStatusData;
use App\Modules\Contratos\DTOs\Input\CriarContratoData;
use App\Modules\Contratos\DTOs\Input\LancarEncargoData;
use App\Modules\Contratos\DTOs\Input\ListarContratosFiltros;
use App\Modules\Contratos\DTOs\Input\ListarParcelasFiltros;
use App\Modules\Contratos\DTOs\Input\RegistrarPagamentoData;
use App\Modules\Contratos\Http\Requests\AlterarContratoRequest;
use App\Modules\Contratos\Http\Requests\AlterarStatusRequest;
use App\Modules\Contratos\Http\Requests\AssinarContratoRequest;
use App\Modules\Contratos\Http\Requests\ConfirmarDocumentoRequest;
use App\Modules\Contratos\Http\Requests\CriarContratoRequest;
use App\Modules\Contratos\Http\Requests\ExcluirContratoRequest;
use App\Modules\Contratos\Http\Requests\LancarEncargoRequest;
use App\Modules\Contratos\Http\Requests\ListarContratosRequest;
use App\Modules\Contratos\Http\Requests\ListarEtapasRequest;
use App\Modules\Contratos\Http\Requests\ListarParcelasRequest;
use App\Modules\Contratos\Http\Requests\MostrarContratoRequest;
use App\Modules\Contratos\Http\Requests\RegistrarBlockchainRequest;
use App\Modules\Contratos\Http\Requests\RegistrarPagamentoRequest;
use App\Modules\Contratos\Http\Resources\ContratoResource;
use App\Modules\Contratos\Http\Resources\EtapaResource;
use App\Modules\Contratos\Http\Resources\ParcelaResource;
use App\Shared\Application\Controllers\ApiController;
use App\Shared\Application\Support\AnexoDeRequisicao;
use App\Shared\Contracts\ArmazenadorDeArquivo;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Throwable;

final class ContratoController extends ApiController
{
    public function __construct(
        private readonly ContratoServiceInterface $contratos,
        private readonly ArmazenadorDeArquivo $armazenador,
    ) {}

    public function index(ListarContratosRequest $request): JsonResponse
    {
        try {
            $filtros = ListarContratosFiltros::fromValidated($request->validated());

            $resposta = $this->respostaLista($request, $this->contratos->listar($filtros), ContratoResource::class);

            /** @var array<string, mixed> $conteudo */
            $conteudo = $resposta->getData(true);
            /** @var array<string, mixed> $dados */
            $dados = $conteudo['dados'];
            $dados['totais'] = $this->contratos->totais();
            $conteudo['dados'] = $dados;

            return new JsonResponse($conteudo, HttpResponse::HTTP_OK);
        } catch (Throwable $erro) {
            return $this->falha($erro, 'contratos.listar');
        }
    }

    public function show(MostrarContratoRequest $request): JsonResponse
    {
        try {
            $contrato = $this->contratos->obter((int) $request->route('id'));

            return $this->resposta($request, new ContratoResource($contrato));
        } catch (Throwable $erro) {
            return $this->falha($erro, 'contratos.obter');
        }
    }

    public function store(CriarContratoRequest $request): JsonResponse
    {
        $anexo = ['anexo' => null, 'nome' => null];
        try {
            $anexo = AnexoDeRequisicao::guardar($this->armazenador, 'garantias', $request->file('comprovanteGarantia'));
            $dados = CriarContratoData::fromValidated([
                ...$request->validated(),
                'comprovanteGarantiaCaminho' => $anexo['anexo'],
                'comprovanteGarantiaNome' => $anexo['nome'],
            ]);
            $contrato = $this->contratos->armazenar($dados, $this->identificadorDoAutor());
            if ($anexo['anexo'] !== null && ! in_array($contrato->garantia, ['Caução', 'Fiador'], true)) {
                $this->armazenador->excluir($anexo['anexo']);
            }

            return $this->resposta($request, new ContratoResource($contrato), HttpResponse::HTTP_CREATED);
        } catch (Throwable $erro) {
            if ($anexo['anexo'] !== null) {
                $this->armazenador->excluir($anexo['anexo']);
            }

            return $this->falha($erro, 'contratos.armazenar');
        }
    }

    public function update(AlterarContratoRequest $request): JsonResponse
    {
        try {
            $dados = AlterarContratoData::fromValidated([
                ...$request->validated(),
                'id' => (int) $request->route('id'),
            ]);
            $contrato = $this->contratos->alterar($dados, $this->identificadorDoAutor());

            return $this->resposta($request, new ContratoResource($contrato));
        } catch (Throwable $erro) {
            return $this->falha($erro, 'contratos.alterar');
        }
    }

    public function alterarStatus(AlterarStatusRequest $request): JsonResponse
    {
        try {
            $dados = AlterarStatusData::fromValidated($request->validated());
            $contrato = $this->contratos->alterarStatus((int) $request->route('id'), $dados, $this->identificadorDoAutor());

            return $this->resposta($request, new ContratoResource($contrato));
        } catch (Throwable $erro) {
            return $this->falha($erro, 'contratos.alterar_status');
        }
    }

    public function destroy(ExcluirContratoRequest $request): JsonResponse
    {
        try {
            $this->contratos->excluir((int) $request->route('id'), $this->identificadorDoAutor());

            return new JsonResponse(null, HttpResponse::HTTP_NO_CONTENT);
        } catch (Throwable $erro) {
            return $this->falha($erro, 'contratos.excluir');
        }
    }

    public function assinar(AssinarContratoRequest $request): JsonResponse
    {
        try {
            $contrato = $this->contratos->assinar(
                (int) $request->route('id'),
                (int) $request->route('assinaturaId'),
                $this->identificadorDoAutor(),
            );

            return $this->resposta($request, new ContratoResource($contrato));
        } catch (Throwable $erro) {
            return $this->falha($erro, 'contratos.assinar');
        }
    }

    public function confirmarDocumento(ConfirmarDocumentoRequest $request): JsonResponse
    {
        $anexo = ['anexo' => null, 'nome' => null];
        $anexoPersistido = false;
        try {
            $nomeAnexo = $request->input('nomeAnexo');
            $anexo = AnexoDeRequisicao::guardar($this->armazenador, 'contratos', $request->file('anexo'));
            $contratoAtual = $this->contratos->obter((int) $request->route('id'));

            $contrato = $this->contratos->confirmarDocumentoFisico(
                (int) $request->route('id'),
                $anexo['anexo'],
                is_string($nomeAnexo) && $nomeAnexo !== '' ? $nomeAnexo : $anexo['nome'],
                $this->identificadorDoAutor(),
            );
            $anexoPersistido = true;

            if ($anexo['anexo'] !== null && $contratoAtual->anexoContrato !== null && $contratoAtual->anexoContrato !== $anexo['anexo']) {
                $this->armazenador->excluir($contratoAtual->anexoContrato);
            }

            return $this->resposta($request, new ContratoResource($contrato));
        } catch (Throwable $erro) {
            if (! $anexoPersistido && $anexo['anexo'] !== null) {
                $this->armazenador->excluir($anexo['anexo']);
            }

            return $this->falha($erro, 'contratos.confirmar_documento');
        }
    }

    public function registrarBlockchain(RegistrarBlockchainRequest $request): JsonResponse
    {
        try {
            $contrato = $this->contratos->registrarBlockchain((int) $request->route('id'), $this->identificadorDoAutor());

            return $this->resposta($request, new ContratoResource($contrato));
        } catch (Throwable $erro) {
            return $this->falha($erro, 'contratos.registrar_blockchain');
        }
    }

    public function etapas(ListarEtapasRequest $request): JsonResponse
    {
        try {
            $etapas = $this->contratos->etapas((int) $request->route('id'));

            return new JsonResponse([
                'dados' => [
                    'itens' => EtapaResource::collection($etapas)->resolve($request),
                ],
            ], HttpResponse::HTTP_OK);
        } catch (Throwable $erro) {
            return $this->falha($erro, 'contratos.etapas');
        }
    }

    public function parcelas(ListarParcelasRequest $request): JsonResponse
    {
        try {
            $filtros = ListarParcelasFiltros::fromValidated((int) $request->route('id'), $request->validated());

            return $this->respostaLista($request, $this->contratos->listarParcelas($filtros), ParcelaResource::class);
        } catch (Throwable $erro) {
            return $this->falha($erro, 'contratos.parcelas');
        }
    }

    public function registrarPagamento(RegistrarPagamentoRequest $request): JsonResponse
    {
        try {
            $dados = RegistrarPagamentoData::fromValidated($request->validated());
            $parcela = $this->contratos->registrarPagamento(
                (int) $request->route('id'),
                (int) $request->route('parcelaId'),
                $dados,
                $this->identificadorDoAutor(),
            );

            return $this->resposta($request, new ParcelaResource($parcela));
        } catch (Throwable $erro) {
            return $this->falha($erro, 'contratos.registrar_pagamento');
        }
    }

    public function lancarEncargo(LancarEncargoRequest $request): JsonResponse
    {
        try {
            $dados = LancarEncargoData::fromValidated($request->validated());
            $parcela = $this->contratos->lancarEncargo(
                (int) $request->route('id'),
                (int) $request->route('parcelaId'),
                $dados,
                $this->identificadorDoAutor(),
            );

            return $this->resposta($request, new ParcelaResource($parcela));
        } catch (Throwable $erro) {
            return $this->falha($erro, 'contratos.lancar_encargo');
        }
    }

    private function identificadorDoAutor(): int
    {
        return (int) auth()->id();
    }
}
