<?php

declare(strict_types=1);

namespace App\Modules\Inquilinos\Models;

use App\Modules\Inquilinos\Database\Factories\InquilinoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @use HasFactory<InquilinoFactory>
 */
final class Inquilino extends Model
{
    /** @use HasFactory<InquilinoFactory> */
    use HasFactory;

    protected $table = 'inquilinos';

    protected $fillable = [
        'nome',
        'cpf',
        'tipo_pessoa',
        'rg',
        'email',
        'telefone',
        'data_nascimento',
        'estado_civil',
        'profissao',
        'renda',
        'endereco',
        'cliente_id',
        'data_cadastro',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data_nascimento' => 'date',
            'renda' => 'decimal:2',
            'data_cadastro' => 'datetime',
        ];
    }

    protected static function newFactory(): InquilinoFactory
    {
        return InquilinoFactory::new();
    }
}
