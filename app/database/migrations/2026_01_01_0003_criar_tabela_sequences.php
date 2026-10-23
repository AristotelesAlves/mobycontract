<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sequences', function (Blueprint $tabela): void {
            $tabela->string('nome', 80)->primary();
            $tabela->unsignedBigInteger('valor')->default(0);
            $tabela->timestampsTz();
            $tabela->timestamp('atualizado_em')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sequences');
    }
};
