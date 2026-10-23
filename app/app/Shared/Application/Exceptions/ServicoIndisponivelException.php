<?php

declare(strict_types=1);

namespace App\Shared\Application\Exceptions;

use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class ServicoIndisponivelException extends ApiException
{
    /**
     * @param  array<string, mixed>  $contexto
     */
    public function __construct(
        string $mensagem = 'Serviço externo indisponível. Tente novamente.',
        array $contexto = [],
        ?Throwable $anterior = null,
    ) {
        parent::__construct('SERVICE_UNAVAILABLE', $mensagem, [], $contexto, $anterior);
    }

    public function status(): int
    {
        return Response::HTTP_SERVICE_UNAVAILABLE;
    }
}
