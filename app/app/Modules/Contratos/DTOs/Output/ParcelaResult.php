<?php

declare(strict_types=1);

namespace App\Modules\Contratos\DTOs\Output;

use App\Modules\Contratos\Models\ContratoEncargo;
use App\Modules\Contratos\Models\ContratoParcela;
use DateTimeInterface;

final readonly class ParcelaResult
{
    /** @param list<array{id: int, tipo: string, valor: float, motivo: string|null, criadoEm: string|null}> $encargos */
    public function __construct(
        public int $id,
        public int $contratoId,
        public string $referencia,
        public ?string $vencimento,
        public float $valor,
        public float $juros,
        public float $taxasAdicionais,
        public float $totalDaParcela,
        public float $valorPago,
        public ?string $dataPagamento,
        public string $status,
        public float $valorExibido,
        public ?string $pagoEm,
        public array $encargos = [],
    ) {}

    public static function fromModel(ContratoParcela $parcela): self
    {
        $valor = (float) $parcela->valor;
        $juros = (float) $parcela->juros;
        $taxas = (float) $parcela->taxas_adicionais;
        $total = max(0.0, $valor + $juros + $taxas);
        $pago = (float) $parcela->valor_pago;
        $status = (string) $parcela->status;

        return new self(
            id: (int) $parcela->id,
            contratoId: (int) $parcela->contrato_id,
            referencia: (string) $parcela->referencia,
            vencimento: self::dataIso($parcela->vencimento),
            valor: $valor,
            juros: $juros,
            taxasAdicionais: $taxas,
            totalDaParcela: $total,
            valorPago: $pago,
            dataPagamento: self::dataIso($parcela->data_pagamento),
            status: $status,
            valorExibido: $status === 'Pago' ? $pago : $total,
            pagoEm: self::instante($parcela->pago_em),
            encargos: $parcela->relationLoaded('encargos') ? array_values($parcela->encargos->map(static fn (ContratoEncargo $encargo): array => [
                'id' => (int) $encargo->id,
                'tipo' => $encargo->tipo,
                'valor' => (float) $encargo->valor,
                'motivo' => $encargo->motivo,
                'criadoEm' => self::instante($encargo->created_at),
            ])->all()) : [],
        );
    }

    private static function dataIso(mixed $valor): ?string
    {
        return $valor instanceof DateTimeInterface ? $valor->format('Y-m-d') : null;
    }

    private static function instante(mixed $valor): ?string
    {
        return $valor instanceof DateTimeInterface ? $valor->format(DATE_ATOM) : null;
    }
}
