<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Imoveis\Models\Imovel;
use App\Modules\Imoveis\Models\Interessado;
use Illuminate\Database\Seeder;

final class InteressadoSeeder extends Seeder
{
    public function run(): void
    {
        $imoveis = Imovel::all();

        if ($imoveis->isEmpty()) {
            $this->command?->warn('Executa o ImovelSeeder primeiro.');

            return;
        }

        $interessados = [
            [
                'imovel_id' => $imoveis->first()->id,
                'lead_id' => null,
                'contrato_id' => null,
                'nome' => 'Lucas Mendes',
                'telefone' => '(48) 93333-7777',
                'email' => 'lucas.mendes@email.com',
                'tipo' => 'Visitar',
                'status' => 'Novo',
            ],
            [
                'imovel_id' => $imoveis->get(1, $imoveis->first())->id,
                'lead_id' => null,
                'contrato_id' => null,
                'nome' => 'Patrícia Gomes',
                'telefone' => '(48) 92222-8888',
                'email' => 'patricia.gomes@email.com',
                'tipo' => 'Visitar',
                'status' => 'Em Visita',
            ],
        ];

        foreach ($interessados as $interessado) {
            Interessado::query()->create($interessado);
        }

        $this->command?->info('Interessados criados: '.count($interessados));
    }
}
