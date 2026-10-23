<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\Http\Controllers;

use App\Modules\Imoveis\Contracts\ImovelSubRecursoServiceInterface;
use App\Modules\Imoveis\Http\Requests\AlterarSituacaoInteressadoRequest;
use App\Modules\Imoveis\Http\Requests\CriarInteressadoRequest;
use App\Modules\Imoveis\Http\Resources\InteressadoResource;
use App\Shared\Application\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Throwable;

final class InteressadoController extends ApiController
{
    public function __construct(
        private readonly ImovelSubRecursoServiceInterface $servico,
    ) {}

    public function index(Request $request, int $imovelId): JsonResponse
    {
        try {
            $pagina = (int) $request->query('pagina', 1);
            $porPagina = (int) $request->query('porPagina', 15);
            $resultado = $this->servico->listarInteressados($imovelId, $pagina, $porPagina);

            return $this->respostaLista($request, $resultado, InteressadoResource::class);
        } catch (Throwable $erro) {
            return $this->falha($erro, 'interessados.listar');
        }
    }

    public function store(CriarInteressadoRequest $request, int $imovelId): JsonResponse
    {
        try {
            $dados = $request->toDto($imovelId);
            $interessado = $this->servico->criarInteressado($dados, $this->identificadorDoAutor());

            return $this->resposta($request, new InteressadoResource($interessado), HttpResponse::HTTP_CREATED);
        } catch (Throwable $erro) {
            return $this->falha($erro, 'interessados.criar');
        }
    }

    public function alterarSituacao(AlterarSituacaoInteressadoRequest $request, int $imovelId, int $interessadoId): JsonResponse
    {
        try {
            $dados = $request->toDto();
            $interessado = $this->servico->alterarSituacaoInteressado($imovelId, $interessadoId, $dados, $this->identificadorDoAutor());

            return $this->resposta($request, new InteressadoResource($interessado));
        } catch (Throwable $erro) {
            return $this->falha($erro, 'interessados.alterar_situacao');
        }
    }

    private function identificadorDoAutor(): int
    {
        return (int) auth()->id();
    }
}
