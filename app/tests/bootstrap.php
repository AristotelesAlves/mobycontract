<?php

declare(strict_types=1);

/**
 * Bootstrap dos testes.
 *
 * Por que este arquivo existe:
 *
 * Dentro do container, o docker-compose injeta variaveis de ambiente reais
 * (env_file: app/.env -> DB_CONNECTION=pgsql, DB_DATABASE=mobycontract). O
 * PHPUnit/Pest aplica o <env force> do phpunit.xml apenas em putenv/$_ENV —
 * nunca em $_SERVER. Como o repositorio de env do Laravel (phpdotenv) le
 * $_SERVER PRIMEIRO, a config do teste resolvia "pgsql" e o RefreshDatabase
 * (migrate:fresh) executava contra o banco REAL, apagando os dados.
 *
 * Este bootstrap corrige $_SERVER antes de qualquer boot da aplicacao.
 *
 * O bootstrap fica especificado no atributo <bootstrap> do phpunit.xml e
 * precisa carregar o autoloader manualmente, pois substitui o padrao.
 */
$_SERVER['DB_CONNECTION'] = 'pgsql_teste';
$_ENV['DB_CONNECTION'] = 'pgsql_teste';
putenv('DB_CONNECTION=pgsql_teste');

require __DIR__.'/../vendor/autoload.php';
