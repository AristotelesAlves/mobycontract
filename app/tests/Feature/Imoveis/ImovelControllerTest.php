<?php

declare(strict_types=1);

namespace Tests\Feature\Imoveis;

use App\Modules\Imoveis\Domain\CompositorDeEndereco;
use App\Modules\Imoveis\Domain\Enums\SituacaoImovel;
use App\Modules\Imoveis\Domain\Enums\TipoImovel;
use App\Modules\Imoveis\Models\Imovel;
use App\Modules\Usuarios\Models\User;
use App\Shared\Domain\Enums\Papel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ImovelControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_operador_cria_imovel_com_endereco_composto(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);

        $resposta = $this->actingAs($operador, 'api')
            ->postJson('/api/v1/imoveis', $this->payload());

        $resposta->assertCreated()
            ->assertJsonPath('dados.tipo', TipoImovel::Apartamento->value)
            ->assertJsonPath('dados.situacao', SituacaoImovel::Disponivel->value)
            ->assertJsonPath('dados.estado', 'CE')
            ->assertJsonPath('dados.endereco', 'Rua das Flores, 100 - Apto 12, Centro')
            ->assertJsonPath('dados.aguaInclusa', false);

        $this->assertDatabaseHas('imoveis', ['titulo' => 'Apartamento central', 'estado' => 'CE']);
    }

    public function test_cep_invalido_devolve_erro_de_validacao(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);

        $resposta = $this->actingAs($operador, 'api')
            ->postJson('/api/v1/imoveis', $this->payload(['cep' => '123']));

        $resposta->assertStatus(422)->assertJsonPath('codigo', 'VALIDATION_ERROR');
    }

    public function test_valor_de_aluguel_zero_devolve_erro_de_validacao(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);

        $resposta = $this->actingAs($operador, 'api')
            ->postJson('/api/v1/imoveis', $this->payload(['valorAluguel' => 0]));

        $resposta->assertStatus(422)->assertJsonPath('codigo', 'VALIDATION_ERROR');
    }

    public function test_listagem_filtra_por_tipo_e_texto(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);
        Imovel::factory()->create(['titulo' => 'Cobertura Aurora', 'tipo' => TipoImovel::Casa->value]);
        Imovel::factory()->create(['titulo' => 'Sala Comercial', 'tipo' => TipoImovel::Comercial->value]);

        $porTipo = $this->actingAs($operador, 'api')->getJson('/api/v1/imoveis?tipo=Casa');
        $porTipo->assertOk()->assertJsonPath('dados.total', 1);

        $porTexto = $this->actingAs($operador, 'api')->getJson('/api/v1/imoveis?texto=aurora');
        $porTexto->assertOk()->assertJsonPath('dados.total', 1);
    }

    public function test_alteracao_recompoe_endereco(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);
        $imovel = Imovel::factory()->create([
            'logradouro' => 'Rua A',
            'numero' => '10',
            'complemento' => 'Casa 2',
            'bairro' => 'Centro',
            'endereco' => CompositorDeEndereco::compor('Rua A', '10', 'Casa 2', 'Centro'),
        ]);

        $resposta = $this->actingAs($operador, 'api')->putJson('/api/v1/imoveis/'.$imovel->id, [
            'numero' => '20',
            'complemento' => null,
        ]);

        $resposta->assertOk()->assertJsonPath('dados.endereco', 'Rua A, 20, Centro');
    }

    public function test_situacao_manual_de_disponibilidade_e_rejeitada(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);
        $imovel = Imovel::factory()->create();

        $resposta = $this->actingAs($operador, 'api')->putJson('/api/v1/imoveis/'.$imovel->id.'/situacao', [
            'status' => SituacaoImovel::Alugado->value,
        ]);

        $resposta->assertStatus(422)->assertJsonPath('codigo', 'BUSINESS_RULE_VIOLATION');
    }

    public function test_vendido_e_irreversivel(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);
        $imovel = Imovel::factory()->create();

        $this->actingAs($operador, 'api')->putJson('/api/v1/imoveis/'.$imovel->id.'/situacao', [
            'status' => SituacaoImovel::EmNegociacao->value,
        ])->assertOk()->assertJsonPath('dados.situacao', SituacaoImovel::EmNegociacao->value);

        $this->actingAs($operador, 'api')->putJson('/api/v1/imoveis/'.$imovel->id.'/situacao', [
            'status' => SituacaoImovel::Vendido->value,
        ])->assertOk()->assertJsonPath('dados.situacao', SituacaoImovel::Vendido->value);

        $this->actingAs($operador, 'api')->putJson('/api/v1/imoveis/'.$imovel->id.'/situacao', [
            'status' => SituacaoImovel::EmNegociacao->value,
        ])->assertStatus(422)->assertJsonPath('codigo', 'BUSINESS_RULE_VIOLATION');
    }

    public function test_custo_mensal_soma_aluguel_condominio_e_iptu_mensal(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);
        $imovel = Imovel::factory()->create([
            'valor_aluguel' => 2400,
            'valor_condominio' => 300,
            'valor_iptu' => 1200,
        ]);

        $resposta = $this->actingAs($operador, 'api')
            ->getJson('/api/v1/imoveis/'.$imovel->id.'/custo-mensal');

        $resposta->assertOk()
            ->assertJsonPath('dados.custoMensal', 2800)
            ->assertJsonPath('dados.valorIptuMensal', 100);
    }

    public function test_apenas_administrador_exclui_imovel(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);
        $admin = User::factory()->administrador()->create();
        $imovel = Imovel::factory()->create();

        $this->actingAs($operador, 'api')
            ->deleteJson('/api/v1/imoveis/'.$imovel->id)
            ->assertForbidden();

        $this->actingAs($admin, 'api')
            ->deleteJson('/api/v1/imoveis/'.$imovel->id)
            ->assertNoContent();

        $this->assertDatabaseMissing('imoveis', ['id' => $imovel->id]);
    }

    public function test_imovel_inexistente_devolve_nao_encontrado(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);

        $this->actingAs($operador, 'api')
            ->getJson('/api/v1/imoveis/9999')
            ->assertNotFound();
    }

    public function test_ocupacao_devolve_situacao_e_contrato_ativo(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);
        $imovel = Imovel::factory()->create();

        $resposta = $this->actingAs($operador, 'api')
            ->getJson('/api/v1/imoveis/'.$imovel->id.'/ocupacao');

        $resposta->assertOk()
            ->assertJsonPath('dados.situacao', SituacaoImovel::Disponivel->value)
            ->assertJsonPath('dados.contratoAtivo', null);
    }

    /**
     * @param  array<string, mixed>  $sobreposicoes
     * @return array<string, mixed>
     */
    private function payload(array $sobreposicoes = []): array
    {
        return array_merge([
            'titulo' => 'Apartamento central',
            'tipo' => TipoImovel::Apartamento->value,
            'cep' => '60000-000',
            'logradouro' => 'Rua das Flores',
            'numero' => '100',
            'complemento' => 'Apto 12',
            'bairro' => 'Centro',
            'cidade' => 'Fortaleza',
            'estado' => 'ce',
            'area' => 85.5,
            'quartos' => 3,
            'banheiros' => 2,
            'vagasGaragem' => 1,
            'valorAluguel' => 2400,
            'valorIptu' => 1200,
            'valorCondominio' => 300,
            'aguaInclusa' => false,
            'energiaInclusa' => false,
            'internetInclusa' => false,
            'gasIncluso' => false,
            'matricula' => null,
            'descricao' => 'Ótimo imóvel.',
        ], $sobreposicoes);
    }

    public function test_nome_proprietario_e_salvo_carregado_e_preservado_ao_editar(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);
        $resposta = $this->actingAs($operador, 'api')->postJson('/api/v1/imoveis', [...$this->payload(), 'nomeProprietario' => 'Maria Proprietária'])->assertCreated()->assertJsonPath('dados.nomeProprietario', 'Maria Proprietária');
        $id = $resposta->json('dados.id');
        $this->getJson("/api/v1/imoveis/{$id}")->assertOk()->assertJsonPath('dados.nomeProprietario', 'Maria Proprietária');
        $this->putJson("/api/v1/imoveis/{$id}", ['titulo' => 'Título atualizado'])->assertOk()->assertJsonPath('dados.nomeProprietario', 'Maria Proprietária');
        $this->putJson("/api/v1/imoveis/{$id}", ['nomeProprietario' => 'João Proprietário'])->assertOk()->assertJsonPath('dados.nomeProprietario', 'João Proprietário');
    }

    public function test_atualiza_proprietario_de_studio_e_cobertura_sem_rejeitar_tipo_existente(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);
        foreach (['Studio', 'Cobertura'] as $tipo) {
            $imovel = Imovel::factory()->create(['tipo' => $tipo]);
            $this->actingAs($operador, 'api')
                ->putJson("/api/v1/imoveis/{$imovel->id}", $this->payload([
                    'tipo' => $tipo,
                    'nomeProprietario' => 'Maria Proprietária',
                ]))
                ->assertOk()
                ->assertJsonPath('dados.tipo', $tipo)
                ->assertJsonPath('dados.nomeProprietario', 'Maria Proprietária');
        }
    }
}
