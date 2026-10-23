<?php

declare(strict_types=1);

namespace App\Shared\Domain\Enums;

/**
 * Tipo de garantia locatícia (R-3.5).
 */
enum Garantia: string
{
    case Caucao = 'Caução';
    case Fiador = 'Fiador';
    case SeguroFianca = 'Seguro-fiança';
    case TituloDeCapitalizacao = 'Título de capitalização';
    case SemGarantia = 'Sem garantia';
}
