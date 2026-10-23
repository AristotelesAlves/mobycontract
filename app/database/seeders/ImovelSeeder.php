<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Imoveis\Models\Imovel;
use Illuminate\Database\Seeder;

final class ImovelSeeder extends Seeder
{
    public function run(): void
    {
        $imoveis = [
            [
                'titulo' => 'Apartamento Centro - 2 Quartos',
                'tipo' => 'Apartamento',
                'status' => 'Alugado',
                'cep' => '88010000',
                'logradouro' => 'Rua das Flores',
                'numero' => '120',
                'complemento' => 'Apto 101',
                'bairro' => 'Centro',
                'cidade' => 'Florianópolis',
                'estado' => 'SC',
                'endereco' => 'Rua das Flores, 120 - Centro, Florianópolis - SC',
                'area' => 65.50,
                'quartos' => 2,
                'banheiros' => 1,
                'vagas_garagem' => 1,
                'valor_aluguel' => 1800.00,
                'valor_iptu' => 150.00,
                'valor_condominio' => 450.00,
                'agua_inclusa' => false,
                'energia_inclusa' => false,
                'internet_inclusa' => false,
                'gas_incluso' => false,
                'matricula' => '12345/2024',
                'descricao' => 'Apartamento bem localizado no centro da cidade.',
            ],
            [
                'titulo' => 'Casa Jardim - 3 Quartos',
                'tipo' => 'Casa',
                'status' => 'Disponível',
                'cep' => '88020000',
                'logradouro' => 'Avenida das Nações',
                'numero' => '500',
                'complemento' => null,
                'bairro' => 'Jardim das Américas',
                'cidade' => 'Florianópolis',
                'estado' => 'SC',
                'endereco' => 'Avenida das Nações, 500 - Jardim das Américas, Florianópolis - SC',
                'area' => 120.00,
                'quartos' => 3,
                'banheiros' => 2,
                'vagas_garagem' => 2,
                'valor_aluguel' => 3200.00,
                'valor_iptu' => 300.00,
                'valor_condominio' => null,
                'agua_inclusa' => false,
                'energia_inclusa' => false,
                'internet_inclusa' => false,
                'gas_incluso' => false,
                'matricula' => '67890/2023',
                'descricao' => 'Casa espaçosa com quintal e garagem coberta.',
            ],
            [
                'titulo' => 'Studio Praia - 1 Quarto',
                'tipo' => 'Studio',
                'status' => 'Alugado',
                'cep' => '88030000',
                'logradouro' => 'Beira Mar',
                'numero' => '1000',
                'complemento' => 'Bloco B',
                'bairro' => 'Jurerê',
                'cidade' => 'Florianópolis',
                'estado' => 'SC',
                'endereco' => 'Beira Mar, 1000 - Jurerê, Florianópolis - SC',
                'area' => 35.00,
                'quartos' => 1,
                'banheiros' => 1,
                'vagas_garagem' => 0,
                'valor_aluguel' => 2500.00,
                'valor_iptu' => 200.00,
                'valor_condominio' => 600.00,
                'agua_inclusa' => true,
                'energia_inclusa' => false,
                'internet_inclusa' => true,
                'gas_incluso' => false,
                'matricula' => '11111/2024',
                'descricao' => 'Studio frente ao mar com vista panorâmica.',
            ],
            [
                'titulo' => 'Cobertura Nobre - 4 Quartos',
                'tipo' => 'Cobertura',
                'status' => 'Disponível',
                'cep' => '88040000',
                'logradouro' => 'Rua Oscar Freire',
                'numero' => '200',
                'complemento' => 'Cobertura',
                'bairro' => 'Trindade',
                'cidade' => 'Florianópolis',
                'estado' => 'SC',
                'endereco' => 'Rua Oscar Freire, 200 - Trindade, Florianópolis - SC',
                'area' => 200.00,
                'quartos' => 4,
                'banheiros' => 3,
                'vagas_garagem' => 3,
                'valor_aluguel' => 5500.00,
                'valor_iptu' => 500.00,
                'valor_condominio' => 900.00,
                'agua_inclusa' => false,
                'energia_inclusa' => false,
                'internet_inclusa' => false,
                'gas_incluso' => false,
                'matricula' => '22222/2024',
                'descricao' => 'Cobertura com piscina privativa e churrasqueira.',
            ],
        ];

        foreach ($imoveis as $imovel) {
            Imovel::query()->create($imovel);
        }

        $this->command?->info('Imoveis criados: '.count($imoveis));
    }
}
