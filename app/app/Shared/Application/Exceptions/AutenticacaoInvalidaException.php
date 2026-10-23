<?php

declare(strict_types=1);

namespace App\Shared\Application\Exceptions;

use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Token ausente, expirado ou revogado. Distinta de credencial incorreta no login.
 */
final class AutenticacaoInvalidaException extends ApiException
{
    /**
     * @param  array<string, mixed>  $contexto
     */
    public function __construct(
        string $mensagem = 'Autenticação necessária.',
        array $contexto = [],
        ?Throwable $anterior = null,
    ) {
        parent::__construct('UNAUTHENTICATED', $mensagem, [], $contexto, $anterior);
    }

    public function status(): int
    {
        return Response::HTTP_UNAUTHORIZED;
    }
}
