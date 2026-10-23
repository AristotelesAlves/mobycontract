<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\DTOs\Output;

final readonly class OcupacaoResult
{
    /**
     * @param  array<string, mixed>|null  $contratoAtivo
     */
    public function __construct(
        public string $situacao,
        public ?array $contratoAtivo,
    ) {}
}
