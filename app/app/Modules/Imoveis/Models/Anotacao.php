<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\Models;

use App\Modules\Usuarios\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Anotacao extends Model
{
    protected $table = 'anotacoes';

    protected $fillable = [
        'imovel_id',
        'texto',
        'autor_id',
    ];

    /**
     * @return BelongsTo<Imovel, $this>
     */
    public function imovel(): BelongsTo
    {
        return $this->belongsTo(Imovel::class, 'imovel_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autor_id');
    }
}
