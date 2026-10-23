<?php

declare(strict_types=1);

namespace Tests\Feature\Usuarios;

use App\Modules\Usuarios\Models\User;
use App\Shared\Domain\Enums\Papel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

final class UsuarioControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrador_cria_usuario(): void
    {
        $admin = User::factory()->administrador()->create();

        $resposta = $this->actingAs($admin, 'api')->postJson('/api/v1/usuarios', [
            'nome' => 'Beatriz Nogueira',
            'email' => 'beatriz@mobycontract.test',
            'senha' => 'Senha1234',
            'papel' => Papel::Operador->value,
        ]);

        $resposta->assertCreated()
            ->assertJsonPath('dados.nome', 'Beatriz Nogueira')
            ->assertJsonPath('dados.email', 'beatriz@mobycontract.test')
            ->assertJsonPath('dados.papel', Papel::Operador->value)
            ->assertJsonPath('dados.ativo', true);

        $this->assertDatabaseHas('users', ['email' => 'beatriz@mobycontract.test']);
    }

    public function test_email_duplicado_devolve_conflito(): void
    {
        $admin = User::factory()->administrador()->create();
        User::factory()->create(['email' => 'repetido@mobycontract.test']);

        $resposta = $this->actingAs($admin, 'api')->postJson('/api/v1/usuarios', [
            'nome' => 'Outro Nome',
            'email' => 'repetido@mobycontract.test',
            'senha' => 'Senha1234',
            'papel' => Papel::Operador->value,
        ]);

        $resposta->assertStatus(409)->assertJsonPath('codigo', 'CONFLICT');
    }

    public function test_operador_nao_pode_listar_usuarios(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);

        $this->actingAs($operador, 'api')
            ->getJson('/api/v1/usuarios')
            ->assertForbidden();
    }

    public function test_administrador_nao_pode_alterar_o_proprio_papel(): void
    {
        $admin = User::factory()->administrador()->create();

        $resposta = $this->actingAs($admin, 'api')->putJson('/api/v1/usuarios/'.$admin->id, [
            'papel' => Papel::Operador->value,
        ]);

        $resposta->assertStatus(422)->assertJsonPath('codigo', 'BUSINESS_RULE_VIOLATION');
    }

    public function test_busca_por_texto_ignora_acento(): void
    {
        $admin = User::factory()->administrador()->create();
        User::factory()->create(['nome' => 'São Rodrigues']);

        $resposta = $this->actingAs($admin, 'api')
            ->getJson('/api/v1/usuarios?texto=sao');

        $resposta->assertOk()->assertJsonPath('dados.total', 1);
    }

    public function test_login_devolve_token(): void
    {
        /**
         * O login emite token Passport: o client pessoal precisa existir na
         * base de testes também.
         */
        Artisan::call('passport:client', ['--personal' => true, '--name' => 'Teste Pessoal']);

        User::factory()->create([
            'email' => 'login@mobycontract.test',
            'password' => 'Senha1234',
        ]);

        $resposta = $this->postJson('/api/v1/usuarios/sessao/entrar', [
            'email' => 'login@mobycontract.test',
            'senha' => 'Senha1234',
        ]);

        $resposta->assertOk()
            ->assertJsonStructure(['dados' => ['expira_em', 'usuario' => ['id', 'nome', 'email', 'papel']]])
            ->assertCookie('mobycontract_access')
            ->assertJsonMissingPath('dados.access_token');

    }
}
