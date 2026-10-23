<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Divida extends Model
{
    protected $table = 'dividas';

    protected $fillable = [
        'contrato_id',
        'descricao',
        'valor',
        'status',
        'data_quitacao',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'valor' => 'decimal:2',
            'data_quitacao' => 'date',
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
