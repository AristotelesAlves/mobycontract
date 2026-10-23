<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Models;

use Illuminate\Database\Eloquent\Model;

final class ContratoEncargo extends Model
{
    protected $table = 'contrato_encargos';

    protected $fillable = [
        'contrato_id',
        'parcela_id',
        'tipo',
        'valor',
        'motivo',
        'criado_por',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'valor' => 'decimal:2',
        ];
    }
}
