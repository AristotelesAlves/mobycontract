<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\Http\Controllers;

use App\Modules\Imoveis\Contracts\ImovelServiceInterface;
use App\Modules\Imoveis\DTOs\Input\AlterarImovelData;
use App\Modules\Imoveis\DTOs\Input\AlterarSituacaoImovelData;
use App\Modules\Imoveis\DTOs\Input\CriarImovelData;
use App\Modules\Imoveis\DTOs\Input\ListarImoveisFiltros;
use App\Modules\Imoveis\DTOs\Output\CustoMensalResult;
use App\Modules\Imoveis\Http\Requests\AlterarImovelRequest;
use App\Modules\Imoveis\Http\Requests\AlterarSituacaoImovelRequest;
use App\Modules\Imoveis\Http\Requests\ConsultarCustoMensalRequest;
use App\Modules\Imoveis\Http\Requests\ConsultarOcupacaoRequest;
use App\Modules\Imoveis\Http\Requests\CriarImovelRequest;
use App\Modules\Imoveis\Http\Requests\ExcluirImovelRequest;
use App\Modules\Imoveis\Http\Requests\ListarImoveisRequest;
use App\Modules\Imoveis\Http\Requests\MostrarImovelRequest;
use App\Modules\Imoveis\Http\Resources\ImovelResource;
use App\Shared\Application\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Throwable;

final class ImovelController extends ApiController
{
    public function __construct(
        private readonly ImovelServiceInterface $imoveis,
    ) {}

    public function index(ListarImoveisRequest $request): JsonResponse
    {
        try {
            $filtros = ListarImoveisFiltros::fromValidated($request->validated());
            $resultado = $this->imoveis->listar($filtros);

            return $this->respostaLista($request, $resultado, ImovelResource::class);
        } catch (Throwable $erro) {
            return $this->falha($erro, 'imoveis.listar');
        }
    }

    public function show(MostrarImovelRequest $request): JsonResponse
    {
        try {
            $imovel = $this->imoveis->obter((int) $request->route('id'));

            return $this->resposta($request, new ImovelResource($imovel));
        } catch (Throwable $erro) {
            return $this->falha($erro, 'imoveis.obter');
        }
    }

    public function store(CriarImovelRequest $request): JsonResponse
    {
        try {
            $dados = CriarImovelData::fromValidated($request->validated());
            $imovel = $this->imoveis->armazenar($dados, $this->identificadorDoAutor());

            return $this->resposta($request, new ImovelResource($imovel), HttpResponse::HTTP_CREATED);
        } catch (Throwable $erro) {
            return $this->falha($erro, 'imoveis.armazenar');
        }
    }

    public function update(AlterarImovelRequest $request): JsonResponse
    {
        try {
            $dados = AlterarImovelData::fromValidated([
                ...$request->validated(),
                'id' => (int) $request->route('id'),
            ]);
            $imovel = $this->imoveis->alterar($dados, $this->identificadorDoAutor());

            return $this->resposta($request, new ImovelResource($imovel));
        } catch (Throwable $erro) {
            return $this->falha($erro, 'imoveis.alterar');
        }
    }

    public function alterarSituacao(AlterarSituacaoImovelRequest $request): JsonResponse
    {
        try {
            $dados = AlterarSituacaoImovelData::fromValidated([
                ...$request->validated(),
                'id' => (int) $request->route('id'),
            ]);
            $imovel = $this->imoveis->alterarSituacao($dados, $this->identificadorDoAutor());

            return $this->resposta($request, new ImovelResource($imovel));
        } catch (Throwable $erro) {
            return $this->falha($erro, 'imoveis.alterar_situacao');
        }
    }

    public function custoMensal(ConsultarCustoMensalRequest $request): JsonResponse
    {
        try {
            $resultado = $this->imoveis->custoMensal((int) $request->route('id'));

            return new JsonResponse(['dados' => $this->formatarCustoMensal($resultado)], HttpResponse::HTTP_OK);
        } catch (Throwable $erro) {
            return $this->falha($erro, 'imoveis.custo_mensal');
        }
    }

    public function destroy(ExcluirImovelRequest $request): JsonResponse
    {
        try {
            $this->imoveis->excluir((int) $request->route('id'), $this->identificadorDoAutor());

            return new JsonResponse(null, HttpResponse::HTTP_NO_CONTENT);
        } catch (Throwable $erro) {
            return $this->falha($erro, 'imoveis.excluir');
        }
    }

    public function ocupacao(ConsultarOcupacaoRequest $request): JsonResponse
    {
        try {
            $resultado = $this->imoveis->ocupacao((int) $request->route('id'));

            return new JsonResponse([
                'dados' => [
                    'situacao' => $resultado->situacao,
                    'contratoAtivo' => $resultado->contratoAtivo,
                ],
            ], HttpResponse::HTTP_OK);
        } catch (Throwable $erro) {
            return $this->falha($erro, 'imoveis.ocupacao');
        }
    }

    /**
     * @return array<string, float>
     */
    private function formatarCustoMensal(CustoMensalResult $resultado): array
    {
        return [
            'custoMensal' => round($resultado->custoMensal, 2),
            'valorAluguel' => round($resultado->valorAluguel, 2),
            'valorCondominio' => round($resultado->valorCondominio, 2),
            'valorIptuMensal' => round($resultado->valorIptuMensal, 2),
        ];
    }

    private function identificadorDoAutor(): int
    {
        return (int) auth()->id();
    }
}
