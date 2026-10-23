<?php

declare(strict_types=1);

namespace Tests\Feature\Inquilinos;

use App\Modules\Inquilinos\Models\Inquilino;
use App\Modules\Usuarios\Models\User;
use App\Shared\Domain\Enums\Papel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class InquilinoControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function dadosValidos(string $cpf = '11144477735'): array
    {
        return [
            'nome' => 'José Ávila',
            'cpf' => $cpf,
            'email' => 'jose.avila@mobycontract.test',
            'telefone' => '(85) 98888-1234',
            'dataNascimento' => now()->subYears(30)->subDay()->format('Y-m-d'),
            'estadoCivil' => 'Solteiro(a)',
            'renda' => 5000,
            'endereco' => 'Rua das Flores, 100',
        ];
    }

    public function test_cria_locatario_pessoa_fisica(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);

        $resposta = $this->actingAs($operador, 'api')
            ->postJson('/api/v1/inquilinos', $this->dadosValidos());

        $resposta->assertCreated()
            ->assertJsonPath('dados.nome', 'José Ávila')
            ->assertJsonPath('dados.tipoPessoa', 'PF')
            ->assertJsonPath('dados.cpf', '11144477735')
            ->assertJsonPath('dados.idade', 30)
            ->assertJsonPath('dados.contratoAtivo', null);

        $this->assertDatabaseHas('inquilinos', ['cpf' => '11144477735', 'tipo_pessoa' => 'PF']);
    }

    public function test_cpf_e_normalizado_para_digitos(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);

        $dados = $this->dadosValidos();
        $dados['cpf'] = '111.444.777-35';

        $this->actingAs($operador, 'api')
            ->postJson('/api/v1/inquilinos', $dados)
            ->assertCreated()
            ->assertJsonPath('dados.cpf', '11144477735');
    }

    public function test_cria_locatario_pessoa_juridica_com_cnpj(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);

        $dados = $this->dadosValidos('11222333000181');
        $dados['tipoPessoa'] = 'PJ';

        $this->actingAs($operador, 'api')
            ->postJson('/api/v1/inquilinos', $dados)
            ->assertCreated()
            ->assertJsonPath('dados.tipoPessoa', 'PJ');
    }

    public function test_cpf_invalido_devolve_erro(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);

        $this->actingAs($operador, 'api')
            ->postJson('/api/v1/inquilinos', $this->dadosValidos('11111111111'))
            ->assertStatus(422)
            ->assertJsonPath('codigo', 'VALIDATION_ERROR');
    }

    public function test_cpf_duplicado_devolve_erro(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);
        $this->actingAs($operador, 'api')->postJson('/api/v1/inquilinos', $this->dadosValidos());

        $dados = $this->dadosValidos();
        $dados['email'] = 'outro@mobycontract.test';

        $this->actingAs($operador, 'api')
            ->postJson('/api/v1/inquilinos', $dados)
            ->assertStatus(422)
            ->assertJsonPath('codigo', 'VALIDATION_ERROR');
    }

    public function test_menor_de_idade_devolve_erro(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);

        $dados = $this->dadosValidos();
        $dados['dataNascimento'] = now()->subYears(10)->format('Y-m-d');

        $this->actingAs($operador, 'api')
            ->postJson('/api/v1/inquilinos', $dados)
            ->assertStatus(422)
            ->assertJsonPath('codigo', 'VALIDATION_ERROR');
    }

    public function test_listagem_filtra_por_texto_e_varios_campos(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);
        Inquilino::factory()->create(['nome' => 'Mariana Souza']);

        $this->actingAs($operador, 'api')
            ->getJson('/api/v1/inquilinos?texto=mariana')
            ->assertOk()
            ->assertJsonPath('dados.total', 1);
    }

    public function test_listagem_com_situacao_sem_contrato_retorna_todos(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);
        Inquilino::factory()->count(2)->create();

        $this->actingAs($operador, 'api')
            ->getJson('/api/v1/inquilinos?situacao=sem_contrato')
            ->assertOk()
            ->assertJsonPath('dados.total', 2);

        $this->actingAs($operador, 'api')
            ->getJson('/api/v1/inquilinos?situacao=com_contrato')
            ->assertOk()
            ->assertJsonPath('dados.total', 0);
    }

    public function test_altera_locatario_parcialmente(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);
        $criado = $this->actingAs($operador, 'api')
            ->postJson('/api/v1/inquilinos', $this->dadosValidos())
            ->json('dados');

        $id = (int) $criado['id'];

        $this->actingAs($operador, 'api')
            ->putJson('/api/v1/inquilinos/'.$id, ['nome' => 'Nome Novo'])
            ->assertOk()
            ->assertJsonPath('dados.nome', 'Nome Novo')
            ->assertJsonPath('dados.cpf', '11144477735');
    }

    public function test_exclui_locatario(): void
    {
        $administrador = User::factory()->create(['papel' => Papel::Administrador->value]);
        $inquilino = Inquilino::factory()->create();

        $this->actingAs($administrador, 'api')
            ->deleteJson('/api/v1/inquilinos/'.$inquilino->id)
            ->assertNoContent();

        $this->assertDatabaseMissing('inquilinos', ['id' => $inquilino->id]);
    }

    public function test_locatario_inexistente_devolve_nao_encontrado(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);

        $this->actingAs($operador, 'api')
            ->getJson('/api/v1/inquilinos/9999')
            ->assertNotFound();
    }

    public function test_indicadores_calculam_limite_seguro(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);
        $inquilino = Inquilino::factory()->create(['renda' => 5000]);

        $this->actingAs($operador, 'api')
            ->getJson('/api/v1/inquilinos/'.$inquilino->id.'/indicadores')
            ->assertOk()
            ->assertJsonPath('dados.idade', $inquilino->data_nascimento->diff(now())->y)
            ->assertJsonPath('dados.limiteSeguro', 1500)
            ->assertJsonPath('dados.compromissoRenda', null)
            ->assertJsonPath('dados.contratosVigentes', 0)
            ->assertJsonPath('dados.parcelasAtrasadas', 0);
    }

    public function test_cadastra_inquilino_sem_endereco_com_profissao_livre(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);
        $dados = $this->dadosValidos();
        unset($dados['endereco']);
        $dados['profissao'] = 'Pesquisador de robótica';
        $this->actingAs($operador, 'api')->postJson('/api/v1/inquilinos', $dados)->assertCreated()->assertJsonPath('dados.profissao', 'Pesquisador de robótica');
        $this->assertDatabaseHas('inquilinos', ['cpf' => $dados['cpf'], 'endereco' => '', 'profissao' => 'Pesquisador de robótica']);
    }

    public function test_lista_profissoes_do_catalogo_com_autenticacao(): void
    {
        $this->getJson('/api/v1/inquilinos/profissoes')->assertUnauthorized();
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);
        $this->actingAs($operador, 'api')->getJson('/api/v1/inquilinos/profissoes')->assertOk()->assertJsonCount(20, 'dados')->assertJsonFragment(['nome' => 'Analista de sistemas']);
    }
}
