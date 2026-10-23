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
        Schema::create('profissoes', function (Blueprint $table): void {
            $table->id();
            $table->string('nome', 120)->unique();
        });
        $nomes = ['Administrador(a)', 'Advogado(a)', 'Analista de sistemas', 'Aposentado(a)', 'Arquiteto(a)', 'Autônomo(a)', 'Comerciante', 'Contador(a)', 'Dentista', 'Empresário(a)', 'Enfermeiro(a)', 'Engenheiro(a)', 'Estudante', 'Médico(a)', 'Motorista', 'Professor(a)', 'Psicólogo(a)', 'Servidor(a) público(a)', 'Técnico(a)', 'Vendedor(a)'];
        DB::table('profissoes')->insert(array_map(static fn (string $nome): array => ['nome' => $nome], $nomes));
    }

    public function down(): void
    {
        Schema::dropIfExists('profissoes');
    }
};
