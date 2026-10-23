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
        Schema::create('modelos', function (Blueprint $tabela): void {
            $tabela->id();
            $tabela->integer('codigo')->unique();
            $tabela->string('titulo', 150);
            $tabela->string('tipo', 20);
            $tabela->boolean('status')->default(true);
            $tabela->integer('versao')->default(1);
            $tabela->text('conteudo_html');
            $tabela->string('garantia', 60)->nullable();
            $tabela->json('margens');
            $tabela->json('tipografia');
            $tabela->timestamps();

            $tabela->index('tipo');
            $tabela->index('status');
        });

        DB::table('sequences')->insertOrIgnore([
            'nome' => 'modelo_codigo',
            'valor' => 0,
            'created_at' => now(),
            'updated_at' => now(),
            'atualizado_em' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('modelos');
        DB::table('sequences')->where('nome', 'modelo_codigo')->delete();
    }
};
