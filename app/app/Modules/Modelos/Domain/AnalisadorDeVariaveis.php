<?php

declare(strict_types=1);

namespace App\Modules\Modelos\Domain;

/**
 * Analisa a gramática `{{ caminho }}` do conteúdo de um modelo (R-8.1).
 */
final class AnalisadorDeVariaveis
{
    private const string GRAMATICA = '/^[A-Za-z0-9_]+(?:\.[A-Za-z0-9_]+)+$/';

    public function analisar(string $conteudoHtml): AnaliseDeVariaveis
    {
        $encontradas = [];
        $desconhecidas = [];
        $invalidas = [];

        if (preg_match_all('/\{\{(.*?)\}\}/s', $conteudoHtml, $ocorrencias) === false) {
            return new AnaliseDeVariaveis([], [], []);
        }

        /** @var list<string> $brutas */
        $brutas = $ocorrencias[1];

        foreach ($brutas as $bruta) {
            $caminho = trim($bruta);

            if (preg_match(self::GRAMATICA, $caminho) !== 1) {
                $invalidas[] = $caminho;

                continue;
            }

            if (CatalogoDeVariaveis::conhecido($caminho)) {
                $encontradas[] = $caminho;

                continue;
            }

            $desconhecidas[] = $caminho;
        }

        return new AnaliseDeVariaveis(
            array_values(array_unique($encontradas)),
            array_values(array_unique($desconhecidas)),
            array_values(array_unique($invalidas)),
        );
    }
}
