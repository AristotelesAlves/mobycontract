<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $tabela): void {
            $tabela->id();
            $tabela->string('nome', 150);
            $tabela->string('telefone', 30);
            $tabela->string('email', 180)->nullable();
            $tabela->text('imovel_de_interesse')->nullable();
            $tabela->string('origem', 32)->default('Outro');
            $tabela->string('status', 32)->default('Novo');
            $tabela->text('observacoes')->nullable();
            $tabela->timestamp('convertido_em')->nullable();
            $tabela->timestamp('data_cadastro')->useCurrent();
            $tabela->timestamps();

            $tabela->index('status');
            $tabela->index('origem');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
