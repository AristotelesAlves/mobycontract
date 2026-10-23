<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('contrato_fiadores', 'contrato_partes');

        Schema::table('contrato_partes', function (Blueprint $tabela): void {
            $tabela->string('tipo', 20)->default('fiador')->after('contrato_id')->comment('fiador, garantidor ou avalista');
            $tabela->string('email', 180)->nullable()->after('telefone');
            $tabela->index('tipo');
        });
    }

    public function down(): void
    {
        Schema::table('contrato_partes', function (Blueprint $tabela): void {
            $tabela->dropColumn(['tipo', 'email']);
        });

        Schema::rename('contrato_partes', 'contrato_fiadores');
    }
};
