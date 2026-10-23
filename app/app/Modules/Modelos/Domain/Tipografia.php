<?php

declare(strict_types=1);

namespace App\Modules\Modelos\Domain;

use App\Shared\Application\Support\DadosDeEntrada;

/**
 * Tipografia do documento (R-8.6). Corpo e entrelinha são normalizados, nunca rejeitados (R-8.6.1).
 */
final class Tipografia
{
    public const string FONTE = 'Arial';

    public const int CORPO_PADRAO = 11;

    public const int CORPO_MINIMO = 6;

    public const int CORPO_MAXIMO = 48;

    public const float ENTRELINHA_PADRAO = 1.5;

    /**
     * @var list<float>
     */
    public const array ENTRELINHAS_PERMITIDAS = [1.0, 1.15, 1.5, 2.0];

    /**
     * @return array<string, mixed>
     */
    public static function padrao(): array
    {
        return [
            'fonte' => self::FONTE,
            'corpo' => self::CORPO_PADRAO,
            'entrelinha' => self::ENTRELINHA_PADRAO,
        ];
    }

    /**
     * @param  array<string, mixed>  $tipografia
     * @return array<string, mixed>
     */
    public static function normalizar(array $tipografia): array
    {
        return [
            'fonte' => self::FONTE,
            'corpo' => max(self::CORPO_MINIMO, min(self::CORPO_MAXIMO, DadosDeEntrada::inteiro($tipografia, 'corpo', self::CORPO_PADRAO))),
            'entrelinha' => self::entrelinha(DadosDeEntrada::decimalOpcional($tipografia, 'entrelinha')),
        ];
    }

    private static function entrelinha(?float $valor): float
    {
        if ($valor === null) {
            return self::ENTRELINHA_PADRAO;
        }

        foreach (self::ENTRELINHAS_PERMITIDAS as $permitida) {
            if (abs($permitida - $valor) < 0.001) {
                return $permitida;
            }
        }

        return self::ENTRELINHA_PADRAO;
    }
}
