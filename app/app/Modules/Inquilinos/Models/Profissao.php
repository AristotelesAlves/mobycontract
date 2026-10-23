<?php

declare(strict_types=1);

namespace App\Modules\Inquilinos\Models;

use Illuminate\Database\Eloquent\Model;

final class Profissao extends Model
{
    public $timestamps = false;

    protected $table = 'profissoes';
}
