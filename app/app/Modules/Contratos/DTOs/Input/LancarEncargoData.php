<?php

declare(strict_types=1);

namespace App\Modules\Contratos\DTOs\Input;

use App\Modules\Contratos\Domain\Enums\TipoEncargo;
use App\Shared\Application\Support\DadosDeEntrada;

final readonly class LancarEncargoData
{
    public function __construct(
        public TipoEncargo $tipo,
        public float $valor,
        public string $motivo,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(array $validated): self
    {
        return new self(
            tipo: TipoEncargo::from(DadosDeEntrada::texto($validated, 'tipo')),
            valor: DadosDeEntrada::decimal($validated, 'valor'),
            motivo: DadosDeEntrada::texto($validated, 'motivo'),
        );
    }
}
