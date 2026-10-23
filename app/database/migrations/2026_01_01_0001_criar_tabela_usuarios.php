<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $tabela): void {
            $tabela->id();
            $tabela->string('nome', 150);
            $tabela->string('email', 180)->unique();
            $tabela->timestamp('email_verificado_em')->nullable();
            $tabela->string('password', 255);
            $tabela->string('papel', 32)->default('Operador');
            $tabela->boolean('ativo')->default(true);
            $tabela->rememberToken();
            $tabela->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $tabela): void {
            $tabela->string('email', 180)->primary();
            $tabela->string('token', 255);
            $tabela->timestamp('criado_em')->nullable();
        });

        Schema::create('sessions', function (Blueprint $tabela): void {
            $tabela->string('id', 255)->primary();
            $tabela->unsignedBigInteger('usuario_id')->nullable()->index();
            $tabela->string('ip_address', 45)->nullable();
            $tabela->text('agent')->nullable();
            $tabela->longText('payload');
            $tabela->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
