<?php

declare(strict_types=1);

namespace App\Modules\Modelos\Domain;

/**
 * Resultado da análise de variáveis de um conteúdo de modelo (R-8.1).
 */
final readonly class AnaliseDeVariaveis
{
    /**
     * @param  list<string>  $encontradas
     * @param  list<string>  $desconhecidas
     * @param  list<string>  $invalidas
     */
    public function __construct(
        public array $encontradas,
        public array $desconhecidas,
        public array $invalidas,
    ) {}

    /**
     * @return list<string>
     */
    public function caminhosDeAditivo(): array
    {
        return array_values(array_filter(
            $this->encontradas,
            static fn (string $caminho): bool => str_starts_with($caminho, 'aditivo.'),
        ));
    }

    public function possuiInvalida(): bool
    {
        return $this->invalidas !== [];
    }
}
