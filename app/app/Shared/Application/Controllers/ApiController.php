<?php

declare(strict_types=1);

namespace App\Shared\Application\Controllers;

use App\Shared\Application\DTOs\PaginatedResult;
use App\Shared\Application\Exceptions\ApiException;
use App\Shared\Contracts\RegistradorDeErrosApi;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

abstract class ApiController extends Controller
{
    protected function resposta(Request $request, JsonResource $resource, int $status = HttpResponse::HTTP_OK): JsonResponse
    {
        return new JsonResponse(['dados' => $resource->resolve($request)], $status);
    }

    /**
     * @template TItem
     *
     * @param  PaginatedResult<TItem>  $resultado
     * @param  class-string<JsonResource>  $classeDoResource
     */
    protected function respostaLista(Request $request, PaginatedResult $resultado, string $classeDoResource): JsonResponse
    {
        return new JsonResponse([
            'dados' => [
                'pagina' => $resultado->pagina,
                'tamanhoPagina' => $resultado->tamanhoPagina,
                'totalPaginas' => $resultado->totalPaginas,
                'total' => $resultado->total,
                'itens' => $classeDoResource::collection($resultado->itens)->resolve($request),
            ],
        ], HttpResponse::HTTP_OK);
    }

    protected function falha(Throwable $erro, string $contexto): JsonResponse
    {
        if ($erro instanceof ValidationException
            || $erro instanceof AuthenticationException
            || $erro instanceof AuthorizationException
            || $erro instanceof HttpExceptionInterface) {
            throw $erro;
        }

        $registrador = app(RegistradorDeErrosApi::class);

        if ($erro instanceof ApiException) {
            $registrador->registrarFalhaDeDominio($erro, $contexto);

            return new JsonResponse([
                'codigo' => $erro->codigo(),
                'mensagem' => $erro->mensagem(),
                'campos' => $erro->campos(),
                'detalhe' => $erro->detalhe(),
            ], $erro->status());
        }

        $registrador->registrarFalhaInesperada($erro, $contexto);

        return new JsonResponse([
            'codigo' => 'INTERNAL_ERROR',
            'mensagem' => 'Não foi possível concluir a operação. Tente novamente.',
            'campos' => [],
            'detalhe' => null,
        ], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
    }
}
