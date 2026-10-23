<?php

declare(strict_types=1);

namespace App\Modules\Leads\Http\Controllers;

use App\Modules\Leads\Contracts\LeadServiceInterface;
use App\Modules\Leads\DTOs\Input\AlterarLeadData;
use App\Modules\Leads\DTOs\Input\AlterarSituacaoLeadData;
use App\Modules\Leads\DTOs\Input\CriarLeadData;
use App\Modules\Leads\DTOs\Input\ListarLeadsFiltros;
use App\Modules\Leads\Http\Requests\AlterarLeadRequest;
use App\Modules\Leads\Http\Requests\AlterarSituacaoLeadRequest;
use App\Modules\Leads\Http\Requests\CriarLeadRequest;
use App\Modules\Leads\Http\Requests\ExcluirLeadRequest;
use App\Modules\Leads\Http\Requests\ListarLeadsRequest;
use App\Modules\Leads\Http\Requests\MostrarLeadRequest;
use App\Modules\Leads\Http\Resources\LeadResource;
use App\Shared\Application\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Throwable;

final class LeadController extends ApiController
{
    public function __construct(
        private readonly LeadServiceInterface $leads,
    ) {}

    public function index(ListarLeadsRequest $request): JsonResponse
    {
        try {
            $filtros = ListarLeadsFiltros::fromValidated($request->validated());
            $resultado = $this->leads->listar($filtros);

            return $this->respostaLista($request, $resultado, LeadResource::class);
        } catch (Throwable $erro) {
            return $this->falha($erro, 'leads.listar');
        }
    }

    public function show(MostrarLeadRequest $request): JsonResponse
    {
        try {
            $lead = $this->leads->obter((int) $request->route('id'));

            return $this->resposta($request, new LeadResource($lead));
        } catch (Throwable $erro) {
            return $this->falha($erro, 'leads.obter');
        }
    }

    public function store(CriarLeadRequest $request): JsonResponse
    {
        try {
            $dados = CriarLeadData::fromValidated($request->validated());
            $lead = $this->leads->armazenar($dados, $this->identificadorDoAutor());

            return $this->resposta($request, new LeadResource($lead), HttpResponse::HTTP_CREATED);
        } catch (Throwable $erro) {
            return $this->falha($erro, 'leads.armazenar');
        }
    }

    public function update(AlterarLeadRequest $request): JsonResponse
    {
        try {
            $dados = AlterarLeadData::fromValidated([
                ...$request->validated(),
                'id' => (int) $request->route('id'),
            ]);
            $lead = $this->leads->alterar($dados, $this->identificadorDoAutor());

            return $this->resposta($request, new LeadResource($lead));
        } catch (Throwable $erro) {
            return $this->falha($erro, 'leads.alterar');
        }
    }

    public function alterarSituacao(AlterarSituacaoLeadRequest $request): JsonResponse
    {
        try {
            $dados = AlterarSituacaoLeadData::fromValidated([
                ...$request->validated(),
                'id' => (int) $request->route('id'),
            ]);
            $lead = $this->leads->alterarSituacao($dados, $this->identificadorDoAutor());

            return $this->resposta($request, new LeadResource($lead));
        } catch (Throwable $erro) {
            return $this->falha($erro, 'leads.alterar_situacao');
        }
    }

    public function destroy(ExcluirLeadRequest $request): JsonResponse
    {
        try {
            $this->leads->excluir((int) $request->route('id'), $this->identificadorDoAutor());

            return new JsonResponse(null, HttpResponse::HTTP_NO_CONTENT);
        } catch (Throwable $erro) {
            return $this->falha($erro, 'leads.excluir');
        }
    }

    private function identificadorDoAutor(): int
    {
        return (int) auth()->id();
    }
}
