<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\Http\Controllers;

use App\Modules\Imoveis\Contracts\ImovelSubRecursoServiceInterface;
use App\Modules\Imoveis\Http\Requests\AlterarManutencaoRequest;
use App\Modules\Imoveis\Http\Requests\CriarManutencaoRequest;
use App\Modules\Imoveis\Http\Resources\ManutencaoResource;
use App\Shared\Application\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Throwable;

final class ManutencaoController extends ApiController
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
            $resultado = $this->servico->listarManutencoes($imovelId, $status, $pagina, $porPagina);

            return $this->respostaLista($request, $resultado, ManutencaoResource::class);
        } catch (Throwable $erro) {
            return $this->falha($erro, 'manutencoes.listar');
        }
    }

    public function store(CriarManutencaoRequest $request, int $imovelId): JsonResponse
    {
        try {
            $dados = $request->toDto($imovelId);
            $manutencao = $this->servico->criarManutencao($dados, $this->identificadorDoAutor());

            return $this->resposta($request, new ManutencaoResource($manutencao), HttpResponse::HTTP_CREATED);
        } catch (Throwable $erro) {
            return $this->falha($erro, 'manutencoes.criar');
        }
    }

    public function update(AlterarManutencaoRequest $request, int $imovelId, int $manutencaoId): JsonResponse
    {
        try {
            $dados = $request->toDto();
            $manutencao = $this->servico->alterarManutencao($imovelId, $manutencaoId, $dados, $this->identificadorDoAutor());

            return $this->resposta($request, new ManutencaoResource($manutencao));
        } catch (Throwable $erro) {
            return $this->falha($erro, 'manutencoes.alterar');
        }
    }

    private function identificadorDoAutor(): int
    {
        return (int) auth()->id();
    }
}
