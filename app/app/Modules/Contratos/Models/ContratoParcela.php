<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ContratoParcela extends Model
{
    /** @return HasMany<ContratoEncargo, $this> */
    public function encargos(): HasMany
    {
        return $this->hasMany(ContratoEncargo::class, 'parcela_id');
    }

    protected $table = 'contrato_parcelas';

    protected $fillable = [
        'contrato_id',
        'referencia',
        'vencimento',
        'valor',
        'juros',
        'taxas_adicionais',
        'valor_pago',
        'data_pagamento',
        'status',
        'pago_em',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'vencimento' => 'date',
            'valor' => 'decimal:2',
            'juros' => 'decimal:2',
            'taxas_adicionais' => 'decimal:2',
            'valor_pago' => 'decimal:2',
            'data_pagamento' => 'date',
            'pago_em' => 'datetime',
        ];
    }
}
