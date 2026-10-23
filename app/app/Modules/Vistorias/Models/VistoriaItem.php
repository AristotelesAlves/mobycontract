<?php

declare(strict_types=1);

namespace App\Modules\Vistorias\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class VistoriaItem extends Model
{
    protected $table = 'vistoria_itens';

    protected $fillable = [
        'vistoria_id',
        'ambiente',
        'item',
        'estado',
        'observacao',
    ];

    /**
     * @return BelongsTo<Vistoria, $this>
     */
    public function vistoria(): BelongsTo
    {
        return $this->belongsTo(Vistoria::class, 'vistoria_id');
    }
}
