<?php

declare(strict_types=1);

namespace App\Modules\Vistorias\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class VistoriaFoto extends Model
{
    protected $table = 'vistoria_fotos';

    protected $fillable = [
        'vistoria_id',
        'descricao',
        'nome_anexo',
        'anexo',
    ];

    /**
     * @return BelongsTo<Vistoria, $this>
     */
    public function vistoria(): BelongsTo
    {
        return $this->belongsTo(Vistoria::class, 'vistoria_id');
    }
}
