<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Contratos\Models\Contrato;
use App\Modules\Contratos\Models\ContratoEncargoFixo;
use App\Modules\Contratos\Models\ContratoParcela;
use App\Modules\Contratos\Models\ContratoUtilidade;
use App\Modules\Imoveis\Models\Imovel;
use App\Modules\Inquilinos\Models\Inquilino;
use App\Modules\Modelos\Models\Modelo;
use App\Modules\Usuarios\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

final class ContratoSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = User::all();
        $imoveis = Imovel::all();
        $inquilinos = Inquilino::all();
        $modelos = Modelo::all();

        if ($usuarios->isEmpty() || $imoveis->isEmpty() || $inquilinos->isEmpty() || $modelos->isEmpty()) {
            $this->command?->warn('Executa os seeders de usuarios, imoveis, inquilinos e modelos antes.');

            return;
        }

        $modeloContrato = $modelos->firstWhere('tipo', 'Contrato');
        if (! $modeloContrato instanceof Modelo) {
            $this->command?->warn('Cadastre um modelo de contrato antes de executar este seeder.');

            return;
        }
        $contratosData = [
            [
                'numero' => '2026/0001',
                'tipo_modelo' => 'Contrato',
                'status' => 'Vigente',
                'imovel_id' => $imoveis->first()->id,
                'inquilino_id' => $inquilinos->first()->id,
                'criado_por' => $usuarios->first()->id,
                'modelo_id' => $modeloContrato->id,
                'valor_mensal' => 1800.00,
                'desconto_mensal' => 0.00,
                'multa_por_atraso' => 2.00,
                'multa_rescisao' => 3.00,
                'garantia' => 'Sem garantia',
                'valor_caucao' => null,
                'destinacao' => 'Residencial',
                'prazo_vigencia' => 'Determinado',
                'data_inicio' => '2026-01-01',
                'data_fim' => '2026-12-31',
                'indice_reajuste' => 'IPCA',
                'anexo_contrato_nome' => null,
                'anexo_contrato' => null,
                'observacoes' => 'Contrato de teste para desenvolvimento.',
                'motivo_cancelamento' => null,
                'documento_fisico_confirmado' => true,
                'dia_vencimento_parcela' => 5,
                'hash_blockchain' => null,
                'data_registro_blockchain' => null,
                'status_blockchain' => 'Aguardando registro',
            ],
            [
                'numero' => '2026/0002',
                'tipo_modelo' => 'Contrato',
                'status' => 'Vigente',
                'imovel_id' => $imoveis->get(1, $imoveis->first())->id,
                'inquilino_id' => $inquilinos->get(1, $inquilinos->first())->id,
                'criado_por' => $usuarios->first()->id,
                'modelo_id' => $modeloContrato->id,
                'valor_mensal' => 3200.00,
                'desconto_mensal' => 0.00,
                'multa_por_atraso' => 2.00,
                'multa_rescisao' => 3.00,
                'garantia' => 'Caução',
                'valor_caucao' => 2000.00,
                'destinacao' => 'Residencial',
                'prazo_vigencia' => 'Indeterminado',
                'data_inicio' => '2026-03-01',
                'data_fim' => null,
                'indice_reajuste' => 'IGP-M',
                'anexo_contrato_nome' => null,
                'anexo_contrato' => null,
                'observacoes' => 'Contrato por prazo indeterminado.',
                'motivo_cancelamento' => null,
                'documento_fisico_confirmado' => false,
                'dia_vencimento_parcela' => 10,
                'hash_blockchain' => null,
                'data_registro_blockchain' => null,
                'status_blockchain' => 'Aguardando registro',
            ],
            [
                'numero' => '2026/0003',
                'tipo_modelo' => 'Contrato',
                'status' => 'Rascunho',
                'imovel_id' => $imoveis->get(2, $imoveis->first())->id,
                'inquilino_id' => $inquilinos->get(2, $inquilinos->first())->id,
                'criado_por' => $usuarios->first()->id,
                'modelo_id' => $modeloContrato->id,
                'valor_mensal' => 2500.00,
                'desconto_mensal' => 100.00,
                'multa_por_atraso' => 2.00,
                'multa_rescisao' => 3.00,
                'garantia' => 'Sem garantia',
                'valor_caucao' => null,
                'destinacao' => 'Residencial',
                'prazo_vigencia' => 'Determinado',
                'data_inicio' => '2026-11-01',
                'data_fim' => '2027-10-31',
                'indice_reajuste' => 'IPCA',
                'anexo_contrato_nome' => null,
                'anexo_contrato' => null,
                'observacoes' => 'Contrato em fase de revisão.',
                'motivo_cancelamento' => null,
                'documento_fisico_confirmado' => false,
                'dia_vencimento_parcela' => 15,
                'hash_blockchain' => null,
                'data_registro_blockchain' => null,
                'status_blockchain' => 'Aguardando registro',
            ],
        ];

        foreach ($contratosData as $dados) {
            $contrato = Contrato::query()->create($dados);

            ContratoUtilidade::query()->create([
                'contrato_id' => $contrato->id,
                'tipo' => 'agua',
                'responsabilidade' => 'Locatário',
                'valor_estimado' => 80.00,
                'observacoes' => 'Leitura individual.',
                'ativo' => true,
            ]);
            ContratoUtilidade::query()->create([
                'contrato_id' => $contrato->id,
                'tipo' => 'luz',
                'responsabilidade' => 'Locatário',
                'valor_estimado' => 150.00,
                'observacoes' => null,
                'ativo' => true,
            ]);
            ContratoUtilidade::query()->create([
                'contrato_id' => $contrato->id,
                'tipo' => 'gas',
                'responsabilidade' => 'Compartilhado',
                'valor_estimado' => 60.00,
                'observacoes' => 'Botijão.',
                'ativo' => true,
            ]);
            ContratoUtilidade::query()->create([
                'contrato_id' => $contrato->id,
                'tipo' => 'internet',
                'responsabilidade' => 'Locador',
                'valor_estimado' => 100.00,
                'observacoes' => 'Incluso no aluguel.',
                'ativo' => true,
            ]);

            ContratoEncargoFixo::query()->create([
                'contrato_id' => $contrato->id,
                'tipo' => 'iptu',
                'responsabilidade' => 'Locatário',
                'valor_estimado' => 150.00,
                'ativo' => true,
            ]);
            ContratoEncargoFixo::query()->create([
                'contrato_id' => $contrato->id,
                'tipo' => 'condominio',
                'responsabilidade' => 'Locatário',
                'valor_estimado' => 450.00,
                'ativo' => true,
            ]);

            for ($mes = 1; $mes <= 3; $mes++) {
                ContratoParcela::query()->create([
                    'contrato_id' => $contrato->id,
                    'referencia' => sprintf('2026/%02d', $mes),
                    'vencimento' => Carbon::parse($dados['data_inicio'])->addMonths($mes - 1)->day($dados['dia_vencimento_parcela'])->format('Y-m-d'),
                    'valor' => $dados['valor_mensal'],
                    'juros' => 0.00,
                    'taxas_adicionais' => 0.00,
                    'valor_pago' => $mes === 1 ? $dados['valor_mensal'] : 0.00,
                    'data_pagamento' => $mes === 1 ? Carbon::parse($dados['data_inicio'])->addDays(3)->format('Y-m-d') : null,
                    'status' => $mes === 1 ? 'Pago' : 'Pendente',
                    'pago_em' => $mes === 1 ? Carbon::parse($dados['data_inicio'])->addDays(3)->format('Y-m-d H:i:s') : null,
                ]);
            }
        }

        $this->command?->info('Contratos criados: '.count($contratosData).' (com utilidades, encargos e parcelas)');
    }
}
