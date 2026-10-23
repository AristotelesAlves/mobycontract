<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contratos', function (Blueprint $tabela): void {
            $tabela->id();
            $tabela->string('numero', 20)->unique();
            $tabela->string('tipo_modelo', 20)->default('Contrato');
            $tabela->string('status', 20)->default('Rascunho');

            $tabela->foreignId('imovel_id')->constrained('imoveis');
            $tabela->string('imovel_titulo', 150);
            $tabela->string('imovel_endereco', 255)->nullable();
            $tabela->string('imovel_cidade', 120)->nullable();
            $tabela->string('imovel_matricula', 80)->nullable();

            $tabela->foreignId('inquilino_id')->constrained('inquilinos');
            $tabela->string('inquilino_nome', 150);

            $tabela->foreignId('criado_por')->constrained('users');

            $tabela->foreignId('modelo_id')->constrained('modelos');
            $tabela->string('modelo_titulo', 150);
            $tabela->integer('modelo_versao');

            $tabela->decimal('valor_mensal', 12, 2);
            $tabela->decimal('desconto_mensal', 12, 2)->default(0);
            $tabela->decimal('multa_por_atraso', 6, 2)->default(0);
            $tabela->decimal('multa_rescisao', 6, 2)->default(3);

            $tabela->string('garantia', 60);
            $tabela->decimal('valor_caucao', 12, 2)->nullable();

            $tabela->string('destinacao', 20);
            $tabela->string('prazo_vigencia', 20);
            $tabela->date('data_inicio');
            $tabela->date('data_fim')->nullable();
            $tabela->string('indice_reajuste', 20);

            $tabela->string('responsabilidade_iptu', 20);
            $tabela->decimal('valor_estimado_iptu', 12, 2)->nullable();
            $tabela->string('responsabilidade_condominio', 20);
            $tabela->decimal('valor_estimado_condominio', 12, 2)->nullable();

            $tabela->string('consumo_agua', 40);
            $tabela->string('consumo_luz', 40);
            $tabela->string('consumo_gas', 40);
            $tabela->string('consumo_internet', 40);

            $tabela->string('anexo_contrato_nome', 255)->nullable();
            $tabela->string('anexo_contrato', 500)->nullable();
            $tabela->text('observacoes')->nullable();

            $tabela->text('motivo_cancelamento')->nullable();
            $tabela->boolean('documento_fisico_confirmado')->default(false);
            $tabela->integer('dia_vencimento_parcela')->default(5);

            $tabela->string('hash_blockchain', 255)->nullable();
            $tabela->timestamp('data_registro_blockchain')->nullable();
            $tabela->string('status_blockchain', 30)->default('Aguardando registro');

            $tabela->timestamps();

            $tabela->index('status');
            $tabela->index('imovel_id');
            $tabela->index('inquilino_id');
        });

        DB::statement(
            "CREATE UNIQUE INDEX contratos_imovel_ativo_unico
             ON contratos (imovel_id)
             WHERE status IN ('Validado', 'Vigente')"
        );

        DB::statement(
            "CREATE UNIQUE INDEX contratos_inquilino_ativo_unico
             ON contratos (inquilino_id)
             WHERE status IN ('Validado', 'Vigente')"
        );

        Schema::create('contrato_fiadores', function (Blueprint $tabela): void {
            $tabela->id();
            $tabela->foreignId('contrato_id')->constrained('contratos')->cascadeOnDelete();
            $tabela->string('nome', 150);
            $tabela->string('cpf', 14);
            $tabela->string('telefone', 30);
            $tabela->timestamps();
        });

        Schema::create('contrato_assinaturas', function (Blueprint $tabela): void {
            $tabela->id();
            $tabela->foreignId('contrato_id')->constrained('contratos')->cascadeOnDelete();
            $tabela->string('papel', 30);
            $tabela->string('nome', 150);
            $tabela->boolean('assinado')->default(false);
            $tabela->timestamp('data_assinatura')->nullable();
            $tabela->timestamps();

            $tabela->index('contrato_id');
        });

        Schema::create('contrato_parcelas', function (Blueprint $tabela): void {
            $tabela->id();
            $tabela->foreignId('contrato_id')->constrained('contratos')->cascadeOnDelete();
            $tabela->string('referencia', 20);
            $tabela->date('vencimento');
            $tabela->decimal('valor', 12, 2);
            $tabela->decimal('juros', 12, 2)->default(0);
            $tabela->decimal('taxas_adicionais', 12, 2)->default(0);
            $tabela->decimal('valor_pago', 12, 2)->default(0);
            $tabela->date('data_pagamento')->nullable();
            $tabela->string('status', 20)->default('Pendente');
            $tabela->timestamp('pago_em')->nullable();
            $tabela->timestamps();

            $tabela->unique(['contrato_id', 'referencia']);
            $tabela->index('status');
        });

        Schema::create('contrato_encargos', function (Blueprint $tabela): void {
            $tabela->id();
            $tabela->foreignId('contrato_id')->constrained('contratos')->cascadeOnDelete();
            $tabela->foreignId('parcela_id')->constrained('contrato_parcelas')->cascadeOnDelete();
            $tabela->string('tipo', 20);
            $tabela->decimal('valor', 12, 2);
            $tabela->string('motivo', 255);
            $tabela->foreignId('criado_por')->nullable()->constrained('users');
            $tabela->timestamps();
        });

        Schema::create('contrato_historicos', function (Blueprint $tabela): void {
            $tabela->id();
            $tabela->foreignId('contrato_id')->constrained('contratos')->cascadeOnDelete();
            $tabela->timestamp('data');
            $tabela->string('evento', 200);
            $tabela->text('detalhe')->nullable();
            $tabela->foreignId('usuario_id')->nullable()->constrained('users');
            $tabela->timestamps();

            $tabela->index('contrato_id');
        });

        Schema::create('contrato_alteracoes', function (Blueprint $tabela): void {
            $tabela->id();
            $tabela->foreignId('contrato_id')->constrained('contratos')->cascadeOnDelete();
            $tabela->timestamp('data');
            $tabela->foreignId('usuario_id')->nullable()->constrained('users');
            $tabela->string('usuario', 150)->nullable();
            $tabela->string('acao', 40);
            $tabela->string('campo', 120);
            $tabela->text('de')->nullable();
            $tabela->text('para')->nullable();
            $tabela->timestamps();

            $tabela->index('contrato_id');
            $tabela->index('acao');
        });

        DB::table('sequences')->insertOrIgnore([
            'nome' => 'contrato',
            'valor' => 0,
            'created_at' => now(),
            'updated_at' => now(),
            'atualizado_em' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('contrato_alteracoes');
        Schema::dropIfExists('contrato_historicos');
        Schema::dropIfExists('contrato_encargos');
        Schema::dropIfExists('contrato_parcelas');
        Schema::dropIfExists('contrato_assinaturas');
        Schema::dropIfExists('contrato_fiadores');
        Schema::dropIfExists('contratos');

        DB::table('sequences')->where('nome', 'contrato')->delete();
    }
};
