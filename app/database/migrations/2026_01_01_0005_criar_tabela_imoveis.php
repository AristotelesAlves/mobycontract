<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('imoveis', function (Blueprint $tabela): void {
            $tabela->id();
            $tabela->string('titulo', 150);
            $tabela->string('tipo', 32);
            $tabela->string('status', 32)->default('Disponível');
            $tabela->string('cep', 8);
            $tabela->string('logradouro', 180);
            $tabela->string('numero', 20);
            $tabela->string('complemento', 120)->nullable();
            $tabela->string('bairro', 120)->nullable();
            $tabela->string('cidade', 120);
            $tabela->string('estado', 2)->nullable();
            $tabela->string('endereco', 255);
            $tabela->decimal('area', 10, 2);
            $tabela->unsignedSmallInteger('quartos')->default(0);
            $tabela->unsignedSmallInteger('banheiros')->default(0);
            $tabela->unsignedSmallInteger('vagas_garagem')->default(0);
            $tabela->decimal('valor_aluguel', 12, 2);
            $tabela->decimal('valor_iptu', 12, 2)->nullable();
            $tabela->decimal('valor_condominio', 12, 2)->nullable();
            $tabela->boolean('agua_inclusa')->default(false);
            $tabela->boolean('energia_inclusa')->default(false);
            $tabela->boolean('internet_inclusa')->default(false);
            $tabela->boolean('gas_incluso')->default(false);
            $tabela->string('matricula', 120)->nullable();
            $tabela->text('descricao')->nullable();
            $tabela->timestamps();

            $tabela->index('status');
            $tabela->index('tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imoveis');
    }
};
