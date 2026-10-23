<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $id
 * @property-read int $contrato_id
 * @property-read string $tipo
 * @property-read string $responsabilidade
 * @property-read float $valor_estimado
 * @property-read bool $ativo
 */
final class ContratoEncargoFixo extends Model
{
    protected $table = 'contrato_encargos_fixos';

    protected $fillable = [
        'contrato_id',
        'tipo',
        'responsabilidade',
        'valor_estimado',
        'ativo',
    ];

    protected $casts = [
        'valor_estimado' => 'decimal:2',
        'ativo' => 'boolean',
    ];

    /**
     * @return BelongsTo<Contrato, $this>
     */
    public function contrato(): BelongsTo
    {
        return $this->belongsTo(Contrato::class, 'contrato_id');
    }
}
