<?php

declare(strict_types=1);

namespace App\Modules\Inquilinos\Database\Factories;

use App\Modules\Inquilinos\Domain\Enums\EstadoCivil;
use App\Modules\Inquilinos\Domain\Enums\TipoPessoa;
use App\Modules\Inquilinos\Models\Inquilino;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Inquilino>
 */
final class InquilinoFactory extends Factory
{
    protected $model = Inquilino::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nome' => fake()->name(),
            'cpf' => $this->gerarCpf(),
            'tipo_pessoa' => TipoPessoa::Pf->value,
            'rg' => null,
            'email' => fake()->unique()->safeEmail(),
            'telefone' => fake()->numerify('(##) #####-####'),
            'data_nascimento' => fake()->dateTimeBetween('-60 years', '-19 years')->format('Y-m-d'),
            'estado_civil' => $this->estadoCivilAleatorio(),
            'profissao' => fake()->jobTitle(),
            'renda' => fake()->randomFloat(2, 1000, 20000),
            'endereco' => fake()->address(),
            'cliente_id' => null,
            'data_cadastro' => now(),
        ];
    }

    private function estadoCivilAleatorio(): string
    {
        $casos = EstadoCivil::cases();

        return $casos[random_int(0, count($casos) - 1)]->value;
    }

    private function gerarCpf(): string
    {
        $digitos = [];

        for ($posicao = 0; $posicao < 9; $posicao++) {
            $digitos[] = random_int(0, 9);
        }

        $digitos[] = $this->digitoVerificador($digitos, 9);
        $digitos[] = $this->digitoVerificador($digitos, 10);

        return implode('', $digitos);
    }

    /**
     * @param  list<int>  $digitos
     */
    private function digitoVerificador(array $digitos, int $quantidade): int
    {
        $soma = 0;

        for ($posicao = 0; $posicao < $quantidade; $posicao++) {
            $soma += $digitos[$posicao] * ($quantidade + 1 - $posicao);
        }

        $resto = ($soma * 10) % 11;

        return $resto === 10 ? 0 : $resto;
    }
}
