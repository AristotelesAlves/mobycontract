<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\Database\Factories;

use App\Modules\Imoveis\Domain\CompositorDeEndereco;
use App\Modules\Imoveis\Domain\Enums\SituacaoImovel;
use App\Modules\Imoveis\Domain\Enums\TipoImovel;
use App\Modules\Imoveis\Models\Imovel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Imovel>
 */
final class ImovelFactory extends Factory
{
    protected $model = Imovel::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $logradouro = fake()->streetName();
        $numero = (string) fake()->buildingNumber();
        $bairro = fake()->citySuffix();

        return [
            'titulo' => fake()->unique()->words(3, true),
            'tipo' => TipoImovel::Apartamento->value,
            'status' => SituacaoImovel::Disponivel->value,
            'cep' => fake()->numerify('########'),
            'logradouro' => $logradouro,
            'numero' => $numero,
            'complemento' => null,
            'bairro' => $bairro,
            'cidade' => fake()->city(),
            'estado' => fake()->randomElement(['CE', 'SP', 'RJ']),
            'endereco' => CompositorDeEndereco::compor($logradouro, $numero, null, $bairro),
            'area' => fake()->randomFloat(2, 20, 300),
            'quartos' => fake()->numberBetween(0, 4),
            'banheiros' => fake()->numberBetween(0, 3),
            'vagas_garagem' => fake()->numberBetween(0, 2),
            'valor_aluguel' => fake()->randomFloat(2, 500, 10000),
            'valor_iptu' => null,
            'valor_condominio' => null,
            'agua_inclusa' => false,
            'energia_inclusa' => false,
            'internet_inclusa' => false,
            'gas_incluso' => false,
            'matricula' => null,
            'descricao' => null,
        ];
    }

    public function doTipo(TipoImovel $tipo): static
    {
        return $this->state(fn (array $atributos): array => ['tipo' => $tipo->value]);
    }

    public function vendido(): static
    {
        return $this->state(fn (array $atributos): array => ['status' => SituacaoImovel::Vendido->value]);
    }

    public function emNegociacao(): static
    {
        return $this->state(fn (array $atributos): array => ['status' => SituacaoImovel::EmNegociacao->value]);
    }

    public function alugado(): static
    {
        return $this->state(fn (array $atributos): array => ['status' => SituacaoImovel::Alugado->value]);
    }
}
