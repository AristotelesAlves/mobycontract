<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Models;

use Illuminate\Database\Eloquent\Model;

final class ContratoHistorico extends Model
{
    protected $table = 'contrato_historicos';

    protected $fillable = [
        'contrato_id',
        'data',
        'evento',
        'detalhe',
        'usuario_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data' => 'datetime',
        ];
    }
}
