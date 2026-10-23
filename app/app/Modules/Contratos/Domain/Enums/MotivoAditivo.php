<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Domain\Enums;

/**
 * Vocabulário de motivos do aditivo e seus campos adicionais (R-3.6.1, R-3.6.2).
 */
enum MotivoAditivo: string
{
    case Vigencia = 'Vigência';
    case Clausula = 'Cláusula';
    case Valor = 'Valor';
    case TaxaCondominio = 'Taxa de Condomínio';
    case Iptu = 'IPTU';
    case SeguroIncendio = 'Seguro Incêndio';
    case Agua = 'Água';
    case Energia = 'Energia';

    /**
     * Campo de entrada que acompanha o motivo (R-12.12.1).
     */
    public function campo(): string
    {
        return match ($this) {
            self::Vigencia => 'novaDataFim',
            self::Clausula => 'clausulaModificada',
            self::Valor => 'novoValor',
            self::TaxaCondominio => 'novaTaxaCondominio',
            self::Iptu => 'novoIPTU',
            self::SeguroIncendio => 'novoSeguroIncendio',
            self::Agua => 'novoAgua',
            self::Energia => 'novoEnergia',
        };
    }

    public function rotulo(): string
    {
        return match ($this) {
            self::Vigencia => 'Nova Data de Término',
            self::Clausula => 'Cláusula Modificada',
            self::Valor => 'Novo Valor Mensal',
            self::TaxaCondominio => 'Nova Taxa de Condomínio',
            self::Iptu => 'Novo IPTU',
            self::SeguroIncendio => 'Novo Seguro Incêndio',
            self::Agua => 'Novo Valor Água',
            self::Energia => 'Novo Valor Energia',
        };
    }

    public function ehMonetario(): bool
    {
        return ! in_array($this, [self::Vigencia, self::Clausula], true);
    }
}
