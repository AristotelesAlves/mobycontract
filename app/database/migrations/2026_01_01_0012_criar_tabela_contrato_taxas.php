<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contrato_taxas', function (Blueprint $tabela): void {
            $tabela->id();
            $tabela->foreignId('contrato_id')->constrained('contratos')->cascadeOnDelete();
            $tabela->string('nome', 150);
            $tabela->boolean('opcional')->default(false);
            $tabela->string('responsavel', 50)->default('Locatário');
            $tabela->string('periodicidade', 50)->default('Mensal');
            $tabela->decimal('valor', 12, 2);
            $tabela->boolean('ativa')->default(true);
            $tabela->timestamps();

            $tabela->index(['contrato_id', 'ativa']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contrato_taxas');
    }
};
