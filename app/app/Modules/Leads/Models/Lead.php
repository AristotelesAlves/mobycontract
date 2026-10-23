<?php

declare(strict_types=1);

namespace App\Modules\Leads\Models;

use App\Modules\Leads\Database\Factories\LeadFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @use HasFactory<LeadFactory>
 */
final class Lead extends Model
{
    /** @use HasFactory<LeadFactory> */
    use HasFactory;

    protected $table = 'leads';

    protected $fillable = [
        'nome',
        'telefone',
        'email',
        'imovel_de_interesse',
        'origem',
        'status',
        'observacoes',
        'convertido_em',
        'data_cadastro',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data_cadastro' => 'datetime',
            'convertido_em' => 'datetime',
        ];
    }

    protected static function newFactory(): LeadFactory
    {
        return LeadFactory::new();
    }
}
