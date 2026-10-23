<?php

declare(strict_types=1);

namespace App\Modules\Vistorias\Domain\Enums;

enum StatusVistoria: string
{
    case Agendada = 'Agendada';
    case EmAndamento = 'Em andamento';
    case Concluida = 'Concluída';
    case Cancelada = 'Cancelada';

    /**
     * @return list<self>
     */
    public function transicoesPermitidas(): array
    {
        return match ($this) {
            self::Agendada => [self::EmAndamento, self::Concluida, self::Cancelada],
            self::EmAndamento => [self::Concluida, self::Cancelada],
            self::Concluida => [],
            self::Cancelada => [],
        };
    }

    public function podeTransicionarPara(self $destino): bool
    {
        return in_array($destino, $this->transicoesPermitidas(), true);
    }

    public function permiteEdicao(): bool
    {
        return $this === self::Agendada || $this === self::EmAndamento;
    }

    public function permiteExclusao(): bool
    {
        return $this === self::Agendada;
    }
}
