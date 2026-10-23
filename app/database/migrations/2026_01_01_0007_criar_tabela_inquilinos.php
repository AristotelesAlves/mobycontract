<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inquilinos', function (Blueprint $tabela): void {
            $tabela->id();
            $tabela->string('nome', 150);
            $tabela->string('cpf', 14)->unique();
            $tabela->string('tipo_pessoa', 2)->default('PF');
            $tabela->string('rg', 30)->nullable();
            $tabela->string('email', 180)->unique();
            $tabela->string('telefone', 30);
            $tabela->date('data_nascimento');
            $tabela->string('estado_civil', 32);
            $tabela->string('profissao', 120)->nullable();
            $tabela->decimal('renda', 12, 2);
            $tabela->string('endereco', 255);
            $tabela->foreignId('cliente_id')->nullable()->constrained('leads')->nullOnDelete();
            $tabela->timestamp('data_cadastro')->useCurrent();
            $tabela->timestamps();

            $tabela->index('tipo_pessoa');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inquilinos');
    }
};
