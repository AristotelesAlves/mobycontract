<?php

declare(strict_types=1);

namespace Tests\Feature\Contratos;

use App\Modules\Contratos\Contracts\ContratoRepositoryInterface;
use App\Modules\Contratos\Domain\Enums\IndiceReajuste;
use App\Modules\Contratos\Domain\Enums\PrazoVigencia;
use App\Modules\Contratos\Domain\Enums\StatusContrato;
use App\Modules\Contratos\Models\Contrato;
use App\Modules\Contratos\Models\ContratoParcela;
use App\Modules\Imoveis\Models\Imovel;
use App\Modules\Inquilinos\Models\Inquilino;
use App\Modules\Modelos\Domain\Enums\TipoModelo;
use App\Modules\Modelos\Models\Modelo;
use App\Modules\Usuarios\Models\User;
use App\Shared\Application\Exceptions\ServicoIndisponivelException;
use App\Shared\Contracts\ArmazenadorDeArquivo;
use App\Shared\Contracts\RegistradorBlockchain;
use App\Shared\Domain\Enums\Garantia;
use App\Shared\Domain\Enums\Papel;
use App\Shared\Infrastructure\Armazenamento\ArmazenadorLocal;
use App\Shared\Infrastructure\Blockchain\RegistradorBlockchainDeterministico;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

final class ContratoControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $operador;

    protected function setUp(): void
    {
        parent::setUp();

        $this->operador = User::factory()->create(['papel' => Papel::Operador->value]);
        $this->app->bind(RegistradorBlockchain::class, RegistradorBlockchainDeterministico::class);
    }

    private function alterarStatus(int $contratoId, StatusContrato $status, array $extras = []): void
    {
        $this->actingAs($this->operador, 'api')
            ->putJson("/api/v1/contratos/{$contratoId}/status", ['status' => $status->value, ...$extras])
            ->assertOk()
            ->assertJsonPath('dados.status', $status->value);
    }

    /**
     * @return array<string, mixed>
     */
    private function dadosValidos(?int $imovelId = null, ?int $modeloId = null, ?int $inquilinoId = null): array
    {
        $imovel = $imovelId ?? Imovel::factory()->create()->id;
        $modelo = $modeloId ?? Modelo::factory()->comTipo(TipoModelo::Contrato)->create(['garantia' => Garantia::SemGarantia->value])->id;
        $inquilino = $inquilinoId ?? Inquilino::factory()->create()->id;
        $dataInicio = now();

        return [
            'imovelId' => $imovel,
            'modeloId' => $modelo,
            'prazoVigencia' => PrazoVigencia::Determinado->value,
            'dataInicio' => $dataInicio->format('Y-m-d'),
            'dataFim' => $dataInicio->copy()->addMonthsNoOverflow(2)->format('Y-m-d'),
            'indiceReajuste' => IndiceReajuste::IPCA->value,
            'modoInquilino' => 'existente',
            'inquilinoId' => $inquilino,
            'nomeLocador' => 'Maria Locadora',
            'descontoMensal' => 0,
            'multaPorAtraso' => 2,
            'diaVencimentoParcela' => 10,
        ];
    }

    public function test_cria_contrato_com_numero_assinaturas_e_parcelas(): void
    {
        $dados = $this->dadosValidos();

        $resposta = $this->actingAs($this->operador, 'api')
            ->postJson('/api/v1/contratos', $dados);

        $resposta->assertCreated()
            ->assertJsonPath('dados.status', StatusContrato::Rascunho->value)
            ->assertJsonPath('dados.situacaoEfetiva', StatusContrato::Rascunho->value)
            ->assertJsonPath('dados.numero', date('Y').'/6001')
            ->assertJsonPath('dados.podeAlterar', true);

        $contratoId = (int) $resposta->json('dados.id');

        $this->assertDatabaseCount('contrato_parcelas', 3);
        $this->assertDatabaseHas('contrato_assinaturas', ['contrato_id' => $contratoId, 'papel' => 'Locador', 'nome' => 'Maria Locadora']);
        $this->assertDatabaseHas('contrato_historicos', ['contrato_id' => $contratoId, 'evento' => 'Contrato criado']);
    }

    public function test_rejeita_modelo_inativo(): void
    {
        $modelo = Modelo::factory()->inativo()->create();
        $dados = $this->dadosValidos(modeloId: $modelo->id);

        $this->actingAs($this->operador, 'api')
            ->postJson('/api/v1/contratos', $dados)
            ->assertStatus(422)
            ->assertJsonPath('codigo', 'BUSINESS_RULE_VIOLATION');
    }

    public function test_rejeita_modelo_de_aditivo(): void
    {
        $modelo = Modelo::factory()->comTipo(TipoModelo::Aditivo)->create();
        $dados = $this->dadosValidos(modeloId: $modelo->id);

        $this->actingAs($this->operador, 'api')
            ->postJson('/api/v1/contratos', $dados)
            ->assertStatus(422)
            ->assertJsonPath('codigo', 'BUSINESS_RULE_VIOLATION');
    }

    public function test_rejeita_imovel_com_contrato_ativo(): void
    {
        $imovel = Imovel::factory()->create();
        Contrato::factory()->comStatus(StatusContrato::Vigente)->create(['imovel_id' => $imovel->id]);

        $dados = $this->dadosValidos(imovelId: $imovel->id);

        $this->actingAs($this->operador, 'api')
            ->postJson('/api/v1/contratos', $dados)
            ->assertStatus(422)
            ->assertJsonPath('codigo', 'BUSINESS_RULE_VIOLATION');
    }

    public function test_transicao_invalida_de_status(): void
    {
        $contrato = Contrato::factory()->create(['criado_por' => $this->operador->id]);

        $this->actingAs($this->operador, 'api')
            ->putJson('/api/v1/contratos/'.$contrato->id.'/status', ['status' => StatusContrato::Vigente->value])
            ->assertStatus(422)
            ->assertJsonPath('codigo', 'BUSINESS_RULE_VIOLATION');
    }

    public function test_fluxo_de_status_ate_vigente_marca_imovel_alugado(): void
    {
        $dados = $this->dadosValidos();

        $resposta = $this->actingAs($this->operador, 'api')
            ->postJson('/api/v1/contratos', $dados)
            ->assertCreated();

        $contratoId = (int) $resposta->json('dados.id');

        $this->alterarStatus($contratoId, StatusContrato::Minuta);

        foreach ($resposta->json('dados.assinaturas') as $assinatura) {
            $this->actingAs($this->operador, 'api')
                ->postJson("/api/v1/contratos/{$contratoId}/assinaturas/{$assinatura['id']}/assinar")
                ->assertOk();
        }

        $this->alterarStatus($contratoId, StatusContrato::Pendente);

        $conteudoAssinado = "%PDF-1.7\nContrato assinado\n%%EOF";
        $this->actingAs($this->operador, 'api')
            ->post("/api/v1/contratos/{$contratoId}/documento-fisico", [
                'anexo' => UploadedFile::fake()->createWithContent('assinado.pdf', $conteudoAssinado),
            ], ['Accept' => 'application/json'])
            ->assertOk();

        $administrador = User::factory()->create(['papel' => Papel::Administrador->value]);

        $this->actingAs($administrador, 'api')
            ->postJson("/api/v1/contratos/{$contratoId}/blockchain")
            ->assertOk()
            ->assertJsonPath('dados.status', StatusContrato::Validado->value)
            ->assertJsonPath('dados.statusBlockchain', 'Registrado');

        $registrado = Contrato::query()->findOrFail($contratoId);
        $this->assertSame('0x'.hash('sha256', $conteudoAssinado), $registrado->documento_hash_blockchain);
        $this->app->make(ArmazenadorDeArquivo::class)->excluir($registrado->anexo_contrato);

        $this->alterarStatus($contratoId, StatusContrato::Vigente);

        $this->assertDatabaseHas('imoveis', [
            'id' => $dados['imovelId'],
            'status' => 'Alugado',
        ]);
    }

    public function test_nao_valida_contrato_por_alteracao_de_status(): void
    {
        $contrato = Contrato::factory()->comStatus(StatusContrato::Pendente)->create(['criado_por' => $this->operador->id]);

        $this->actingAs($this->operador, 'api')
            ->putJson("/api/v1/contratos/{$contrato->id}/status", ['status' => StatusContrato::Validado->value])
            ->assertStatus(422)
            ->assertJsonPath('codigo', 'BUSINESS_RULE_VIOLATION');
    }

    public function test_registro_em_blockchain_exige_assinaturas_e_documento(): void
    {
        $contrato = Contrato::factory()->comStatus(StatusContrato::Pendente)->create(['criado_por' => $this->operador->id]);

        $administrador = User::factory()->create(['papel' => Papel::Administrador->value]);

        $this->actingAs($administrador, 'api')
            ->postJson("/api/v1/contratos/{$contrato->id}/blockchain")
            ->assertStatus(422)
            ->assertJsonPath('codigo', 'BUSINESS_RULE_VIOLATION');
    }

    public function test_operador_nao_registra_em_blockchain(): void
    {
        $contrato = Contrato::factory()->comStatus(StatusContrato::Validado)->create(['criado_por' => $this->operador->id]);

        $this->actingAs($this->operador, 'api')
            ->postJson("/api/v1/contratos/{$contrato->id}/blockchain")
            ->assertForbidden()
            ->assertJsonPath('codigo', 'FORBIDDEN');
    }

    public function test_cancelamento_exige_administrador(): void
    {
        $contrato = Contrato::factory()->create([
            'criado_por' => $this->operador->id,
            'status' => StatusContrato::Pendente->value,
            'hash_blockchain' => '0xabc',
            'status_blockchain' => 'Registrado',
        ]);

        $this->actingAs($this->operador, 'api')
            ->putJson("/api/v1/contratos/{$contrato->id}/status", [
                'status' => StatusContrato::Cancelado->value,
                'motivo' => 'Cancelamento por solicitação das partes.',
            ])
            ->assertForbidden()
            ->assertJsonPath('codigo', 'FORBIDDEN');

        $administrador = User::factory()->create(['papel' => Papel::Administrador->value]);

        $this->actingAs($administrador, 'api')
            ->putJson("/api/v1/contratos/{$contrato->id}/status", [
                'status' => StatusContrato::Cancelado->value,
                'motivo' => 'Cancelamento por solicitação das partes.',
            ])
            ->assertOk()
            ->assertJsonPath('dados.status', StatusContrato::Cancelado->value);
    }

    public function test_alteracao_bloqueada_fora_de_rascunho(): void
    {
        $contrato = Contrato::factory()->comStatus(StatusContrato::Pendente)->create(['criado_por' => $this->operador->id]);

        $this->actingAs($this->operador, 'api')
            ->putJson('/api/v1/contratos/'.$contrato->id, ['descontoMensal' => 100])
            ->assertStatus(422)
            ->assertJsonPath('codigo', 'BUSINESS_RULE_VIOLATION');
    }

    public function test_exclusao_bloqueada_fora_de_rascunho(): void
    {
        $contrato = Contrato::factory()->comStatus(StatusContrato::Pendente)->create(['criado_por' => $this->operador->id]);

        $administrador = User::factory()->create(['papel' => Papel::Administrador->value]);

        $this->actingAs($administrador, 'api')
            ->deleteJson('/api/v1/contratos/'.$contrato->id)
            ->assertStatus(422)
            ->assertJsonPath('codigo', 'BUSINESS_RULE_VIOLATION');
    }

    public function test_repositorio_nao_exclui_contrato_que_saiu_de_rascunho(): void
    {
        $contrato = Contrato::factory()->comStatus(StatusContrato::Pendente)->create(['criado_por' => $this->operador->id]);

        $this->assertFalse($this->app->make(ContratoRepositoryInterface::class)->excluir($contrato->id));
        $this->assertDatabaseHas('contratos', ['id' => $contrato->id]);
    }

    public function test_exclusao_de_contrato_apaga_anexo_sem_passar_caminho_nulo(): void
    {
        $contrato = Contrato::factory()->create([
            'criado_por' => $this->operador->id,
            'anexo_contrato' => 'contratos/assinado.pdf',
            'comprovante_garantia' => null,
        ]);
        $armazenador = \Mockery::mock(ArmazenadorDeArquivo::class);
        $armazenador->shouldReceive('excluir')->once()->with('contratos/assinado.pdf');
        $this->app->instance(ArmazenadorDeArquivo::class, $armazenador);
        $administrador = User::factory()->create(['papel' => Papel::Administrador->value]);

        $this->actingAs($administrador, 'api')
            ->deleteJson('/api/v1/contratos/'.$contrato->id)
            ->assertNoContent();

        $this->assertDatabaseMissing('contratos', ['id' => $contrato->id]);
    }

    public function test_exclusao_de_contrato_apaga_comprovante_de_garantia(): void
    {
        $contrato = Contrato::factory()->create([
            'criado_por' => $this->operador->id,
            'comprovante_garantia' => 'garantias/comprovante.pdf',
        ]);
        $armazenador = \Mockery::mock(ArmazenadorDeArquivo::class);
        $armazenador->shouldReceive('excluir')->once()->with('garantias/comprovante.pdf');
        $this->app->instance(ArmazenadorDeArquivo::class, $armazenador);
        $administrador = User::factory()->create(['papel' => Papel::Administrador->value]);

        $this->actingAs($administrador, 'api')
            ->deleteJson('/api/v1/contratos/'.$contrato->id)
            ->assertNoContent();

        $this->assertDatabaseMissing('contratos', ['id' => $contrato->id]);
    }

    public function test_substituir_anexo_de_contrato_apaga_o_arquivo_anterior(): void
    {
        $contrato = Contrato::factory()->create([
            'criado_por' => $this->operador->id,
            'anexo_contrato' => 'contratos/anterior.pdf',
        ]);
        $armazenador = \Mockery::mock(ArmazenadorDeArquivo::class);
        $armazenador->shouldReceive('guardar')->once()->andReturn('contratos/novo.pdf');
        $armazenador->shouldReceive('excluir')->once()->with('contratos/anterior.pdf');
        $this->app->instance(ArmazenadorDeArquivo::class, $armazenador);

        $this->actingAs($this->operador, 'api')
            ->post("/api/v1/contratos/{$contrato->id}/documento-fisico", [
                'anexo' => UploadedFile::fake()->createWithContent('novo.pdf', "%PDF-1.7\nNovo contrato\n%%EOF"),
            ], ['Accept' => 'application/json'])
            ->assertOk();

        $this->assertDatabaseHas('contratos', [
            'id' => $contrato->id,
            'anexo_contrato' => 'contratos/novo.pdf',
        ]);
    }

    public function test_operador_nao_pode_excluir_contrato(): void
    {
        $contrato = Contrato::factory()->create(['criado_por' => $this->operador->id]);

        $this->actingAs($this->operador, 'api')
            ->deleteJson('/api/v1/contratos/'.$contrato->id)
            ->assertForbidden()
            ->assertJsonPath('codigo', 'FORBIDDEN');
    }

    public function test_listagem_retorna_totais_e_filtra_por_texto(): void
    {
        $inquilinoAlvo = Inquilino::factory()->create(['nome' => 'Alvo Especial']);
        $inquilinoOutro = Inquilino::factory()->create(['nome' => 'Outro Nome']);
        Contrato::factory()->create(['criado_por' => $this->operador->id, 'numero' => '2026/7001', 'inquilino_id' => $inquilinoAlvo->id]);
        Contrato::factory()->create(['criado_por' => $this->operador->id, 'numero' => '2026/7002', 'inquilino_id' => $inquilinoOutro->id]);

        $this->actingAs($this->operador, 'api')
            ->getJson('/api/v1/contratos?texto=Alvo')
            ->assertOk()
            ->assertJsonPath('dados.total', 1)
            ->assertJsonStructure(['dados' => ['totais' => ['Rascunho']]]);
    }

    public function test_registra_pagamento_e_impede_duplicidade(): void
    {
        $criado = $this->actingAs($this->operador, 'api')
            ->postJson('/api/v1/contratos', $this->dadosValidos())
            ->assertCreated();

        $contratoId = (int) $criado->json('dados.id');
        $parcela = ContratoParcela::query()->where('contrato_id', $contratoId)->orderBy('id')->firstOrFail();

        $this->actingAs($this->operador, 'api')
            ->postJson("/api/v1/contratos/{$contratoId}/parcelas/{$parcela->id}/pagamento", ['dataPagamento' => now()->toDateString()])
            ->assertOk()
            ->assertJsonPath('dados.status', 'Pago');

        $this->actingAs($this->operador, 'api')
            ->postJson("/api/v1/contratos/{$contratoId}/parcelas/{$parcela->id}/pagamento", ['dataPagamento' => now()->addDay()->toDateString()])
            ->assertStatus(422)
            ->assertJsonPath('codigo', 'BUSINESS_RULE_VIOLATION');
    }

    public function test_pagamento_sem_valor_registra_total_com_encargos(): void
    {
        $id = $this->actingAs($this->operador, 'api')->postJson('/api/v1/contratos', $this->dadosValidos())->assertCreated()->json('dados.id');
        $parcela = ContratoParcela::query()->where('contrato_id', $id)->firstOrFail();
        $parcela->update(['valor' => 1000, 'juros' => 25, 'taxas_adicionais' => 15]);
        $this->postJson("/api/v1/contratos/{$id}/parcelas/{$parcela->id}/pagamento", ['dataPagamento' => now()->toDateString()])
            ->assertOk()->assertJsonPath('dados.valorPago', 1040)->assertJsonPath('dados.valorExibido', 1040);
        $this->assertDatabaseHas('contrato_alteracoes', ['contrato_id' => $id, 'acao' => 'Pagamento']);
    }

    public function test_pagamento_respeita_valor_recebido_e_permite_correcao_auditada(): void
    {
        $id = $this->actingAs($this->operador, 'api')->postJson('/api/v1/contratos', $this->dadosValidos())->assertCreated()->json('dados.id');
        $parcela = ContratoParcela::query()->where('contrato_id', $id)->firstOrFail();
        $url = "/api/v1/contratos/{$id}/parcelas/{$parcela->id}/pagamento";
        $this->postJson($url, ['dataPagamento' => now()->toDateString(), 'valorPago' => 900])->assertOk()->assertJsonPath('dados.valorPago', 900);
        $antes = $parcela->fresh()->pago_em->toDateTimeString();
        $this->postJson($url, ['dataPagamento' => now()->toDateString(), 'valorPago' => 0])->assertUnprocessable();
        $this->postJson($url, ['dataPagamento' => now()->toDateString(), 'valorPago' => 950, 'corrigir' => true])->assertOk()->assertJsonPath('dados.valorPago', 950);
        $this->assertSame($antes, $parcela->fresh()->pago_em->toDateTimeString());
        $this->getJson("/api/v1/contratos/{$id}")->assertOk()->assertJsonFragment(['evento' => 'Pagamento corrigido']);
        $this->assertDatabaseHas('contrato_alteracoes', ['contrato_id' => $id, 'de' => 'Pago: R$ 900,00 em '.now()->toDateString(), 'para' => 'Pago: R$ 950,00 em '.now()->toDateString()]);
        $this->postJson($url, ['dataPagamento' => now()->toDateString(), 'corrigir' => true])->assertUnprocessable();
    }

    public function test_correcao_nao_registra_pagamento_de_parcela_pendente(): void
    {
        $id = $this->actingAs($this->operador, 'api')->postJson('/api/v1/contratos', $this->dadosValidos())->assertCreated()->json('dados.id');
        $parcela = ContratoParcela::query()->where('contrato_id', $id)->firstOrFail();
        $this->postJson("/api/v1/contratos/{$id}/parcelas/{$parcela->id}/pagamento", ['dataPagamento' => now()->toDateString(), 'valorPago' => 1000, 'corrigir' => true])->assertUnprocessable();
        $this->assertSame('Pendente', $parcela->fresh()->status);
    }

    public function test_lanca_encargo_e_recalcula_parcela(): void
    {
        $criado = $this->actingAs($this->operador, 'api')
            ->postJson('/api/v1/contratos', $this->dadosValidos())
            ->assertCreated();

        $contratoId = (int) $criado->json('dados.id');
        $parcela = ContratoParcela::query()->where('contrato_id', $contratoId)->orderBy('id')->firstOrFail();

        $this->actingAs($this->operador, 'api')
            ->postJson("/api/v1/contratos/{$contratoId}/parcelas/{$parcela->id}/encargos", [
                'tipo' => 'taxa',
                'valor' => 50,
                'motivo' => 'Taxa de emissão',
            ])
            ->assertOk()
            ->assertJsonPath('dados.taxasAdicionais', 50);
    }

    public function test_lista_etapas_de_assinatura(): void
    {
        $dados = $this->dadosValidos();
        $criado = $this->actingAs($this->operador, 'api')
            ->postJson('/api/v1/contratos', $dados)
            ->assertCreated();

        $contratoId = (int) $criado->json('dados.id');

        $this->actingAs($this->operador, 'api')
            ->getJson("/api/v1/contratos/{$contratoId}/etapas")
            ->assertOk()
            ->assertJsonCount(3, 'dados.itens')
            ->assertJsonPath('dados.itens.0.ativa', true)
            ->assertJsonPath('dados.itens.1.ativa', false);
    }

    public function test_snapshot_preserva_cadastros_e_modelo_apos_edicao(): void
    {
        $dados = $this->dadosValidos();
        $criado = $this->actingAs($this->operador, 'api')->postJson('/api/v1/contratos', $dados)->assertCreated();
        $id = $criado->json('dados.id');
        $snapshot = $criado->json('dados.snapshot');
        $this->assertSame(1, $snapshot['versao']);
        Imovel::query()->findOrFail($dados['imovelId'])->update(['titulo' => 'Cadastro novo', 'valor_aluguel' => 9999]);
        Inquilino::query()->findOrFail($dados['inquilinoId'])->update(['nome' => 'Nome novo']);
        Modelo::query()->findOrFail($dados['modeloId'])->update(['titulo' => 'Modelo novo', 'conteudo_html' => '<p>Alterado</p>']);
        $this->getJson("/api/v1/contratos/{$id}")->assertOk()
            ->assertJsonPath('dados.imovelTitulo', $snapshot['imovel']['titulo'])
            ->assertJsonPath('dados.inquilinoNome', $snapshot['inquilino']['nome'])
            ->assertJsonPath('dados.modeloTitulo', $snapshot['modelo']['titulo'])
            ->assertJsonPath('dados.snapshot', $snapshot);
        $this->getJson('/api/v1/contratos?texto='.urlencode($snapshot['inquilino']['nome']))
            ->assertOk()->assertJsonPath('dados.total', 1)
            ->assertJsonPath('dados.itens.0.inquilinoNome', $snapshot['inquilino']['nome'])
            ->assertJsonPath('dados.itens.0.snapshot', null);
        $contrato = Contrato::query()->findOrFail($id);
        $contrato->snapshot = ['versao' => 2];
        $this->expectException(\LogicException::class);
        $contrato->save();
    }

    public function test_sem_garantia_ignora_valor_de_caucao(): void
    {
        $dados = $this->dadosValidos();
        $this->actingAs($this->operador, 'api')->postJson('/api/v1/contratos', [...$dados, 'valorCaucao' => 3000])
            ->assertCreated()->assertJsonPath('dados.valorCaucao', null);
        $this->assertDatabaseCount('dividas', 0);
    }

    public function test_consulta_publica_nao_expoe_dados_e_nao_usa_cadastro_atual(): void
    {
        $inquilino = Inquilino::factory()->create(['cpf' => '52998224725']);
        $dados = $this->dadosValidos(inquilinoId: $inquilino->id);
        $criado = $this->actingAs($this->operador, 'api')->postJson('/api/v1/contratos', $dados)->assertCreated();
        $id = $criado->json('dados.id');
        $numero = $criado->json('dados.numero');
        Contrato::query()->whereKey($id)->update(['status' => 'Vigente']);
        $inquilino->update(['cpf' => '11144477735']);
        $this->postJson('/api/v1/contratos/validar', ['cpf' => '529.982.247-25', 'numero' => $numero])
            ->assertOk()->assertJsonPath('dados.reconhecido', true)
            ->assertJsonMissing(['cpf' => '52998224725'])->assertJsonMissing(['nome' => $inquilino->nome]);
        $erro = $this->postJson('/api/v1/contratos/validar', ['cpf' => '11144477735', 'numero' => $numero])->assertOk()->json();
        $inexistente = $this->postJson('/api/v1/contratos/validar', ['cpf' => '11144477735', 'numero' => '9999/9999'])->assertOk()->json();
        $this->assertSame($erro, $inexistente);
        $this->postJson('/api/v1/contratos/validar', ['cpf' => '11111111111', 'numero' => $numero])->assertUnprocessable();
    }

    public function test_consulta_publica_tem_limite_por_ip(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/contratos/validar', ['cpf' => '52998224725', 'numero' => '9999/9999'])->assertOk();
        }
        $this->postJson('/api/v1/contratos/validar', ['cpf' => '52998224725', 'numero' => '9999/9999'])->assertStatus(429);
    }

    public function test_desconto_editado_atualiza_primeira_parcela_sem_alterar_snapshot(): void
    {
        $criado = $this->actingAs($this->operador, 'api')->postJson('/api/v1/contratos', $this->dadosValidos())->assertCreated();
        $id = $criado->json('dados.id');
        $snapshot = $criado->json('dados.snapshot');
        $primeira = ContratoParcela::query()->where('contrato_id', $id)->orderBy('vencimento')->firstOrFail();
        $valorOriginal = (float) $primeira->valor;
        $this->putJson("/api/v1/contratos/{$id}", ['descontoMensal' => 100])->assertOk()->assertJsonPath('dados.snapshot', $snapshot);
        $this->assertSame($valorOriginal - 100, (float) $primeira->fresh()->valor);
        $this->putJson("/api/v1/contratos/{$id}", ['descontoMensal' => $valorOriginal])->assertUnprocessable();
    }

    public function test_modelo_com_caucao_exige_valor_e_comprovante_real(): void
    {
        $diretorio = sys_get_temp_dir().'/tcc-garantia-'.bin2hex(random_bytes(8));
        $this->app->instance(ArmazenadorDeArquivo::class, new ArmazenadorLocal($diretorio));
        $modelo = Modelo::factory()->comTipo(TipoModelo::Contrato)->create(['garantia' => Garantia::Caucao->value]);
        $dados = $this->dadosValidos(modeloId: $modelo->id);
        $this->actingAs($this->operador, 'api')->postJson('/api/v1/contratos', $dados)->assertUnprocessable();
        $this->postJson('/api/v1/contratos', [...$dados, 'valorCaucao' => 1500])->assertUnprocessable();
        $comprovante = UploadedFile::fake()->createWithContent('deposito.pdf', $this->pdfDeTeste());
        $resposta = $this->post('/api/v1/contratos', [...$dados, 'valorCaucao' => 1500, 'comprovanteGarantia' => $comprovante], ['Accept' => 'application/json'])->assertCreated();
        $id = $resposta->json('dados.id');
        $this->assertDatabaseHas('dividas', ['contrato_id' => $id, 'valor' => 1500]);
        $this->get("/api/v1/contratos/{$id}/comprovante-garantia")->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->putJson("/api/v1/contratos/{$id}", ['valorCaucao' => 1700])->assertOk();
        $this->assertDatabaseHas('dividas', ['contrato_id' => $id, 'valor' => 1700]);
        $contrato = Contrato::query()->findOrFail($id);
        $this->app->make(ArmazenadorDeArquivo::class)->excluir($contrato->comprovante_garantia);
        rmdir($diretorio.'/garantias');
        rmdir($diretorio);
    }

    public function test_seguro_e_titulo_de_capitalizacao_nao_exigem_caucao(): void
    {
        foreach ([Garantia::SeguroFianca, Garantia::TituloDeCapitalizacao] as $garantia) {
            $modelo = Modelo::factory()->comTipo(TipoModelo::Contrato)->create(['garantia' => $garantia->value]);
            $this->actingAs($this->operador, 'api')->postJson('/api/v1/contratos', $this->dadosValidos(modeloId: $modelo->id))
                ->assertCreated()->assertJsonPath('dados.garantia', $garantia->value)->assertJsonPath('dados.valorCaucao', null);
        }
    }

    public function test_registrador_de_teste_nao_se_passa_por_blockchain_em_producao(): void
    {
        $this->app->detectEnvironment(static fn (): string => 'production');
        try {
            $this->expectException(ServicoIndisponivelException::class);
            (new RegistradorBlockchainDeterministico)->registrar('documento de teste');
        } finally {
            $this->app->detectEnvironment(static fn (): string => 'testing');
        }
    }

    public function test_fiador_exige_comprovante_mas_dispensa_caucao_e_pode_consultar(): void
    {
        $diretorio = sys_get_temp_dir().'/tcc-fiador-'.bin2hex(random_bytes(8));
        $this->app->instance(ArmazenadorDeArquivo::class, new ArmazenadorLocal($diretorio));
        $modelo = Modelo::factory()->comTipo(TipoModelo::Contrato)->create(['garantia' => Garantia::Fiador->value]);
        $locatario = Inquilino::factory()->create(['cpf' => '11144477735']);
        $dados = [...$this->dadosValidos(modeloId: $modelo->id, inquilinoId: $locatario->id), 'fiadores' => [
            ['nome' => 'Fiador de teste', 'cpf' => '52998224725', 'telefone' => '88999990000'],
        ]];
        $this->actingAs($this->operador, 'api')->postJson('/api/v1/contratos', $dados)->assertUnprocessable();
        $resposta = $this->post('/api/v1/contratos', [...$dados, 'comprovanteGarantia' => UploadedFile::fake()->createWithContent('renda.pdf', $this->pdfDeTeste())], ['Accept' => 'application/json'])
            ->assertCreated()->assertJsonPath('dados.valorCaucao', null);
        $contrato = Contrato::query()->findOrFail($resposta->json('dados.id'));
        $contrato->update(['status' => 'Vigente']);
        $this->postJson('/api/v1/contratos/validar', ['cpf' => '52998224725', 'numero' => $contrato->numero])->assertOk()->assertJsonPath('dados.reconhecido', true);
        $this->assertDatabaseCount('dividas', 0);
        $this->app->make(ArmazenadorDeArquivo::class)->excluir($contrato->comprovante_garantia);
        rmdir($diretorio.'/garantias');
        rmdir($diretorio);
    }

    public function test_exportacao_pdf_exige_sessao_e_emite_autorizacao_temporaria_por_contrato(): void
    {
        $contrato = Contrato::factory()->create();
        $rota = "/api/v1/contratos/{$contrato->id}/exportacao-pdf";
        $this->postJson($rota)->assertUnauthorized();
        $url = $this->actingAs($this->operador, 'api')->postJson($rota)
            ->assertOk()->json('dados.autorizacao');

        $this->app['auth']->forgetGuards();
        $this->getJson($url)->assertNoContent();
        $this->getJson(str_replace("/{$contrato->id}/", '/999999/', $url))->assertForbidden();
        $this->getJson($rota)->assertForbidden();
        $this->travel(2)->minutes();
        $this->getJson($url)->assertForbidden();
    }

    public function test_novo_inquilino_no_contrato_dispensa_endereco_e_preserva_profissao(): void
    {
        $dados = $this->dadosValidos();
        $dados['modoInquilino'] = 'novo';
        unset($dados['inquilinoId']);
        $dados['inquilino'] = ['nome' => 'Novo inquilino', 'cpf' => '52998224725', 'email' => 'novo-sem-endereco@example.test', 'telefone' => '(85) 99999-1234', 'dataNascimento' => '1990-01-01', 'estadoCivil' => 'Solteiro(a)', 'renda' => 0, 'profissao' => 'Analista de sistemas'];
        $this->actingAs($this->operador, 'api')->postJson('/api/v1/contratos', $dados)->assertCreated();
        $this->assertDatabaseHas('inquilinos', ['cpf' => '52998224725', 'endereco' => '', 'profissao' => 'Analista de sistemas']);
    }

    public function test_contrato_usa_proprietario_do_imovel_no_snapshot_e_assinatura(): void
    {
        $imovel = Imovel::factory()->create(['nome_proprietario' => 'Proprietário cadastrado']);
        $dados = $this->dadosValidos(imovelId: $imovel->id);
        $dados['nomeLocador'] = 'Nome diferente enviado pelo cliente';
        $resposta = $this->actingAs($this->operador, 'api')->postJson('/api/v1/contratos', $dados)->assertCreated()->assertJsonPath('dados.snapshot.locador.nome', 'Proprietário cadastrado');
        $this->assertDatabaseHas('contrato_assinaturas', ['contrato_id' => $resposta->json('dados.id'), 'papel' => 'Locador', 'nome' => 'Proprietário cadastrado']);
    }
}
