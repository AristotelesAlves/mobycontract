<?php

declare(strict_types=1);

namespace Tests\Feature\Modelos;

use App\Modules\Contratos\Models\Contrato;
use App\Modules\Modelos\Domain\Enums\TipoModelo;
use App\Modules\Modelos\Models\Modelo;
use App\Modules\Usuarios\Models\User;
use App\Shared\Domain\Enums\Garantia;
use App\Shared\Domain\Enums\Papel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ModeloControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function dadosValidos(): array
    {
        return [
            'titulo' => 'Contrato Padrão',
            'tipo' => TipoModelo::Contrato->value,
            'versao' => 1,
            'conteudoHtml' => '<p>Contrato {{ contrato.numero }} de {{ cliente.nome }}</p>',
            'garantia' => Garantia::Caucao->value,
        ];
    }

    public function test_cria_modelo_com_codigo_e_padroes(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);

        $resposta = $this->actingAs($operador, 'api')
            ->postJson('/api/v1/modelos', $this->dadosValidos());

        $resposta->assertCreated()
            ->assertJsonPath('dados.titulo', 'Contrato Padrão')
            ->assertJsonPath('dados.ativo', true)
            ->assertJsonPath('dados.margens.superior', 25)
            ->assertJsonPath('dados.tipografia.corpo', 11)
            ->assertJsonPath('dados.tipografia.entrelinha', 1.5);

        $this->assertIsInt($resposta->json('dados.codigo'));
        $this->assertDatabaseHas('modelos', ['titulo' => 'Contrato Padrão', 'tipo' => 'Contrato']);
    }

    public function test_contrato_exige_garantia(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);
        $dados = $this->dadosValidos();
        unset($dados['garantia']);

        $this->actingAs($operador, 'api')
            ->postJson('/api/v1/modelos', $dados)
            ->assertStatus(422)
            ->assertJsonPath('codigo', 'VALIDATION_ERROR');
    }

    public function test_distrato_descarta_garantia(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);

        $dados = $this->dadosValidos();
        $dados['tipo'] = TipoModelo::Distrato->value;
        $dados['garantia'] = Garantia::Caucao->value;

        $this->actingAs($operador, 'api')
            ->postJson('/api/v1/modelos', $dados)
            ->assertCreated()
            ->assertJsonPath('dados.garantia', null);
    }

    public function test_conteudo_com_variavel_invalida_devolve_erro(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);

        $dados = $this->dadosValidos();
        $dados['conteudoHtml'] = '<p>{{ imovel titulo }}</p>';

        $this->actingAs($operador, 'api')
            ->postJson('/api/v1/modelos', $dados)
            ->assertStatus(422)
            ->assertJsonPath('codigo', 'VALIDATION_ERROR');
    }

    public function test_remove_codigo_executavel_do_html_do_modelo(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);
        $dados = $this->dadosValidos();
        $dados['conteudoHtml'] = '<p onclick="alert(1)">Seguro</p><script>alert(1)</script><img src=x onerror="alert(1)">';

        $resposta = $this->actingAs($operador, 'api')
            ->postJson('/api/v1/modelos', $dados)
            ->assertCreated();

        $conteudo = (string) $resposta->json('dados.conteudoHtml');
        self::assertStringContainsString('Seguro', $conteudo);
        self::assertStringNotContainsString('script', strtolower($conteudo));
        self::assertStringNotContainsString('onclick', strtolower($conteudo));
        self::assertStringNotContainsString('onerror', strtolower($conteudo));
        self::assertStringNotContainsString('<img', strtolower($conteudo));
    }

    public function test_modelo_de_contrato_rejeita_variavel_de_aditivo(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);

        $dados = $this->dadosValidos();
        $dados['conteudoHtml'] = '<p>{{ aditivo.numero }}</p>';

        $this->actingAs($operador, 'api')
            ->postJson('/api/v1/modelos', $dados)
            ->assertStatus(422)
            ->assertJsonPath('codigo', 'VALIDATION_ERROR');
    }

    public function test_modelo_de_aditivo_aceita_variavel_de_aditivo(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);

        $dados = $this->dadosValidos();
        $dados['tipo'] = TipoModelo::Aditivo->value;
        $dados['conteudoHtml'] = '<p>{{ aditivo.title }}</p>';

        $this->actingAs($operador, 'api')
            ->postJson('/api/v1/modelos', $dados)
            ->assertCreated();
    }

    public function test_margens_fora_do_limite_sao_limitadas(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);

        $dados = $this->dadosValidos();
        $dados['margens'] = ['superior' => 999, 'direita' => 1];

        $this->actingAs($operador, 'api')
            ->postJson('/api/v1/modelos', $dados)
            ->assertCreated()
            ->assertJsonPath('dados.margens.superior', 50)
            ->assertJsonPath('dados.margens.direita', 5);
    }

    public function test_listagem_filtra_por_tipo_ativo_e_texto(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);
        Modelo::factory()->create(['titulo' => 'Modelo Alfa']);
        Modelo::factory()->inativo()->comTipo(TipoModelo::Distrato)->create(['titulo' => 'Modelo Beta']);

        $this->actingAs($operador, 'api')
            ->getJson('/api/v1/modelos?tipo='.rawurlencode(TipoModelo::Contrato->value))
            ->assertOk()
            ->assertJsonPath('dados.total', 1);

        $this->actingAs($operador, 'api')
            ->getJson('/api/v1/modelos?ativo=0')
            ->assertOk()
            ->assertJsonPath('dados.total', 1);

        $this->actingAs($operador, 'api')
            ->getJson('/api/v1/modelos?texto=alfa')
            ->assertOk()
            ->assertJsonPath('dados.total', 1);
    }

    public function test_obter_modelo_devolve_caminhos_conhecidos(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);
        $modelo = Modelo::factory()->create();

        $resposta = $this->actingAs($operador, 'api')->getJson('/api/v1/modelos/'.$modelo->id);

        $resposta->assertOk()->assertJsonPath('dados.caminhosConhecidos.0.caminho', 'imovel.titulo');
        $this->assertNotEmpty($resposta->json('dados.caminhosConhecidos'));
    }

    public function test_altera_parcialmente_e_ignora_status(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);
        $modelo = Modelo::factory()->create(['titulo' => 'Antigo', 'tipo' => TipoModelo::Distrato->value, 'garantia' => null]);

        $this->actingAs($operador, 'api')
            ->putJson('/api/v1/modelos/'.$modelo->id, ['titulo' => 'Novo', 'status' => false])
            ->assertOk()
            ->assertJsonPath('dados.titulo', 'Novo')
            ->assertJsonPath('dados.ativo', true);
    }

    public function test_clonar_inativa_original_e_incrementa_versao(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);
        $modelo = Modelo::factory()->create(['codigo' => 500000, 'versao' => 3]);

        $resposta = $this->actingAs($operador, 'api')->postJson('/api/v1/modelos/'.$modelo->id.'/clonar');

        $resposta->assertCreated()->assertJsonPath('dados.versao', 4);
        $this->assertDatabaseHas('modelos', ['id' => $modelo->id, 'status' => false]);
        $this->assertNotSame($modelo->codigo, $resposta->json('dados.codigo'));
    }

    public function test_ativar_e_inativar_modelo(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);
        $modelo = Modelo::factory()->create();

        $this->actingAs($operador, 'api')
            ->putJson('/api/v1/modelos/'.$modelo->id.'/inativar')
            ->assertOk()
            ->assertJsonPath('dados.ativo', false);

        $this->actingAs($operador, 'api')
            ->putJson('/api/v1/modelos/'.$modelo->id.'/ativar')
            ->assertOk()
            ->assertJsonPath('dados.ativo', true);
    }

    public function test_validar_conteudo_classifica_variaveis(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);

        $this->actingAs($operador, 'api')
            ->postJson('/api/v1/modelos/validar-conteudo', [
                'conteudoHtml' => '{{ contrato.numero }} {{ foo.bar }} {{ imovel titulo }}',
            ])
            ->assertOk()
            ->assertJsonPath('dados.variaveisEncontradas', ['contrato.numero'])
            ->assertJsonPath('dados.variaveisDesconhecidas', ['foo.bar'])
            ->assertJsonPath('dados.variaveisInvalidas', ['imovel titulo']);
    }

    public function test_alterar_tipo_para_distrato_apaga_garantia(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);
        $modelo = Modelo::factory()->create(['tipo' => TipoModelo::Contrato->value, 'garantia' => Garantia::Caucao->value]);

        $this->actingAs($operador, 'api')
            ->putJson('/api/v1/modelos/'.$modelo->id, ['tipo' => TipoModelo::Distrato->value])
            ->assertOk()
            ->assertJsonPath('dados.garantia', null);
    }

    public function test_modelo_inexistente_devolve_nao_encontrado(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);

        $this->actingAs($operador, 'api')->getJson('/api/v1/modelos/9999')->assertNotFound();
    }

    public function test_modelo_vinculado_a_contrato_nao_pode_ser_editado_mas_pode_ser_clonado(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);
        $modelo = Modelo::factory()->create(['tipo' => 'Contrato', 'garantia' => 'Caução']);
        Contrato::factory()->create(['modelo_id' => $modelo->id]);
        $titulo = $modelo->titulo;
        $this->actingAs($operador, 'api')->getJson("/api/v1/modelos/{$modelo->id}")
            ->assertOk()->assertJsonPath('dados.podeAlterar', false)->assertJsonPath('dados.vinculadoAContrato', true);
        $this->putJson("/api/v1/modelos/{$modelo->id}", ['titulo' => 'Alteração bloqueada'])
            ->assertUnprocessable()->assertJsonPath('codigo', 'BUSINESS_RULE_VIOLATION');
        $this->assertSame($titulo, $modelo->fresh()->titulo);
        $this->postJson("/api/v1/modelos/{$modelo->id}/clonar")->assertCreated()->assertJsonPath('dados.podeAlterar', true);
    }

    public function test_modelo_sem_contrato_pode_ser_editado(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);
        $modelo = Modelo::factory()->create(['tipo' => 'Contrato', 'garantia' => 'Caução']);
        $this->actingAs($operador, 'api')->getJson("/api/v1/modelos/{$modelo->id}")->assertOk()->assertJsonPath('dados.podeAlterar', true);
        $this->putJson("/api/v1/modelos/{$modelo->id}", ['titulo' => 'Título atualizado'])->assertOk()->assertJsonPath('dados.titulo', 'Título atualizado');
    }
}
