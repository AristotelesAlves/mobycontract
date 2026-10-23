<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Domain\Enums;

/**
 * Máquina de estados do aditivo (R-5.3).
 */
enum StatusAditivo: string
{
    case Rascunho = 'Rascunho';
    case PendenteDeAssinatura = 'Pendente de assinatura';
    case Assinado = 'Assinado';
    case Validado = 'Validado';
    case Cancelado = 'Cancelado';

    /**
     * @return list<self>
     */
    public function transicoesPermitidas(): array
    {
        return match ($this) {
            self::Rascunho => [self::PendenteDeAssinatura, self::Cancelado],
            self::PendenteDeAssinatura => [self::Assinado, self::Rascunho],
            self::Assinado => [self::Validado, self::Cancelado],
            self::Validado => [],
            self::Cancelado => [],
        };
    }

    public function podeTransicionarPara(self $destino): bool
    {
        return in_array($destino, $this->transicoesPermitidas(), true);
    }

    public function permiteEdicao(): bool
    {
        return $this === self::Rascunho || $this === self::PendenteDeAssinatura;
    }

    public function permiteExclusao(): bool
    {
        return $this === self::Rascunho;
    }
}
