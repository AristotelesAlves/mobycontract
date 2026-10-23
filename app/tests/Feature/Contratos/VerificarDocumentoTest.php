<?php

declare(strict_types=1);

namespace Tests\Feature\Contratos;

use App\Modules\Contratos\Models\Contrato;
use App\Modules\Usuarios\Models\User;
use App\Shared\Contracts\ExternalHttpClient;
use App\Shared\Domain\Enums\Papel;
use App\Shared\Infrastructure\Blockchain\MobyLedgerClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class VerificarDocumentoTest extends TestCase
{
    use RefreshDatabase;

    private const PDF = "%PDF-1.7\nDocumento assinado\n%%EOF";

    protected function setUp(): void
    {
        parent::setUp();
        config(['mobycontract.blockchain.registry_url' => 'http://registry.test', 'mobycontract.blockchain.api_key' => 'chave-teste']);
    }

    private function contrato(): Contrato
    {
        return Contrato::factory()->create([
            'hash_blockchain' => '0x'.str_repeat('a', 64),
            'documento_hash_blockchain' => '0x'.hash('sha256', self::PDF),
        ]);
    }

    private function consultar(Contrato $contrato)
    {
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);

        return $this->actingAs($operador, 'api')->post("/api/v1/contratos/{$contrato->id}/verificar-documento", [
            'arquivo' => UploadedFile::fake()->createWithContent('contrato.pdf', self::PDF),
        ], ['Accept' => 'application/json']);
    }

    public function test_confirma_arquivo_consultando_hash_real_na_blockchain(): void
    {
        $contrato = $this->contrato();
        $hash = '0x'.hash('sha256', self::PDF);
        Http::fake(['registry.test/*' => Http::response(['integro' => true, 'hashAncorado' => $hash])]);
        $this->consultar($contrato)->assertOk()->assertJsonPath('dados.resultado', 'correspondente')->assertJsonPath('dados.hashArquivo', $hash)->assertJsonPath('dados.hashRegistrado', $hash);
        Http::assertSent(fn ($request) => $request->url() === "http://registry.test/contratos/{$contrato->id}/integridade" && $request['documentoHash'] === $hash && $request->hasHeader('x-api-key', 'chave-teste'));
        $this->assertNull($contrato->fresh()->anexo_contrato);
    }

    public function test_arquivo_diferente_nao_e_confirmado_mesmo_com_hash_local_igual(): void
    {
        $contrato = $this->contrato();
        Http::fake(['registry.test/*' => Http::response(['integro' => false, 'hashAncorado' => '0x'.str_repeat('b', 64)])]);
        $this->consultar($contrato)->assertOk()->assertJsonPath('dados.resultado', 'diferente');
    }

    public function test_legado_e_nao_registrado_nao_sao_confundidos_com_arquivo_valido(): void
    {
        Http::fake();
        $contrato = $this->contrato();
        $contrato->update(['documento_hash_blockchain' => null]);
        $this->consultar($contrato)->assertOk()->assertJsonPath('dados.resultado', 'registro_antigo');
        $contrato->update(['hash_blockchain' => null]);
        $this->consultar($contrato)->assertOk()->assertJsonPath('dados.resultado', 'nao_registrado');
        Http::assertNothingSent();
    }

    public function test_falha_e_resposta_inconsistente_da_rede_nao_validam_documento(): void
    {
        $contrato = $this->contrato();
        Http::fake(['registry.test/*' => Http::response(['mensagem' => 'Indisponível'], 503)]);
        $this->consultar($contrato)->assertStatus(503);
        Http::fake(['registry.test/*' => Http::response(['integro' => true, 'hashAncorado' => '0x'.str_repeat('b', 64)])]);
        $this->consultar($contrato)->assertStatus(503);
    }

    public function test_ausencia_de_registro_na_rede_nao_confirma_arquivo(): void
    {
        Http::fake(['registry.test/*' => Http::response([], 404)]);
        $this->consultar($this->contrato())->assertOk()->assertJsonPath('dados.resultado', 'nao_registrado');
        Http::fake(['registry.test/*' => Http::response([
            'erro' => ['detalhe' => ['motivo' => 'E_UNKNOWN_CONTRACT']],
        ], 409)]);
        $this->consultar($this->contrato())->assertOk()->assertJsonPath('dados.resultado', 'nao_registrado');
    }

    public function test_consulta_transacao_informa_a_ancora_ou_a_ausencia_na_rede(): void
    {
        $contrato = $this->contrato();
        $hash = $contrato->hash_blockchain;
        Http::fake([
            'registry.test/*' => Http::sequence()
                ->push([
                    'hashTransacao' => $hash,
                    'hashDocumento' => $contrato->documento_hash_blockchain,
                    'chave' => '0x'.str_repeat('c', 64),
                    'numeroBloco' => 12,
                    'confirmacoes' => 3,
                ])
                ->push([], 404),
        ]);
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);
        $this->actingAs($operador, 'api')->postJson('/api/v1/contratos/verificar-transacao', ['hashTransacao' => $hash])
            ->assertOk()
            ->assertJsonPath('dados.resultado', 'confirmada')
            ->assertJsonPath('dados.hashDocumento', $contrato->documento_hash_blockchain);

        $this->actingAs($operador, 'api')->postJson('/api/v1/contratos/verificar-transacao', ['hashTransacao' => $hash])
            ->assertOk()
            ->assertJsonPath('dados.resultado', 'nao_encontrada');
    }

    public function test_consulta_transacao_rejeita_hash_fora_do_formato_evm(): void
    {
        $operador = User::factory()->create(['papel' => Papel::Operador->value]);

        $this->actingAs($operador, 'api')
            ->postJson('/api/v1/contratos/verificar-transacao', ['hashTransacao' => 'hash-invalido'])
            ->assertUnprocessable()
            ->assertJsonPath('campos.hashTransacao.0', 'O hash da transação deve conter 0x seguido de 64 caracteres hexadecimais.');
    }

    public function test_upload_exige_autenticacao_arquivo_permitido_e_limite_de_tamanho(): void
    {
        $contrato = $this->contrato();
        $url = "/api/v1/contratos/{$contrato->id}/verificar-documento";
        $this->postJson($url)->assertUnauthorized();
        $this->actingAs(User::factory()->create(['papel' => Papel::Operador->value]), 'api');
        $this->postJson($url)->assertStatus(422)->assertJsonStructure(['campos' => ['arquivo']]);
        $this->post($url, ['arquivo' => UploadedFile::fake()->createWithContent('script.html', '<html>teste</html>')], ['Accept' => 'application/json'])->assertStatus(422);
        $this->post($url, ['arquivo' => UploadedFile::fake()->create('grande.pdf', 25601, 'application/pdf')], ['Accept' => 'application/json'])->assertStatus(422);
    }

    public function test_registrador_envia_sha256_do_arquivo_assinado_para_a_rede(): void
    {
        $hash = '0x'.hash('sha256', self::PDF);
        Http::fake(['registry.test/*' => Http::response(['hashTransacao' => '0x'.str_repeat('a', 64)], 201)]);
        $client = new MobyLedgerClient('http://registry.test', 'chave-teste', $this->app->make(ExternalHttpClient::class));
        $client->registrar('contrato|1|2026/0001|Imóvel|Inquilino|2026-01-01|2027-01-01|1500.00|'.$hash);
        Http::assertSent(fn ($request) => $request['documentoHash'] === $hash);
    }
}
