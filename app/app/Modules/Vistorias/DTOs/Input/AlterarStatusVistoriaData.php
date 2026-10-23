<?php

declare(strict_types=1);

namespace App\Modules\Vistorias\DTOs\Input;

use App\Modules\Vistorias\Domain\Enums\StatusVistoria;
use App\Shared\Application\Support\DadosDeEntrada;

final readonly class AlterarStatusVistoriaData
{
    public function __construct(
        public StatusVistoria $status,
        public ?string $motivo,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(array $validated): self
    {
        return new self(
            status: StatusVistoria::from(DadosDeEntrada::texto($validated, 'status')),
            motivo: DadosDeEntrada::textoOpcional($validated, 'motivo'),
        );
    }
}
