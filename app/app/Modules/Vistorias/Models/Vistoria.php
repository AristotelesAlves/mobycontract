<?php

declare(strict_types=1);

namespace App\Modules\Vistorias\Models;

use App\Modules\Contratos\Models\Contrato;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Vistoria extends Model
{
    protected $table = 'vistorias';

    protected $fillable = [
        'contrato_id',
        'numero',
        'tipo',
        'vistoriador',
        'data',
        'status',
        'observacoes',
        'chave_acesso',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data' => 'date',
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
     * @return HasMany<VistoriaItem, $this>
     */
    public function itens(): HasMany
    {
        return $this->hasMany(VistoriaItem::class, 'vistoria_id');
    }

    /**
     * @return HasMany<VistoriaFoto, $this>
     */
    public function fotos(): HasMany
    {
        return $this->hasMany(VistoriaFoto::class, 'vistoria_id');
    }
}
