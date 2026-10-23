<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        Schema::table('contratos', function (Blueprint $tabela): void {
            $tabela->dropColumn([
                'imovel_titulo',
                'imovel_endereco',
                'imovel_cidade',
                'imovel_matricula',
                'inquilino_nome',
                'modelo_titulo',
                'modelo_versao',
                'consumo_agua',
                'consumo_luz',
                'consumo_gas',
                'consumo_internet',
                'responsabilidade_iptu',
                'valor_estimado_iptu',
                'responsabilidade_condominio',
                'valor_estimado_condominio',
            ]);
        });

        Schema::table('contrato_aditivos', function (Blueprint $tabela): void {
            $tabela->dropColumn(['modelo_titulo']);
        });
    }

    public function down(): void
    {
        Schema::table('contratos', function (Blueprint $tabela): void {
            $tabela->string('imovel_titulo', 150)->nullable()->after('imovel_id');
            $tabela->string('imovel_endereco', 255)->nullable()->after('imovel_titulo');
            $tabela->string('imovel_cidade', 120)->nullable()->after('imovel_endereco');
            $tabela->string('imovel_matricula', 80)->nullable()->after('imovel_cidade');
            $tabela->string('inquilino_nome', 150)->nullable()->after('inquilino_id');
            $tabela->string('modelo_titulo', 150)->nullable()->after('modelo_id');
            $tabela->integer('modelo_versao')->nullable()->after('modelo_titulo');
            $tabela->string('consumo_agua', 40)->nullable();
            $tabela->string('consumo_luz', 40)->nullable();
            $tabela->string('consumo_gas', 40)->nullable();
            $tabela->string('consumo_internet', 40)->nullable();
            $tabela->string('responsabilidade_iptu', 20)->nullable();
            $tabela->decimal('valor_estimado_iptu', 12, 2)->nullable();
            $tabela->string('responsabilidade_condominio', 20)->nullable();
            $tabela->decimal('valor_estimado_condominio', 12, 2)->nullable();
        });

        Schema::table('contrato_aditivos', function (Blueprint $tabela): void {
            $tabela->string('modelo_titulo', 150)->nullable()->after('modelo_id');
        });
    }
};
