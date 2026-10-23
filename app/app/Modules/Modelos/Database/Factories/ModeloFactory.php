<?php

declare(strict_types=1);

namespace App\Modules\Modelos\Database\Factories;

use App\Modules\Modelos\Domain\Enums\TipoModelo;
use App\Modules\Modelos\Domain\GeometriaDaPagina;
use App\Modules\Modelos\Domain\Tipografia;
use App\Modules\Modelos\Models\Modelo;
use App\Shared\Domain\Enums\Garantia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Modelo>
 */
final class ModeloFactory extends Factory
{
    protected $model = Modelo::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'codigo' => fake()->unique()->numberBetween(1, 100000),
            'titulo' => 'Contrato Padrão '.fake()->word(),
            'tipo' => TipoModelo::Contrato->value,
            'status' => true,
            'versao' => 1,
            'conteudo_html' => '<p>Contrato {{ contrato.numero }} — {{ cliente.nome }}</p>',
            'garantia' => Garantia::SemGarantia->value,
            'margens' => GeometriaDaPagina::padrao(),
            'tipografia' => Tipografia::padrao(),
        ];
    }

    public function comTipo(TipoModelo $tipo): static
    {
        return $this->state(fn (): array => [
            'tipo' => $tipo->value,
            'garantia' => $tipo->exigeGarantia() ? Garantia::Caucao->value : null,
        ]);
    }

    public function inativo(): static
    {
        return $this->state(fn (): array => ['status' => false]);
    }
}
