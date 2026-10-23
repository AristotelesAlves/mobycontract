<?php

declare(strict_types=1);

namespace App\Modules\Leads\Domain\Enums;

/**
 * Máquina de estados do lead (R-5.5).
 */
enum SituacaoLead: string
{
    case Novo = 'Novo';
    case EmContato = 'Em contato';
    case Desistiu = 'Desistiu';
    case Alugou = 'Alugou';

    /**
     * @return list<self>
     */
    public function transicoesPermitidas(): array
    {
        return match ($this) {
            self::Novo => [self::EmContato, self::Alugou, self::Desistiu],
            self::EmContato => [self::Alugou, self::Desistiu, self::Novo],
            self::Alugou => [self::Desistiu],
            self::Desistiu => [self::EmContato],
        };
    }

    public function podeTransicionarPara(self $destino): bool
    {
        return in_array($destino, $this->transicoesPermitidas(), true);
    }

    public function estaConvertido(): bool
    {
        return $this === self::Alugou;
    }
}
