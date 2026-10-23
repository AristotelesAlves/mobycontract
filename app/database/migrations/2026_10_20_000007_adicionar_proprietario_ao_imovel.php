<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('imoveis', fn (Blueprint $table) => $table->string('nome_proprietario', 150)->nullable());
    }

    public function down(): void
    {
        Schema::table('imoveis', fn (Blueprint $table) => $table->dropColumn('nome_proprietario'));
    }
};
