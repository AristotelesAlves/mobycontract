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
        Schema::create('regras_imobiliarias', function (Blueprint $tabela): void {
            $tabela->id();
            $tabela->boolean('contrato_unico_por_imovel')->default(true)
                ->comment('Bloqueia um segundo contrato ativo para o mesmo imóvel.');
            $tabela->boolean('contrato_unico_por_inquilino')->default(true)
                ->comment('Bloqueia um segundo contrato ativo para o mesmo locatário.');
            $tabela->timestamps();
        });

        DB::table('regras_imobiliarias')->insert([
            'contrato_unico_por_imovel' => true,
            'contrato_unico_por_inquilino' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('regras_imobiliarias');
    }
};
