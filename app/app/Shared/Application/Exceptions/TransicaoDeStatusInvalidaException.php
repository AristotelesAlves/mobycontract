<?php

declare(strict_types=1);

namespace App\Shared\Application\Exceptions;

use Throwable;

/**
 * Transição não listada na máquina de estados (R-5.1, R-5.2, R-5.3, R-5.4, R-5.5).
 * Recebe rótulos textuais do estado atual e do destino, seja qual for o agregado.
 */
final class TransicaoDeStatusInvalidaException extends ApiException
{
    /**
     * @param  array<string, mixed>  $contexto
     */
    public function __construct(
        string $mensagem,
        string $de,
        string $para,
        array $contexto = [],
        ?Throwable $anterior = null,
    ) {
        parent::__construct(
            'BUSINESS_RULE_VIOLATION',
            $mensagem,
            ['status' => $mensagem],
            [...$contexto, 'de' => $de, 'para' => $para],
            $anterior,
        );
    }

    public static function entre(string $de, string $para): self
    {
        return new self(
            sprintf('Não é possível alterar o status de %s para %s.', $de, $para),
            $de,
            $para,
        );
    }

    public function status(): int
    {
        return 422;
    }
}
