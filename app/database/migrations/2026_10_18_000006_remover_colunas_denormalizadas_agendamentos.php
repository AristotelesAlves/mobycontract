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
            $tabela->dropColumn(['interessado', 'telefone']);
        });
    }

    public function down(): void
    {
        Schema::table('agendamentos', function (Blueprint $tabela): void {
            $tabela->string('interessado', 150)->nullable()->after('hora');
            $tabela->string('telefone', 30)->nullable()->after('interessado');
        });
    }
};
