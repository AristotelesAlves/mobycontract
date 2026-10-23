<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\Models;

use App\Modules\Imoveis\Database\Factories\ImovelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @use HasFactory<ImovelFactory>
 *
 * @property string|null $nome_proprietario
 */
final class Imovel extends Model
{
    /** @use HasFactory<ImovelFactory> */
    use HasFactory;

    protected $table = 'imoveis';

    protected $fillable = [
        'nome_proprietario',
        'titulo',
        'tipo',
        'status',
        'cep',
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'estado',
        'endereco',
        'area',
        'quartos',
        'banheiros',
        'vagas_garagem',
        'valor_aluguel',
        'valor_iptu',
        'valor_condominio',
        'agua_inclusa',
        'energia_inclusa',
        'internet_inclusa',
        'gas_incluso',
        'matricula',
        'descricao',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'area' => 'decimal:2',
            'quartos' => 'integer',
            'banheiros' => 'integer',
            'vagas_garagem' => 'integer',
            'valor_aluguel' => 'decimal:2',
            'valor_iptu' => 'decimal:2',
            'valor_condominio' => 'decimal:2',
            'agua_inclusa' => 'boolean',
            'energia_inclusa' => 'boolean',
            'internet_inclusa' => 'boolean',
            'gas_incluso' => 'boolean',
        ];
    }

    protected static function newFactory(): ImovelFactory
    {
        return ImovelFactory::new();
    }
}
