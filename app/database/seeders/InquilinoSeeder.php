<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Inquilinos\Models\Inquilino;
use Illuminate\Database\Seeder;

final class InquilinoSeeder extends Seeder
{
    public function run(): void
    {
        $inquilinos = [
            [
                'nome' => 'João Silva Santos',
                'cpf' => '12345678901',
                'tipo_pessoa' => 'PF',
                'rg' => '1234567',
                'email' => 'joao.silva@email.com',
                'telefone' => '(48) 99999-1111',
                'data_nascimento' => '1985-03-15',
                'estado_civil' => 'Solteiro',
                'profissao' => 'Engenheiro',
                'renda' => 6500.00,
                'endereco' => 'Rua A, 100 - Centro, Florianópolis - SC',
                'cliente_id' => null,
                'data_cadastro' => now(),
            ],
            [
                'nome' => 'Maria Oliveira Souza',
                'cpf' => '98765432100',
                'tipo_pessoa' => 'PF',
                'rg' => '7654321',
                'email' => 'maria.oliveira@email.com',
                'telefone' => '(48) 98888-2222',
                'data_nascimento' => '1990-07-22',
                'estado_civil' => 'Casado',
                'profissao' => 'Advogada',
                'renda' => 8200.00,
                'endereco' => 'Rua B, 200 - Trindade, Florianópolis - SC',
                'cliente_id' => null,
                'data_cadastro' => now(),
            ],
            [
                'nome' => 'Carlos Eduardo Lima',
                'cpf' => '45678912300',
                'tipo_pessoa' => 'PF',
                'rg' => '3456789',
                'email' => 'carlos.lima@email.com',
                'telefone' => '(48) 97777-3333',
                'data_nascimento' => '1982-11-08',
                'estado_civil' => 'Divorciado',
                'profissao' => 'Professor',
                'renda' => 4800.00,
                'endereco' => 'Rua C, 300 - Jurerê, Florianópolis - SC',
                'cliente_id' => null,
                'data_cadastro' => now(),
            ],
        ];

        foreach ($inquilinos as $inquilino) {
            Inquilino::query()->create($inquilino);
        }

        $this->command?->info('Inquilinos criados: '.count($inquilinos));
    }
}
