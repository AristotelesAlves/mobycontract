<?php

declare(strict_types=1);

namespace App\Shared\Application\Exceptions;

use RuntimeException;
use Throwable;

abstract class ApiException extends RuntimeException
{
    /**
     * @param  array<string, string|array<int, string>>  $campos
     * @param  array<string, mixed>  $contexto
     */
    public function __construct(
        private readonly string $codigo,
        private readonly string $mensagem,
        private readonly array $campos = [],
        private readonly array $contexto = [],
        ?Throwable $anterior = null,
    ) {
        parent::__construct($mensagem, 0, $anterior);
    }

    abstract public function status(): int;

    public function codigo(): string
    {
        return $this->codigo;
    }

    public function mensagem(): string
    {
        return $this->mensagem;
    }

    /**
     * @return array<string, string|array<int, string>>
     */
    public function campos(): array
    {
        return $this->campos;
    }

    /**
     * @return array<string, mixed>
     */
    public function contexto(): array
    {
        return $this->contexto;
    }

    public function detalhe(): string
    {
        return $this->getMessage();
    }
}
