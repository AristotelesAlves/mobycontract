<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contrato_encargos_fixos', function (Blueprint $tabela): void {
            $tabela->id();
            $tabela->foreignId('contrato_id')->constrained('contratos')->cascadeOnDelete();
            $tabela->string('tipo', 20)->comment('iptu ou condominio');
            $tabela->string('responsabilidade', 20)->default('Locatario')->comment('Locador, Locatario ou Compartilhado');
            $tabela->decimal('valor_estimado', 12, 2)->default(0);
            $tabela->boolean('ativo')->default(true);
            $tabela->timestamps();

            $tabela->unique(['contrato_id', 'tipo']);
            $tabela->index('contrato_id');
            $tabela->index('tipo');
            $tabela->index('ativo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contrato_encargos_fixos');
    }
};
