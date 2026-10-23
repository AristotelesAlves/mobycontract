<?php

declare(strict_types=1);

namespace App\Shared\Application\Exceptions;

use Throwable;

final class RegraNegocioException extends ApiException
{
    /**
     * @param  array<string, string>  $campos
     * @param  array<string, mixed>  $contexto
     */
    public function __construct(
        string $mensagem,
        array $campos = [],
        array $contexto = [],
        ?Throwable $anterior = null,
    ) {
        parent::__construct('BUSINESS_RULE_VIOLATION', $mensagem, $campos, $contexto, $anterior);
    }

    public function status(): int
    {
        return 422;
    }
}
