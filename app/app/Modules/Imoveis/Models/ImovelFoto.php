<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\Models;

use Illuminate\Database\Eloquent\Model;

final class ImovelFoto extends Model
{
    protected $table = 'imovel_fotos';

    protected $fillable = ['imovel_id', 'caminho', 'nome', 'mime'];
}
