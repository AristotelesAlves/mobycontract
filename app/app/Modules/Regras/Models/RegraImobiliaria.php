<?php

declare(strict_types=1);

namespace App\Modules\Regras\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Configuração global de regras imobiliárias.
 * Otimizado para linha única (id = 1) por design.
 */
final class RegraImobiliaria extends Model
{
    protected $table = 'regras_imobiliarias';

    protected $fillable = [
        'contrato_unico_por_imovel',
        'contrato_unico_por_inquilino',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'contrato_unico_por_imovel' => 'boolean',
            'contrato_unico_por_inquilino' => 'boolean',
        ];
    }
}
