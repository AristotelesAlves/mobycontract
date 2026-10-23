<?php

declare(strict_types=1);

namespace App\Modules\Leads\Database\Factories;

use App\Modules\Leads\Domain\Enums\OrigemLead;
use App\Modules\Leads\Domain\Enums\SituacaoLead;
use App\Modules\Leads\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
final class LeadFactory extends Factory
{
    protected $model = Lead::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nome' => fake()->name(),
            'telefone' => fake()->numerify('(##) #####-####'),
            'email' => fake()->safeEmail(),
            'imovel_de_interesse' => fake()->sentence(3),
            'origem' => OrigemLead::Site->value,
            'status' => SituacaoLead::Novo->value,
            'observacoes' => null,
            'convertido_em' => null,
            'data_cadastro' => now(),
        ];
    }

    public function comSituacao(SituacaoLead $situacao): static
    {
        return $this->state(fn (array $atributos): array => ['status' => $situacao->value]);
    }
}
