<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contrato_aditivos', function (Blueprint $tabela): void {
            $tabela->id();
            $tabela->foreignId('contrato_id')->constrained('contratos')->cascadeOnDelete();
            $tabela->string('numero', 30);
            $tabela->string('titulo', 150);
            $tabela->date('data');
            $tabela->text('descricao')->nullable();
            $tabela->foreignId('criado_por')->constrained('users');
            $tabela->string('status', 30)->default('Rascunho');
            $tabela->json('motivos');
            $tabela->foreignId('modelo_id')->constrained('modelos');
            $tabela->string('modelo_titulo', 150);
            $tabela->json('dados_extras')->nullable();
            $tabela->boolean('upload_confirmado')->default(false);
            $tabela->string('anexo_nome', 255)->nullable();
            $tabela->string('hash_blockchain', 255)->nullable();
            $tabela->timestamp('data_registro_blockchain')->nullable();
            $tabela->string('status_blockchain', 30)->default('Aguardando registro');
            $tabela->text('motivo_cancelamento')->nullable();
            $tabela->timestamps();

            $tabela->unique(['contrato_id', 'numero']);
            $tabela->index('contrato_id');
            $tabela->index('status');
        });

        Schema::create('contrato_aditivo_assinaturas', function (Blueprint $tabela): void {
            $tabela->id();
            $tabela->foreignId('aditivo_id')->constrained('contrato_aditivos')->cascadeOnDelete();
            $tabela->string('papel', 30);
            $tabela->string('nome', 150);
            $tabela->boolean('assinado')->default(false);
            $tabela->timestamp('data_assinatura')->nullable();
            $tabela->timestamps();

            $tabela->index('aditivo_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contrato_aditivo_assinaturas');
        Schema::dropIfExists('contrato_aditivos');
    }
};
