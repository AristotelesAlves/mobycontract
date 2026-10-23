<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ContratoTaxa extends Model
{
    protected $table = 'contrato_taxas';

    protected $fillable = [
        'contrato_id',
        'nome',
        'opcional',
        'responsavel',
        'periodicidade',
        'valor',
        'ativa',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'opcional' => 'boolean',
            'valor' => 'decimal:2',
            'ativa' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Contrato, $this>
     */
    public function contrato(): BelongsTo
    {
        return $this->belongsTo(Contrato::class, 'contrato_id');
    }
}
