<?php

declare(strict_types=1);

return [

    'default' => env('DB_CONNECTION', 'pgsql'),

    'connections' => [

        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', 'db'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'mobycontract'),
            'username' => env('DB_USERNAME', 'mobycontract'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => env('DB_SSLMODE', 'prefer'),
        ],

        'pgsql_teste' => [
            'driver' => 'pgsql',
            'url' => null,
            'host' => env('DB_HOST', 'db'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE_TESTE', 'mobycontract_teste'),
            'username' => env('DB_USERNAME', 'mobycontract'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => env('DB_SSLMODE', 'prefer'),
        ],

    ],

    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],

    /*
    | Cache e fila são instâncias distintas de Redis (P7 e R-INF-3.4). A política de
    |_evicção_ é configurada no container, não aqui: `allkeys-lru` em `cache` e
    | `noeviction` em `fila`. Aqui só mora endereço, porta e prefixo de chave.
    */
    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', 'mobycontract_cache_'),
            'persistent' => env('REDIS_PERSISTENT', false),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_CACHE_HOST', '127.0.0.1'),
            'username' => env('REDIS_CACHE_USERNAME'),
            'password' => env('REDIS_CACHE_PASSWORD'),
            'port' => env('REDIS_CACHE_PORT', 6379),
            'database' => env('REDIS_CACHE_DB', 0),
            'read_timeout' => 60,
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_CACHE_HOST', '127.0.0.1'),
            'username' => env('REDIS_CACHE_USERNAME'),
            'password' => env('REDIS_CACHE_PASSWORD'),
            'port' => env('REDIS_CACHE_PORT', 6379),
            'database' => env('REDIS_CACHE_DB', 0),
            'read_timeout' => 60,
        ],

        'fila' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_QUEUE_HOST', '127.0.0.1'),
            'username' => env('REDIS_QUEUE_USERNAME'),
            'password' => env('REDIS_QUEUE_PASSWORD'),
            'port' => env('REDIS_QUEUE_PORT', 6379),
            'database' => env('REDIS_QUEUE_DB', 0),
            'read_timeout' => 60,
        ],

    ],

];
