<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Models;

use Illuminate\Database\Eloquent\Model;

final class ContratoAssinatura extends Model
{
    protected $table = 'contrato_assinaturas';

    protected $fillable = [
        'contrato_id',
        'papel',
        'nome',
        'assinado',
        'data_assinatura',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'assinado' => 'boolean',
            'data_assinatura' => 'datetime',
        ];
    }
}
