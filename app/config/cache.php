<?php

declare(strict_types=1);

return [

    'default' => env('CACHE_STORE', 'redis'),

    'stores' => [

        'array' => [
            'driver' => 'array',
            'serialize' => false,
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'cache',
            'lock_connection' => 'cache',
        ],

        'fila' => [
            'driver' => 'redis',
            'connection' => 'fila',
            'lock_connection' => 'fila',
        ],

    ],

    'prefix' => env('CACHE_PREFIX', 'mobycontract'),

    /*
    | Duração de vida de cada leitura cacheada, em segundos (R-INF-9.2).
    | Toda duração vem daqui; nenhum número é escrito na chamada de cache (R-INF-10.18).
    */
    'duracoes' => [
        'painel_resumo' => 60,
        'totais_por_status' => 60,
        'situacao_do_imovel' => 300,
        'indicadores_do_inquilino' => 120,
        'custo_mensal_do_imovel' => 600,
        'listagem_sem_texto' => 120,
        'perfil_do_usuario' => 300,
        'variaveis_do_modelo' => 3600,
        'endereco_por_cep' => 86400,
        'negativo' => 15,
    ],

];
