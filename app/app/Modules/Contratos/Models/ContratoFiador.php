<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Models;

use Illuminate\Database\Eloquent\Model;

final class ContratoFiador extends Model
{
    protected $table = 'contrato_partes';

    protected $fillable = ['contrato_id', 'tipo', 'nome', 'cpf', 'telefone'];
}
