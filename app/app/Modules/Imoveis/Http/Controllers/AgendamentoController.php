<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\Http\Controllers;

use App\Modules\Imoveis\Contracts\ImovelSubRecursoServiceInterface;
use App\Modules\Imoveis\Http\Requests\AlterarSituacaoAgendamentoRequest;
use App\Modules\Imoveis\Http\Requests\CriarAgendamentoRequest;
use App\Modules\Imoveis\Http\Resources\AgendamentoResource;
use App\Shared\Application\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Throwable;

final class AgendamentoController extends ApiController
{
    public function __construct(
        private readonly ImovelSubRecursoServiceInterface $servico,
    ) {}

    public function index(Request $request, int $imovelId): JsonResponse
    {
        try {
            $status = $request->has('status') ? (string) $request->query('status') : null;
            $pagina = (int) $request->query('pagina', 1);
            $porPagina = (int) $request->query('porPagina', 15);
            $resultado = $this->servico->listarAgendamentos($imovelId, $status, $pagina, $porPagina);

            return $this->respostaLista($request, $resultado, AgendamentoResource::class);
        } catch (Throwable $erro) {
            return $this->falha($erro, 'agendamentos.listar');
        }
    }

    public function store(CriarAgendamentoRequest $request, int $imovelId): JsonResponse
    {
        try {
            $dados = $request->toDto($imovelId);
            $agendamento = $this->servico->criarAgendamento($dados, $this->identificadorDoAutor());

            return $this->resposta($request, new AgendamentoResource($agendamento), HttpResponse::HTTP_CREATED);
        } catch (Throwable $erro) {
            return $this->falha($erro, 'agendamentos.criar');
        }
    }

    public function alterarSituacao(AlterarSituacaoAgendamentoRequest $request, int $imovelId, int $agendamentoId): JsonResponse
    {
        try {
            $dados = $request->toDto();
            $agendamento = $this->servico->alterarSituacaoAgendamento($imovelId, $agendamentoId, $dados, $this->identificadorDoAutor());

            return $this->resposta($request, new AgendamentoResource($agendamento));
        } catch (Throwable $erro) {
            return $this->falha($erro, 'agendamentos.alterar_situacao');
        }
    }

    private function identificadorDoAutor(): int
    {
        return (int) auth()->id();
    }
}
