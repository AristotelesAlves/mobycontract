<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Models;

use App\Modules\Contratos\Database\Factories\ContratoAditivoFactory;
use App\Modules\Modelos\Models\Modelo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property array<string, mixed>|null $snapshot
 *
 * @use HasFactory<ContratoAditivoFactory>
 */
final class ContratoAditivo extends Model
{
    /** @use HasFactory<ContratoAditivoFactory> */
    use HasFactory;

    protected $table = 'contrato_aditivos';

    protected $fillable = [
        'contrato_id',
        'numero',
        'titulo',
        'data',
        'descricao',
        'criado_por',
        'status',
        'motivos',
        'modelo_id',
        'dados_extras',
        'upload_confirmado',
        'anexo_nome',
        'anexo',
        'snapshot',
        'hash_blockchain',
        'data_registro_blockchain',
        'status_blockchain',
        'motivo_cancelamento',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'snapshot' => 'array',
            'data' => 'date',
            'motivos' => 'array',
            'dados_extras' => 'array',
            'upload_confirmado' => 'boolean',
            'data_registro_blockchain' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Contrato, $this>
     */
    public function contrato(): BelongsTo
    {
        return $this->belongsTo(Contrato::class, 'contrato_id');
    }

    /**
     * @return BelongsTo<Modelo, $this>
     */
    public function modelo(): BelongsTo
    {
        return $this->belongsTo(Modelo::class, 'modelo_id');
    }

    /**
     * @return HasMany<ContratoAditivoAssinatura, $this>
     */
    public function assinaturas(): HasMany
    {
        return $this->hasMany(ContratoAditivoAssinatura::class, 'aditivo_id');
    }

    protected static function booted(): void
    {
        self::updating(function (self $aditivo): void {
            if ($aditivo->isDirty('snapshot')) {
                throw new \LogicException('O snapshot do aditivo é imutável.');
            }
        });
    }

    protected static function newFactory(): ContratoAditivoFactory
    {
        return ContratoAditivoFactory::new();
    }
}
