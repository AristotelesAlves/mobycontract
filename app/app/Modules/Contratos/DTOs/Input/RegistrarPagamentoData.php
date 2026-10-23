<?php

declare(strict_types=1);

namespace App\Modules\Contratos\DTOs\Input;

use App\Shared\Application\Support\DadosDeEntrada;

final readonly class RegistrarPagamentoData
{
    public function __construct(
        public string $dataPagamento,
        public ?float $valorPago,
        public bool $corrigir = false,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(array $validated): self
    {
        return new self(
            dataPagamento: DadosDeEntrada::texto($validated, 'dataPagamento'),
            valorPago: DadosDeEntrada::decimalOpcional($validated, 'valorPago'),
            corrigir: (bool) ($validated['corrigir'] ?? false),
        );
    }
}
