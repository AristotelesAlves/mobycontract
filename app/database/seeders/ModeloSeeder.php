<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Modelos\Models\Modelo;
use Illuminate\Database\Seeder;

final class ModeloSeeder extends Seeder
{
    public function run(): void
    {
        $modelos = [
            [
                'codigo' => 1001,
                'titulo' => 'Contrato de Locação Residencial Padrão',
                'tipo' => 'Contrato',
                'status' => true,
                'versao' => 1,
                'conteudo_html' => '<h1>Contrato de Locação</h1><p>Entre {{ locador.nome }} e {{ locatario.nome }}.</p>',
                'garantia' => 'Sem Garantia',
                'margens' => ['superior' => 2.5, 'inferior' => 2.5, 'esquerda' => 2.0, 'direita' => 2.0],
                'tipografia' => ['fonte' => 'Arial', 'tamanho' => 12],
            ],
            [
                'codigo' => 1002,
                'titulo' => 'Contrato de Locação Comercial',
                'tipo' => 'Contrato',
                'status' => true,
                'versao' => 1,
                'conteudo_html' => '<h1>Contrato Comercial</h1><p>Locação de imóvel comercial entre as partes.</p>',
                'garantia' => 'Caução',
                'margens' => ['superior' => 2.0, 'inferior' => 2.0, 'esquerda' => 2.0, 'direita' => 2.0],
                'tipografia' => ['fonte' => 'Times New Roman', 'tamanho' => 11],
            ],
            [
                'codigo' => 2001,
                'titulo' => 'Aditivo de Reajuste Anual',
                'tipo' => 'Aditivo',
                'status' => true,
                'versao' => 1,
                'conteudo_html' => '<h1>Aditivo Contratual</h1><p>Reajuste anual conforme cláusula X.</p>',
                'garantia' => null,
                'margens' => ['superior' => 2.5, 'inferior' => 2.5, 'esquerda' => 2.0, 'direita' => 2.0],
                'tipografia' => ['fonte' => 'Arial', 'tamanho' => 12],
            ],
        ];

        foreach ($modelos as $modelo) {
            Modelo::query()->create($modelo);
        }

        $this->command?->info('Modelos criados: '.count($modelos));
    }
}
