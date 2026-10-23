<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ContratoAditivoAssinatura extends Model
{
    protected $table = 'contrato_aditivo_assinaturas';

    protected $fillable = [
        'aditivo_id',
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

    /**
     * @return BelongsTo<ContratoAditivo, $this>
     */
    public function aditivo(): BelongsTo
    {
        return $this->belongsTo(ContratoAditivo::class, 'aditivo_id');
    }
}
