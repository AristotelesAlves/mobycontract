<?php

declare(strict_types=1);

namespace App\Modules\Modelos\Domain;

use App\Shared\Application\Support\DadosDeEntrada;

/**
 * Geometria da página do documento (R-8.5). Margens fora do limite são limitadas (R-8.5.1).
 */
final class GeometriaDaPagina
{
    public const int LARGURA_MM = 210;

    public const int ALTURA_MM = 297;

    public const int ESPACO_ENTRE_PAGINAS_MM = 12;

    public const int LIMITE_MINIMO = 5;

    public const int LIMITE_MAXIMO = 50;

    public const int SUPERIOR_PADRAO = 25;

    public const int DIREITA_PADRAO = 20;

    public const int INFERIOR_PADRAO = 25;

    public const int ESQUERDA_PADRAO = 20;

    /**
     * @return array<string, int>
     */
    public static function padrao(): array
    {
        return [
            'superior' => self::SUPERIOR_PADRAO,
            'direita' => self::DIREITA_PADRAO,
            'inferior' => self::INFERIOR_PADRAO,
            'esquerda' => self::ESQUERDA_PADRAO,
            'espacoEntrePaginas' => self::ESPACO_ENTRE_PAGINAS_MM,
        ];
    }

    /**
     * @param  array<string, mixed>  $margens
     * @return array<string, int>
     */
    public static function normalizar(array $margens): array
    {
        return [
            'superior' => self::limitar(DadosDeEntrada::inteiro($margens, 'superior', self::SUPERIOR_PADRAO)),
            'direita' => self::limitar(DadosDeEntrada::inteiro($margens, 'direita', self::DIREITA_PADRAO)),
            'inferior' => self::limitar(DadosDeEntrada::inteiro($margens, 'inferior', self::INFERIOR_PADRAO)),
            'esquerda' => self::limitar(DadosDeEntrada::inteiro($margens, 'esquerda', self::ESQUERDA_PADRAO)),
            'espacoEntrePaginas' => self::ESPACO_ENTRE_PAGINAS_MM,
        ];
    }

    private static function limitar(int $valor): int
    {
        return max(self::LIMITE_MINIMO, min(self::LIMITE_MAXIMO, $valor));
    }
}
