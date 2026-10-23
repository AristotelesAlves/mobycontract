<?php

declare(strict_types=1);

namespace Tests\Feature\Contratos;

use App\Modules\Contratos\Domain\Enums\MotivoAditivo;
use App\Modules\Contratos\Domain\Enums\PapelAssinatura;
use App\Modules\Contratos\Domain\Enums\StatusAditivo;
use App\Modules\Contratos\Domain\Enums\StatusContrato;
use App\Modules\Contratos\Models\Contrato;
use App\Modules\Contratos\Models\ContratoAditivo;
use App\Modules\Contratos\Models\ContratoAssinatura;
use App\Modules\Modelos\Domain\Enums\TipoModelo;
use App\Modules\Modelos\Models\Modelo;
use App\Modules\Usuarios\Models\User;
use App\Shared\Contracts\RegistradorBlockchain;
use App\Shared\Domain\Enums\Papel;
use App\Shared\Infrastructure\Blockchain\RegistradorBlockchainDeterministico;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AditivoControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $operador;

    protected function setUp(): void
    {
        parent::setUp();

        $this->operador = User::factory()->create(['papel' => Papel::Operador->value]);
        $this->app->bind(RegistradorBlockchain::class, RegistradorBlockchainDeterministico::class);
    }

    private function contratoComAssinaturas(StatusContrato $status = StatusContrato::Vigente): Contrato
    {
        $contrato = Contrato::factory()->comStatus($status)->create(['criado_por' => $this->operador->id]);

        ContratoAssinatura::query()->create([
            'contrato_id' => $contrato->id,
            'papel' => PapelAssinatura::Locador->value,
            'nome' => 'Maria Locadora',
        ]);

        ContratoAssinatura::query()->create([
            'contrato_id' => $contrato->id,
            'papel' => PapelAssinatura::Locatario->value,
            'nome' => 'João Locatário',
        ]);

        return $contrato;
    }

    /**
     * @return array<string, mixed>
     */
    private function dadosValidos(?int $modeloId = null): array
    {
        return [
            'titulo' => 'Aditivo de reajuste',
            'descricao' => 'Reajuste anual do aluguel.',
            'data' => '2026-02-01',
            'modeloId' => $modeloId ?? Modelo::factory()->comTipo(TipoModelo::Aditivo)->create()->id,
            'motivos' => [MotivoAditivo::Valor->value],
            'novoValor' => 2500,
        ];
    }

    public function test_cria_aditivo_com_numero_assinaturas_e_dados_extras(): void
    {
        $contrato = $this->contratoComAssinaturas();

        $resposta = $this->actingAs($this->operador, 'api')
            ->postJson("/api/v1/contratos/{$contrato->id}/aditivos", $this->dadosValidos());

        $resposta->assertCreated()
            ->assertJsonPath('dados.status', StatusAditivo::Rascunho->value)
            ->assertJsonPath('dados.numero', 'ADT 001/'.date('Y'))
            ->assertJsonPath('dados.podeAlterar', true)
            ->assertJsonPath('dados.dadosExtras.0.rotulo', 'Novo Valor Mensal')
            ->assertJsonPath('dados.dadosExtras.0.valor', '2.500,00');

        $this->assertCount(2, $resposta->json('dados.assinaturas'));

        $this->assertDatabaseHas('contrato_historicos', [
            'contrato_id' => $contrato->id,
            'evento' => 'Aditivo criado',
        ]);
    }

    public function test_motivo_valor_nao_altera_valor_mensal_do_contrato(): void
    {
        $contrato = $this->contratoComAssinaturas();
        $valorOriginal = $contrato->valor_mensal;

        $this->actingAs($this->operador, 'api')
            ->postJson("/api/v1/contratos/{$contrato->id}/aditivos", $this->dadosValidos())
            ->assertCreated();

        $this->assertSame($valorOriginal, $contrato->refresh()->valor_mensal);
    }

    public function test_numero_do_aditivo_e_incremental_por_contrato(): void
    {
        $contrato = $this->contratoComAssinaturas();

        $this->actingAs($this->operador, 'api')
            ->postJson("/api/v1/contratos/{$contrato->id}/aditivos", $this->dadosValidos())
            ->assertJsonPath('dados.numero', 'ADT 001/'.date('Y'));

        $this->actingAs($this->operador, 'api')
            ->postJson("/api/v1/contratos/{$contrato->id}/aditivos", $this->dadosValidos())
            ->assertJsonPath('dados.numero', 'ADT 002/'.date('Y'));
    }

    public function test_titulo_obrigatorio(): void
    {
        $contrato = $this->contratoComAssinaturas();
        $dados = $this->dadosValidos();
        $dados['titulo'] = '';

        $this->actingAs($this->operador, 'api')
            ->postJson("/api/v1/contratos/{$contrato->id}/aditivos", $dados)
            ->assertStatus(422)
            ->assertJsonPath('codigo', 'VALIDATION_ERROR');
    }

    public function test_modelo_precisa_ser_do_tipo_aditivo(): void
    {
        $contrato = $this->contratoComAssinaturas();
        $modelo = Modelo::factory()->comTipo(TipoModelo::Contrato)->create();

        $this->actingAs($this->operador, 'api')
            ->postJson("/api/v1/contratos/{$contrato->id}/aditivos", $this->dadosValidos($modelo->id))
            ->assertStatus(422)
            ->assertJsonPath('codigo', 'BUSINESS_RULE_VIOLATION');
    }

    public function test_contrato_encerrado_nao_pode_receber_aditivo(): void
    {
        $contrato = $this->contratoComAssinaturas(StatusContrato::Encerrado);

        $this->actingAs($this->operador, 'api')
            ->postJson("/api/v1/contratos/{$contrato->id}/aditivos", $this->dadosValidos())
            ->assertStatus(422)
            ->assertJsonPath('codigo', 'BUSINESS_RULE_VIOLATION');
    }

    public function test_fluxo_completo_ate_validado_por_blockchain(): void
    {
        $contrato = $this->contratoComAssinaturas();

        $criado = $this->actingAs($this->operador, 'api')
            ->postJson("/api/v1/contratos/{$contrato->id}/aditivos", $this->dadosValidos())
            ->assertCreated();

        $aditivoId = (int) $criado->json('dados.id');

        $this->actingAs($this->operador, 'api')
            ->putJson("/api/v1/contratos/{$contrato->id}/aditivos/{$aditivoId}/status", [
                'status' => StatusAditivo::PendenteDeAssinatura->value,
            ])
            ->assertOk();

        foreach ($criado->json('dados.assinaturas') as $assinatura) {
            $this->actingAs($this->operador, 'api')
                ->postJson("/api/v1/contratos/{$contrato->id}/aditivos/{$aditivoId}/assinaturas/{$assinatura['id']}/assinar")
                ->assertOk();
        }

        $this->actingAs($this->operador, 'api')
            ->putJson("/api/v1/contratos/{$contrato->id}/aditivos/{$aditivoId}/status", [
                'status' => StatusAditivo::Assinado->value,
            ])
            ->assertOk();

        $this->actingAs($this->operador, 'api')
            ->postJson("/api/v1/contratos/{$contrato->id}/aditivos/{$aditivoId}/documento-fisico")
            ->assertOk()
            ->assertJsonPath('dados.uploadConfirmado', true);

        $administrador = User::factory()->create(['papel' => Papel::Administrador->value]);

        $this->actingAs($administrador, 'api')
            ->postJson("/api/v1/contratos/{$contrato->id}/aditivos/{$aditivoId}/blockchain")
            ->assertOk()
            ->assertJsonPath('dados.status', StatusAditivo::Validado->value)
            ->assertJsonPath('dados.statusBlockchain', 'Registrado');

        $this->assertNotNull(ContratoAditivo::query()->findOrFail($aditivoId)->hash_blockchain);
    }

    public function test_nao_valida_aditivo_por_alteracao_de_status(): void
    {
        $contrato = $this->contratoComAssinaturas();
        $aditivo = ContratoAditivo::factory()->comStatus(StatusAditivo::Assinado)->create([
            'contrato_id' => $contrato->id,
            'criado_por' => $this->operador->id,
        ]);

        $this->actingAs($this->operador, 'api')
            ->putJson("/api/v1/contratos/{$contrato->id}/aditivos/{$aditivo->id}/status", [
                'status' => StatusAditivo::Validado->value,
            ])
            ->assertStatus(422)
            ->assertJsonPath('codigo', 'BUSINESS_RULE_VIOLATION');
    }

    public function test_operador_nao_registra_em_blockchain(): void
    {
        $contrato = $this->contratoComAssinaturas();
        $aditivo = ContratoAditivo::factory()->comStatus(StatusAditivo::Assinado)->create([
            'contrato_id' => $contrato->id,
            'criado_por' => $this->operador->id,
            'upload_confirmado' => true,
        ]);

        $this->actingAs($this->operador, 'api')
            ->postJson("/api/v1/contratos/{$contrato->id}/aditivos/{$aditivo->id}/blockchain")
            ->assertForbidden()
            ->assertJsonPath('codigo', 'FORBIDDEN');
    }

    public function test_nao_exclui_aditivo_fora_de_rascunho(): void
    {
        $contrato = $this->contratoComAssinaturas();
        $aditivo = ContratoAditivo::factory()->comStatus(StatusAditivo::Assinado)->create([
            'contrato_id' => $contrato->id,
            'criado_por' => $this->operador->id,
        ]);

        $administrador = User::factory()->create(['papel' => Papel::Administrador->value]);

        $this->actingAs($administrador, 'api')
            ->deleteJson("/api/v1/contratos/{$contrato->id}/aditivos/{$aditivo->id}")
            ->assertStatus(422)
            ->assertJsonPath('codigo', 'BUSINESS_RULE_VIOLATION');
    }

    public function test_altera_aditivo_em_rascunho_e_recalcula_dados_extras(): void
    {
        $contrato = $this->contratoComAssinaturas();
        $aditivo = ContratoAditivo::factory()->create([
            'contrato_id' => $contrato->id,
            'criado_por' => $this->operador->id,
        ]);

        $this->actingAs($this->operador, 'api')
            ->putJson("/api/v1/contratos/{$contrato->id}/aditivos/{$aditivo->id}", [
                'titulo' => 'Aditivo revisado',
                'motivos' => [MotivoAditivo::Valor->value],
                'novoValor' => 3000,
            ])
            ->assertOk()
            ->assertJsonPath('dados.titulo', 'Aditivo revisado')
            ->assertJsonPath('dados.dadosExtras.0.valor', '3.000,00');
    }

    public function test_lista_aditivos_do_contrato(): void
    {
        $contrato = $this->contratoComAssinaturas();

        ContratoAditivo::factory()->create([
            'contrato_id' => $contrato->id,
            'criado_por' => $this->operador->id,
            'numero' => 'ADT 001/'.date('Y'),
        ]);

        ContratoAditivo::factory()->create([
            'contrato_id' => $contrato->id,
            'criado_por' => $this->operador->id,
            'numero' => 'ADT 002/'.date('Y'),
        ]);

        $this->actingAs($this->operador, 'api')
            ->getJson("/api/v1/contratos/{$contrato->id}/aditivos")
            ->assertOk()
            ->assertJsonCount(2, 'dados.itens');
    }
}
