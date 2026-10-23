<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Imoveis\Models\Agendamento;
use App\Modules\Imoveis\Models\Imovel;
use App\Modules\Imoveis\Models\Interessado;
use Illuminate\Database\Seeder;

final class AgendamentoSeeder extends Seeder
{
    public function run(): void
    {
        $imoveis = Imovel::all();
        $interessados = Interessado::all();

        if ($imoveis->isEmpty() || $interessados->isEmpty()) {
            $this->command?->warn('Executa os seeders de imoveis e interessados primeiro.');

            return;
        }

        $agendamentos = [
            [
                'imovel_id' => $imoveis->first()->id,
                'interessado_id' => $interessados->first()->id,
                'data' => now()->addDays(2)->format('Y-m-d'),
                'hora' => '14:00',
                'telefone_contato' => '(48) 93333-7777',
                'status' => 'Agendado',
            ],
            [
                'imovel_id' => $imoveis->get(1, $imoveis->first())->id,
                'interessado_id' => $interessados->get(1, $interessados->first())->id,
                'data' => now()->addDays(5)->format('Y-m-d'),
                'hora' => '10:00',
                'telefone_contato' => '(48) 92222-8888',
                'status' => 'Agendado',
            ],
        ];

        foreach ($agendamentos as $agendamento) {
            Agendamento::query()->create($agendamento);
        }

        $this->command?->info('Agendamentos criados: '.count($agendamentos));
    }
}
