<?php

declare(strict_types=1);

namespace App\Shared\Domain\Enums;

enum Papel: string
{
    case Administrador = 'Administrador';
    case Operador = 'Operador';

    /**
     * @return list<string>
     */
    public function abilities(): array
    {
        return match ($this) {
            self::Administrador => ['*'],
            self::Operador => ['listar', 'criar', 'alterar', 'consultar'],
        };
    }

    public function podeValidarContrato(): bool
    {
        return $this === self::Administrador;
    }

    public function podeExcluir(): bool
    {
        return $this === self::Administrador;
    }

    public function permite(string $habilidade): bool
    {
        return in_array('*', $this->abilities(), true)
            || in_array($habilidade, $this->abilities(), true);
    }
}
