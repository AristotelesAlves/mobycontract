<?php

declare(strict_types=1);

namespace App\Modules\Modelos\DTOs\Output;

final readonly class VariavelConhecidaResult
{
    public function __construct(
        public string $caminho,
        public string $origem,
        public string $exemplo,
    ) {}

    /**
     * @return array<string, string>
     */
    public function paraArray(): array
    {
        return [
            'caminho' => $this->caminho,
            'origem' => $this->origem,
            'exemplo' => $this->exemplo,
        ];
    }
}
