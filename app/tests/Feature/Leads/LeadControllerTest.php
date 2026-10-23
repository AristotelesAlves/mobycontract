<?php

declare(strict_types=1);

namespace Tests\Feature\Leads;

use App\Modules\Leads\Domain\Enums\OrigemLead;
use App\Modules\Leads\Domain\Enums\SituacaoLead;
use App\Modules\Leads\Models\Lead;
use App\Modules\Usuarios\Models\User;
use App\Shared\Domain\Enums\Papel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class LeadControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_cria_lead_com_situacao_inicial_novo(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);

        $resposta = $this->actingAs($operador, 'api')->postJson('/api/v1/leads', [
            'nome' => 'Mariana Souza',
            'telefone' => '(85) 99999-1234',
            'email' => 'mariana@mobycontract.test',
            'imovelDeInteresse' => 'Cobertura Aurora',
            'origem' => OrigemLead::Site->value,
            'observacoes' => 'Ligar após as 18h.',
        ]);

        $resposta->assertCreated()
            ->assertJsonPath('dados.nome', 'Mariana Souza')
            ->assertJsonPath('dados.situacao', SituacaoLead::Novo->value)
            ->assertJsonPath('dados.origem', OrigemLead::Site->value);

        $this->assertNotNull($resposta->json('dados.dataCadastro'));
        $this->assertDatabaseHas('leads', ['nome' => 'Mariana Souza', 'status' => 'Novo']);
    }

    public function test_origem_ausente_assume_outro(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);

        $resposta = $this->actingAs($operador, 'api')->postJson('/api/v1/leads', [
            'nome' => 'Sem Origem',
            'telefone' => '85000000000',
        ]);

        $resposta->assertCreated()->assertJsonPath('dados.origem', OrigemLead::Outro->value);
    }

    public function test_nome_e_telefone_sao_obrigatorios(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);

        $this->actingAs($operador, 'api')
            ->postJson('/api/v1/leads', ['nome' => '', 'telefone' => ''])
            ->assertStatus(422)
            ->assertJsonPath('codigo', 'VALIDATION_ERROR');
    }

    public function test_observacoes_acima_do_limite_devolve_erro(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);

        $this->actingAs($operador, 'api')->postJson('/api/v1/leads', [
            'nome' => 'Nome',
            'telefone' => '85000000000',
            'observacoes' => str_repeat('a', 201),
        ])->assertStatus(422)->assertJsonPath('codigo', 'VALIDATION_ERROR');
    }

    public function test_listagem_filtra_por_situacao_e_texto(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);
        Lead::factory()->create(['nome' => 'Mariana Souza']);
        Lead::factory()->comSituacao(SituacaoLead::Desistiu)->create(['nome' => 'Outro Nome']);

        $porSituacao = $this->actingAs($operador, 'api')
            ->getJson('/api/v1/leads?status='.rawurlencode(SituacaoLead::Novo->value));
        $porSituacao->assertOk()->assertJsonPath('dados.total', 1);

        $porTexto = $this->actingAs($operador, 'api')->getJson('/api/v1/leads?texto=mariana');
        $porTexto->assertOk()->assertJsonPath('dados.total', 1);
    }

    public function test_altera_situacao_conforme_maquina_de_estados(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);
        $lead = Lead::factory()->create();

        $this->actingAs($operador, 'api')
            ->putJson('/api/v1/leads/'.$lead->id.'/situacao', ['status' => SituacaoLead::EmContato->value])
            ->assertOk()
            ->assertJsonPath('dados.situacao', SituacaoLead::EmContato->value);

        $this->actingAs($operador, 'api')->putJson('/api/v1/leads/'.$lead->id.'/situacao', [
            'status' => SituacaoLead::Alugou->value,
        ])->assertOk();

        $this->actingAs($operador, 'api')
            ->putJson('/api/v1/leads/'.$lead->id.'/situacao', ['status' => SituacaoLead::EmContato->value])
            ->assertStatus(422)
            ->assertJsonPath('codigo', 'BUSINESS_RULE_VIOLATION');
    }

    public function test_alteracao_generica_nao_muda_a_situacao(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);
        $lead = Lead::factory()->create(['nome' => 'Nome Antigo']);

        $resposta = $this->actingAs($operador, 'api')->putJson('/api/v1/leads/'.$lead->id, [
            'nome' => 'Nome Novo',
            'status' => SituacaoLead::Desistiu->value,
        ]);

        $resposta->assertOk()
            ->assertJsonPath('dados.nome', 'Nome Novo')
            ->assertJsonPath('dados.situacao', SituacaoLead::Novo->value);
    }

    public function test_exclui_lead(): void
    {
        $administrador = User::factory()->create(['papel' => Papel::Administrador->value]);
        $lead = Lead::factory()->create();

        $this->actingAs($administrador, 'api')
            ->deleteJson('/api/v1/leads/'.$lead->id)
            ->assertNoContent();

        $this->assertDatabaseMissing('leads', ['id' => $lead->id]);
    }

    public function test_lead_inexistente_devolve_nao_encontrado(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);

        $this->actingAs($operador, 'api')
            ->getJson('/api/v1/leads/9999')
            ->assertNotFound();
    }
}
