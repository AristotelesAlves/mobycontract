<?php

declare(strict_types=1);

namespace App\Modules\Regras\DTOs\Input;

use App\Shared\Application\Support\DadosDeEntrada;

final readonly class AtualizarRegrasData
{
    public function __construct(
        public bool $contratoUnicoPorImovel,
        public bool $contratoUnicoPorInquilino,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(array $validated): self
    {
        return new self(
            contratoUnicoPorImovel: DadosDeEntrada::booleano($validated, 'contratoUnicoPorImovel'),
            contratoUnicoPorInquilino: DadosDeEntrada::booleano($validated, 'contratoUnicoPorInquilino'),
        );
    }
}
