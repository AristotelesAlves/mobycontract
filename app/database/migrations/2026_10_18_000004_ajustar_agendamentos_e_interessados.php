<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        Schema::table('agendamentos', function (Blueprint $tabela): void {
            $tabela->foreignId('interessado_id')->nullable()->constrained('interessados')->nullOnDelete();
            $tabela->string('telefone_contato', 30)->nullable()->after('telefone');
        });

        Schema::table('interessados', function (Blueprint $tabela): void {
            $tabela->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $tabela->foreignId('contrato_id')->nullable()->constrained('contratos')->nullOnDelete();
            $tabela->index('lead_id');
            $tabela->index('contrato_id');
        });
    }

    public function down(): void
    {
        Schema::table('agendamentos', function (Blueprint $tabela): void {
            $tabela->dropForeign(['interessado_id']);
            $tabela->dropColumn(['interessado_id', 'telefone_contato']);
        });

        Schema::table('interessados', function (Blueprint $tabela): void {
            $tabela->dropForeign(['lead_id']);
            $tabela->dropForeign(['contrato_id']);
            $tabela->dropColumn(['lead_id', 'contrato_id']);
        });
    }
};
