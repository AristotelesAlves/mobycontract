<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\Http\Controllers;

use App\Modules\Imoveis\Contracts\ImovelSubRecursoServiceInterface;
use App\Modules\Imoveis\Http\Requests\CriarAnotacaoRequest;
use App\Modules\Imoveis\Http\Resources\AnotacaoResource;
use App\Shared\Application\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Throwable;

final class AnotacaoController extends ApiController
{
    public function __construct(
        private readonly ImovelSubRecursoServiceInterface $servico,
    ) {}

    public function index(Request $request, int $imovelId): JsonResponse
    {
        try {
            $pagina = (int) $request->query('pagina', 1);
            $porPagina = (int) $request->query('porPagina', 15);
            $resultado = $this->servico->listarAnotacoes($imovelId, $pagina, $porPagina);

            return $this->respostaLista($request, $resultado, AnotacaoResource::class);
        } catch (Throwable $erro) {
            return $this->falha($erro, 'anotacoes.listar');
        }
    }

    public function store(CriarAnotacaoRequest $request, int $imovelId): JsonResponse
    {
        try {
            $dados = $request->toDto($imovelId);
            $anotacao = $this->servico->criarAnotacao($dados, $this->identificadorDoAutor());

            return $this->resposta($request, new AnotacaoResource($anotacao), HttpResponse::HTTP_CREATED);
        } catch (Throwable $erro) {
            return $this->falha($erro, 'anotacoes.criar');
        }
    }

    public function destroy(Request $request, int $imovelId, int $anotacaoId): JsonResponse
    {
        try {
            $this->servico->excluirAnotacao($imovelId, $anotacaoId, $this->identificadorDoAutor());

            return new JsonResponse(null, HttpResponse::HTTP_NO_CONTENT);
        } catch (Throwable $erro) {
            return $this->falha($erro, 'anotacoes.excluir');
        }
    }

    private function identificadorDoAutor(): int
    {
        return (int) auth()->id();
    }
}
