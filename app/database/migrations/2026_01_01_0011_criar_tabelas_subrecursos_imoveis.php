<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anotacoes', function (Blueprint $tabela): void {
            $tabela->id();
            $tabela->foreignId('imovel_id')->constrained('imoveis')->cascadeOnDelete();
            $tabela->text('texto');
            $tabela->foreignId('autor_id')->constrained('users')->cascadeOnDelete();
            $tabela->timestamps();

            $tabela->index('imovel_id');
        });

        Schema::create('agendamentos', function (Blueprint $tabela): void {
            $tabela->id();
            $tabela->foreignId('imovel_id')->constrained('imoveis')->cascadeOnDelete();
            $tabela->date('data');
            $tabela->string('hora', 10);
            $tabela->string('interessado', 150);
            $tabela->string('telefone', 30)->nullable();
            $tabela->string('status', 32)->default('Agendado');
            $tabela->timestamps();

            $tabela->index(['imovel_id', 'status']);
        });

        Schema::create('interessados', function (Blueprint $tabela): void {
            $tabela->id();
            $tabela->foreignId('imovel_id')->constrained('imoveis')->cascadeOnDelete();
            $tabela->string('nome', 150);
            $tabela->string('telefone', 30);
            $tabela->string('email', 150)->nullable();
            $tabela->string('tipo', 32);
            $tabela->string('status', 32)->default('Novo');
            $tabela->timestamps();

            $tabela->index(['imovel_id', 'status']);
        });

        Schema::create('manutencoes', function (Blueprint $tabela): void {
            $tabela->id();
            $tabela->foreignId('imovel_id')->constrained('imoveis')->cascadeOnDelete();
            $tabela->string('titulo', 150);
            $tabela->text('descricao')->nullable();
            $tabela->string('responsavel', 150)->nullable();
            $tabela->decimal('custo', 12, 2)->default(0);
            $tabela->string('status', 32)->default('Aberto');
            $tabela->timestamps();

            $tabela->index(['imovel_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manutencoes');
        Schema::dropIfExists('interessados');
        Schema::dropIfExists('agendamentos');
        Schema::dropIfExists('anotacoes');
    }
};
