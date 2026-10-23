<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Manutencao extends Model
{
    protected $table = 'manutencoes';

    protected $fillable = [
        'imovel_id',
        'titulo',
        'descricao',
        'responsavel',
        'custo',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'custo' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Imovel, $this>
     */
    public function imovel(): BelongsTo
    {
        return $this->belongsTo(Imovel::class, 'imovel_id');
    }
}
