<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vistorias', function (Blueprint $tabela): void {
            $tabela->id();
            $tabela->foreignId('contrato_id')->constrained('contratos')->cascadeOnDelete();
            $tabela->string('numero', 20);
            $tabela->string('tipo', 20)->comment('Entrada, Retirada ou Periódica');
            $tabela->string('vistoriador', 120)->nullable();
            $tabela->date('data');
            $tabela->string('status', 20)->default('Agendada')
                ->comment('Agendada, Em andamento, Concluída ou Cancelada');
            $tabela->text('observacoes')->nullable();
            $tabela->uuid('chave_acesso')->unique()
                ->comment('Chave para acesso externo do vistoriador.');
            $tabela->timestamps();

            $tabela->unique(['contrato_id', 'numero']);
            $tabela->index('contrato_id');
            $tabela->index('status');
            $tabela->index('data');
        });

        Schema::create('vistoria_itens', function (Blueprint $tabela): void {
            $tabela->id();
            $tabela->foreignId('vistoria_id')->constrained('vistorias')->cascadeOnDelete();
            $tabela->string('ambiente', 80);
            $tabela->string('item', 120);
            $tabela->string('estado', 20)->comment('Ótimo, Bom, Regular, Ruim ou Substituir');
            $tabela->text('observacao')->nullable();
            $tabela->timestamps();

            $tabela->index('vistoria_id');
        });

        Schema::create('vistoria_fotos', function (Blueprint $tabela): void {
            $tabela->id();
            $tabela->foreignId('vistoria_id')->constrained('vistorias')->cascadeOnDelete();
            $tabela->string('descricao', 120)->nullable();
            $tabela->string('nome_anexo', 200);
            $tabela->string('anexo', 200)->nullable();
            $tabela->timestamps();

            $tabela->index('vistoria_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vistoria_fotos');
        Schema::dropIfExists('vistoria_itens');
        Schema::dropIfExists('vistorias');
    }
};
