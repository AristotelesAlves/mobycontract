<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personal_access_tokens', function (Blueprint $tabela): void {
            $tabela->id();
            $tabela->morphs('tokenable');
            $tabela->string('name', 120);
            $tabela->string('token', 64)->unique();
            $tabela->text('abilities')->nullable();
            $tabela->timestamp('last_used_at')->nullable();
            $tabela->timestamp('expires_at')->nullable();
            $tabela->timestamps();
        });

        Schema::create('fila', function (Blueprint $tabela): void {
            $tabela->id();
            $tabela->string('queue', 120)->index();
            $tabela->longText('payload');
            $tabela->unsignedTinyInteger('tentativas');
            $tabela->unsignedInteger('reserved_at')->nullable();
            $tabela->unsignedInteger('available_at');
            $tabela->unsignedInteger('created_at');
        });

        Schema::create('fila_falhas', function (Blueprint $tabela): void {
            $tabela->id();
            $tabela->string('uuid', 36)->unique();
            $tabela->text('conexao');
            $tabela->text('fila');
            $tabela->longText('payload');
            $tabela->longText('excecao');
            $tabela->timestamp('falhou_em')->useCurrent();
        });

        Schema::create('jobs_batches', function (Blueprint $tabela): void {
            $tabela->string('id', 36)->primary();
            $tabela->string('name', 120);
            $tabela->integer('total_jobs');
            $tabela->integer('pending_jobs');
            $tabela->integer('failed_jobs');
            $tabela->longText('failed_job_ids');
            $tabela->mediumText('options')->nullable();
            $tabela->integer('cancelled_at')->nullable();
            $tabela->integer('created_at');
            $tabela->integer('finished_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jobs_batches');
        Schema::dropIfExists('fila_falhas');
        Schema::dropIfExists('fila');
        Schema::dropIfExists('personal_access_tokens');
    }
};
