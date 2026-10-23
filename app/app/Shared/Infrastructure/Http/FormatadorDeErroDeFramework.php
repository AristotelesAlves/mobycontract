<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Http;

use App\Shared\Application\Exceptions\ApiException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

final class FormatadorDeErroDeFramework
{
    public function render(Request $request, Throwable $erro): JsonResponse
    {
        return match (true) {
            $erro instanceof ApiException => $this->daApiException($erro),
            $erro instanceof ValidationException => $this->daValidacao($erro),
            $erro instanceof AccessDeniedHttpException => $this->resposta(
                'FORBIDDEN',
                'Acesso negado.',
                [],
                Response::HTTP_FORBIDDEN,
            ),
            $erro instanceof ModelNotFoundException, $erro instanceof NotFoundHttpException => $this->resposta(
                'NOT_FOUND',
                'Recurso não encontrado.',
                [],
                Response::HTTP_NOT_FOUND,
            ),
            $erro instanceof QueryException => $this->daQueryException($erro),
            $erro instanceof AuthenticationException => $this->resposta(
                'UNAUTHENTICATED',
                'Autenticação necessária.',
                [],
                Response::HTTP_UNAUTHORIZED,
            ),
            $erro instanceof AuthorizationException => $this->resposta(
                'FORBIDDEN',
                'Permissão insuficiente para esta operação.',
                [],
                Response::HTTP_FORBIDDEN,
            ),
            $erro instanceof MethodNotAllowedHttpException => $this->resposta(
                'NOT_FOUND',
                'Recurso não encontrado.',
                [],
                Response::HTTP_NOT_FOUND,
            ),
            $erro instanceof HttpExceptionInterface => $this->doHttp($erro),
            default => $this->resposta(
                'INTERNAL_ERROR',
                'Não foi possível concluir a operação. Tente novamente.',
                [],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            ),
        };
    }

    private function daApiException(ApiException $erro): JsonResponse
    {
        return $this->resposta(
            $erro->codigo(),
            $erro->mensagem(),
            $erro->campos(),
            $erro->status(),
        );
    }

    private function daValidacao(ValidationException $erro): JsonResponse
    {
        return $this->resposta(
            'VALIDATION_ERROR',
            'Verifique os campos informados.',
            $this->camposDaValidacao($erro),
            Response::HTTP_UNPROCESSABLE_ENTITY,
        );
    }

    private function daQueryException(QueryException $erro): JsonResponse
    {
        $sqlState = $erro->errorInfo[0] ?? null;

        if ($sqlState === '23505' || $sqlState === '23000') {
            return $this->resposta(
                'CONFLICT',
                'Já existe um registro com estes dados.',
                [],
                Response::HTTP_CONFLICT,
            );
        }

        return $this->resposta(
            'INTERNAL_ERROR',
            'Não foi possível concluir a operação. Tente novamente.',
            [],
            Response::HTTP_INTERNAL_SERVER_ERROR,
        );
    }

    private function textoDoCampo(mixed $valor): string
    {
        if (is_string($valor)) {
            return $valor;
        }

        return is_scalar($valor) ? (string) $valor : '';
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function camposDaValidacao(ValidationException $erro): array
    {
        $campos = [];

        foreach ($erro->errors() as $campo => $mensagens) {
            $lista = is_array($mensagens)
                ? array_values(array_map($this->textoDoCampo(...), $mensagens))
                : [$this->textoDoCampo($mensagens)];

            $campos[(string) $campo] = $lista;
        }

        return $campos;
    }

    private function doHttp(HttpExceptionInterface $erro): JsonResponse
    {
        return $this->resposta(
            'BUSINESS_RULE_VIOLATION',
            $erro->getMessage() !== '' ? $erro->getMessage() : 'Operação não permitida.',
            [],
            $erro->getStatusCode(),
        );
    }

    /**
     * @param  array<string, string|array<int, string>>  $campos
     */
    private function resposta(
        string $codigo,
        string $mensagem,
        array $campos,
        int $status,
        ?string $detalhe = null,
    ): JsonResponse {
        $corpo = [
            'codigo' => $codigo,
            'mensagem' => $mensagem,
            'campos' => $campos,
        ];

        if ($detalhe !== null) {
            $corpo['detalhe'] = $detalhe;
        }

        return new JsonResponse($corpo, $status);
    }
}
