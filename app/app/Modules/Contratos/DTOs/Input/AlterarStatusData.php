<?php

declare(strict_types=1);

namespace App\Modules\Contratos\DTOs\Input;

use App\Modules\Contratos\Domain\Enums\StatusContrato;
use App\Shared\Application\Support\DadosDeEntrada;

final readonly class AlterarStatusData
{
    public function __construct(
        public StatusContrato $status,
        public ?string $motivo,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(array $validated): self
    {
        return new self(
            status: StatusContrato::from(DadosDeEntrada::texto($validated, 'status')),
            motivo: DadosDeEntrada::textoOpcional($validated, 'motivo'),
        );
    }
}
