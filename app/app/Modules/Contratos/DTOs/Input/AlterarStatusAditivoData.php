<?php

declare(strict_types=1);

namespace App\Modules\Contratos\DTOs\Input;

use App\Modules\Contratos\Domain\Enums\StatusAditivo;
use App\Shared\Application\Support\DadosDeEntrada;

final readonly class AlterarStatusAditivoData
{
    public function __construct(
        public StatusAditivo $status,
        public ?string $motivo,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(array $validated): self
    {
        return new self(
            status: StatusAditivo::from(DadosDeEntrada::texto($validated, 'status')),
            motivo: DadosDeEntrada::textoOpcional($validated, 'motivo'),
        );
    }
}
