<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $id
 * @property-read int $contrato_id
 * @property-read string $tipo
 * @property-read string $nome
 * @property-read string $cpf
 * @property-read string $telefone
 * @property-read string|null $email
 */
final class ContratoParte extends Model
{
    protected $table = 'contrato_partes';

    protected $fillable = [
        'contrato_id',
        'tipo',
        'nome',
        'cpf',
        'telefone',
        'email',
    ];

    /**
     * @return BelongsTo<Contrato, $this>
     */
    public function contrato(): BelongsTo
    {
        return $this->belongsTo(Contrato::class, 'contrato_id');
    }
}
