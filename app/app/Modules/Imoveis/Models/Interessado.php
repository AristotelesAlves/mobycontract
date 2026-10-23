<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\Models;

use App\Modules\Contratos\Models\Contrato;
use App\Modules\Imoveis\Database\Factories\InteressadoFactory;
use App\Modules\Leads\Models\Lead;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @use HasFactory<InteressadoFactory>
 */
final class Interessado extends Model
{
    /** @use HasFactory<InteressadoFactory> */
    use HasFactory;

    protected $table = 'interessados';

    protected $fillable = [
        'imovel_id',
        'lead_id',
        'contrato_id',
        'nome',
        'telefone',
        'email',
        'tipo',
        'status',
    ];

    /**
     * @return BelongsTo<Imovel, $this>
     */
    public function imovel(): BelongsTo
    {
        return $this->belongsTo(Imovel::class, 'imovel_id');
    }

    /**
     * @return BelongsTo<Lead, $this>
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    /**
     * @return BelongsTo<Contrato, $this>
     */
    public function contrato(): BelongsTo
    {
        return $this->belongsTo(Contrato::class, 'contrato_id');
    }

    protected static function newFactory(): InteressadoFactory
    {
        return InteressadoFactory::new();
    }
}
