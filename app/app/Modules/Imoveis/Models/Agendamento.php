<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\Models;

use App\Modules\Imoveis\Database\Factories\AgendamentoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @use HasFactory<AgendamentoFactory>
 */
final class Agendamento extends Model
{
    /** @use HasFactory<AgendamentoFactory> */
    use HasFactory;

    protected $table = 'agendamentos';

    protected $fillable = [
        'imovel_id',
        'interessado_id',
        'data',
        'hora',
        'telefone_contato',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data' => 'date:Y-m-d',
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
     * @return BelongsTo<Interessado, $this>
     */
    public function interessado(): BelongsTo
    {
        return $this->belongsTo(Interessado::class, 'interessado_id');
    }

    protected static function newFactory(): AgendamentoFactory
    {
        return AgendamentoFactory::new();
    }
}
