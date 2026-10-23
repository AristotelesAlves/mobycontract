<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('imovel_fotos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('imovel_id')->constrained('imoveis')->restrictOnDelete();
            $table->string('caminho');
            $table->string('nome');
            $table->string('mime', 50);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imovel_fotos');
    }
};
