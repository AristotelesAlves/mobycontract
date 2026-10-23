<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Models;

use App\Modules\Contratos\Database\Factories\ContratoFactory;
use App\Modules\Imoveis\Models\Imovel;
use App\Modules\Inquilinos\Models\Inquilino;
use App\Modules\Modelos\Models\Modelo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @use HasFactory<ContratoFactory>
 *
 * @property string|null $documento_hash_blockchain
 */
final class Contrato extends Model
{
    /** @use HasFactory<ContratoFactory> */
    use HasFactory;

    protected $table = 'contratos';

    protected $fillable = [
        'snapshot',
        'numero',
        'tipo_modelo',
        'status',
        'imovel_id',
        'inquilino_id',
        'criado_por',
        'modelo_id',
        'valor_mensal',
        'desconto_mensal',
        'multa_por_atraso',
        'multa_rescisao',
        'garantia',
        'valor_caucao',
        'destinacao',
        'prazo_vigencia',
        'data_inicio',
        'data_fim',
        'indice_reajuste',
        'anexo_contrato_nome',
        'comprovante_garantia',
        'comprovante_garantia_nome',
        'anexo_contrato',
        'observacoes',
        'motivo_cancelamento',
        'documento_fisico_confirmado',
        'dia_vencimento_parcela',
        'hash_blockchain',
        'documento_hash_blockchain',
        'data_registro_blockchain',
        'status_blockchain',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'snapshot' => 'array',
            'valor_mensal' => 'decimal:2',
            'desconto_mensal' => 'decimal:2',
            'multa_por_atraso' => 'decimal:2',
            'multa_rescisao' => 'decimal:2',
            'valor_caucao' => 'decimal:2',
            'data_inicio' => 'date',
            'data_fim' => 'date',
            'documento_fisico_confirmado' => 'boolean',
            'dia_vencimento_parcela' => 'integer',
            'data_registro_blockchain' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Imovel, $this>
     */
    public function imovel(): BelongsTo
    {
        return $this->belongsTo(Imovel::class, 'imovel_id');
    }

    /**
     * @return BelongsTo<Inquilino, $this>
     */
    public function inquilino(): BelongsTo
    {
        return $this->belongsTo(Inquilino::class, 'inquilino_id');
    }

    /**
     * @return BelongsTo<Modelo, $this>
     */
    public function modelo(): BelongsTo
    {
        return $this->belongsTo(Modelo::class, 'modelo_id');
    }

    /**
     * @return HasMany<ContratoParte, $this>
     */
    public function partes(): HasMany
    {
        return $this->hasMany(ContratoParte::class, 'contrato_id');
    }

    /**
     * @return HasMany<ContratoAssinatura, $this>
     */
    public function assinaturas(): HasMany
    {
        return $this->hasMany(ContratoAssinatura::class, 'contrato_id');
    }

    /**
     * @return HasMany<ContratoAditivo, $this>
     */
    public function aditivos(): HasMany
    {
        return $this->hasMany(ContratoAditivo::class, 'contrato_id');
    }

    /**
     * @return HasMany<ContratoParte, $this>
     */
    public function fiadores(): HasMany
    {
        return $this->hasMany(ContratoParte::class, 'contrato_id')->where('tipo', 'fiador');
    }

    /**
     * @return HasMany<ContratoParcela, $this>
     */
    public function parcelas(): HasMany
    {
        return $this->hasMany(ContratoParcela::class, 'contrato_id');
    }

    /**
     * @return HasMany<ContratoEncargoFixo, $this>
     */
    public function encargosFixos(): HasMany
    {
        return $this->hasMany(ContratoEncargoFixo::class, 'contrato_id');
    }

    /**
     * @return HasMany<ContratoUtilidade, $this>
     */
    public function utilidades(): HasMany
    {
        return $this->hasMany(ContratoUtilidade::class, 'contrato_id');
    }

    /**
     * @return HasMany<ContratoHistorico, $this>
     */
    public function historicos(): HasMany
    {
        return $this->hasMany(ContratoHistorico::class, 'contrato_id');
    }

    /**
     * @return HasMany<ContratoAlteracao, $this>
     */
    public function alteracoes(): HasMany
    {
        return $this->hasMany(ContratoAlteracao::class, 'contrato_id');
    }

    protected static function booted(): void
    {
        self::updating(function (self $contrato): void {
            if ($contrato->isDirty('snapshot')) {
                throw new \LogicException('O snapshot contratual é imutável.');
            }
        });
    }

    protected static function newFactory(): ContratoFactory
    {
        return ContratoFactory::new();
    }
}
