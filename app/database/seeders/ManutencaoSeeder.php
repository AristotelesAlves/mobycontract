<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Imoveis\Models\Imovel;
use App\Modules\Imoveis\Models\Manutencao;
use Illuminate\Database\Seeder;

final class ManutencaoSeeder extends Seeder
{
    public function run(): void
    {
        $imoveis = Imovel::all();

        if ($imoveis->isEmpty()) {
            $this->command?->warn('Executa o ImovelSeeder primeiro.');

            return;
        }

        $manutencoes = [
            [
                'imovel_id' => $imoveis->first()->id,
                'titulo' => 'Conserto de vazamento na cozinha',
                'descricao' => 'Vazamento na torneira da pia da cozinha.',
                'responsavel' => 'Encanador João',
                'custo' => 150.00,
                'status' => 'Concluido',
            ],
            [
                'imovel_id' => $imoveis->get(1, $imoveis->first())->id,
                'titulo' => 'Pintura da fachada',
                'descricao' => 'Reformulações na pintura externa do imóvel.',
                'responsavel' => 'Pintor Silva',
                'custo' => 1200.00,
                'status' => 'Aberto',
            ],
        ];

        foreach ($manutencoes as $manutencao) {
            Manutencao::query()->create($manutencao);
        }

        $this->command?->info('Manutencoes criadas: '.count($manutencoes));
    }
}
