<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Leads\Models\Lead;
use Illuminate\Database\Seeder;

final class LeadSeeder extends Seeder
{
    public function run(): void
    {
        $leads = [
            [
                'nome' => 'Ana Pereira Costa',
                'telefone' => '(48) 96666-4444',
                'email' => 'ana.pereira@email.com',
                'imovel_de_interesse' => 'Apartamento no Centro',
                'origem' => 'Site',
                'status' => 'Novo',
                'observacoes' => 'Interessado em imóvel com 2 quartos.',
                'convertido_em' => null,
                'data_cadastro' => now(),
            ],
            [
                'nome' => 'Roberto Almeida',
                'telefone' => '(48) 95555-5555',
                'email' => 'roberto.almeida@email.com',
                'imovel_de_interesse' => 'Casa com quintal',
                'origem' => 'Indicação',
                'status' => 'Em Negociação',
                'observacoes' => 'Quer visitar o imóvel esta semana.',
                'convertido_em' => null,
                'data_cadastro' => now(),
            ],
            [
                'nome' => 'Fernanda Souza',
                'telefone' => '(48) 94444-6666',
                'email' => 'fernanda.souza@email.com',
                'imovel_de_interesse' => 'Studio perto da praia',
                'origem' => 'Rede Social',
                'status' => 'Convertido',
                'observacoes' => 'Convertida em inquilina.',
                'convertido_em' => now(),
                'data_cadastro' => now(),
            ],
        ];

        foreach ($leads as $lead) {
            Lead::query()->create($lead);
        }

        $this->command?->info('Leads criados: '.count($leads));
    }
}
