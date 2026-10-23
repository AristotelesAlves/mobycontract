<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contratos', function (): void {
            Schema::getConnection()->statement(
                'DROP INDEX IF EXISTS contratos_inquilino_ativo_unico'
            );

            Schema::getConnection()->statement(
                'DROP INDEX IF EXISTS contratos_imovel_ativo_unico'
            );

            Schema::getConnection()->statement(
                "CREATE UNIQUE INDEX contratos_imovel_ativo_unico
                 ON contratos (imovel_id)
                 WHERE status NOT IN ('Encerrado', 'Cancelado')"
            );
        });
    }

    public function down(): void
    {
        Schema::getConnection()->statement(
            'DROP INDEX IF EXISTS contratos_imovel_ativo_unico'
        );

        Schema::getConnection()->statement(
            "CREATE UNIQUE INDEX contratos_imovel_ativo_unico
             ON contratos (imovel_id)
             WHERE status IN ('Validado', 'Vigente')"
        );

        Schema::getConnection()->statement(
            "CREATE UNIQUE INDEX contratos_inquilino_ativo_unico
             ON contratos (inquilino_id)
             WHERE status IN ('Validado', 'Vigente')"
        );
    }
};
