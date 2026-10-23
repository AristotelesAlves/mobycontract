<?php

declare(strict_types=1);

namespace App\Modules\Modelos\Models;

use App\Modules\Contratos\Models\Contrato;
use App\Modules\Modelos\Database\Factories\ModeloFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @use HasFactory<ModeloFactory>
 */
final class Modelo extends Model
{
    /** @return HasMany<Contrato, $this> */
    public function contratos(): HasMany
    {
        return $this->hasMany(Contrato::class, 'modelo_id');
    }

    /** @use HasFactory<ModeloFactory> */
    use HasFactory;

    protected $table = 'modelos';

    protected $fillable = [
        'codigo',
        'titulo',
        'tipo',
        'status',
        'versao',
        'conteudo_html',
        'garantia',
        'margens',
        'tipografia',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'codigo' => 'integer',
            'status' => 'boolean',
            'versao' => 'integer',
            'margens' => 'array',
            'tipografia' => 'array',
        ];
    }

    protected static function newFactory(): ModeloFactory
    {
        return ModeloFactory::new();
    }
}
