<?php

declare(strict_types=1);

namespace App\Modules\Modelos\DTOs\Input;

use App\Shared\Application\Support\DadosDeEntrada;

final readonly class ValidarConteudoData
{
    public function __construct(
        public string $conteudoHtml,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(array $validated): self
    {
        return new self(DadosDeEntrada::texto($validated, 'conteudoHtml'));
    }
}
