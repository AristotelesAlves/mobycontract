<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Models;

use Illuminate\Database\Eloquent\Model;

final class ContratoAlteracao extends Model
{
    protected $table = 'contrato_alteracoes';

    protected $fillable = [
        'contrato_id',
        'data',
        'usuario_id',
        'usuario',
        'acao',
        'campo',
        'de',
        'para',
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
