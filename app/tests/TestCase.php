<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use PDO;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // O limitador de tentativas usa o cache da aplicação. Sem limpar o
        // store entre casos, uma consulta pública de um teste afeta o próximo.
        Cache::flush();
    }

    /**
     * Camada extra de protecao: garante a conexao de teste antes do boot do
     * app. O repositorio de env do Laravel le $_SERVER primeiro (phpdotenv),
     * e o <env force> do phpunit.xml nao atualiza $_SERVER — sem isto, dentro
     * do container os testes usariam o banco real.
     */
    public function createApplication()
    {
        $_SERVER['DB_CONNECTION'] = 'pgsql_teste';
        $_ENV['DB_CONNECTION'] = 'pgsql_teste';
        putenv('DB_CONNECTION=pgsql_teste');
        $_SERVER['CACHE_STORE'] = 'array';
        $_ENV['CACHE_STORE'] = 'array';
        putenv('CACHE_STORE=array');

        $app = parent::createApplication();
        $config = $app->make('config');
        $app->detectEnvironment(static fn (): string => 'testing');
        $config->set('app.env', 'testing');
        $config->set('cache.default', 'array');
        $teste = $config->get('database.connections.pgsql_teste');
        $principal = $config->get('database.connections.pgsql');
        if (! is_array($teste) || ! is_string($teste['database'] ?? null)
            || ! str_contains($teste['database'], 'teste')
            || $teste['database'] === ($principal['database'] ?? null)) {
            throw new \LogicException('Testes bloqueados: configure um banco de testes separado do banco principal.');
        }

        // A configuração em cache tem precedência sobre as variáveis de ambiente.
        $app->make('db')->setDefaultConnection('pgsql_teste');

        return $app;
    }

    /**
     * Aponta cada processo do Pest paralelo para um banco de teste próprio,
     * criando-o se necessário, para que migrate:fresh não colida entre
     * processos que rodam arquivos Feature de banco em paralelo.
     */
    protected function setUpTraits()
    {
        $this->apontarParaBancoDoProcesso();

        return parent::setUpTraits();
    }

    private function apontarParaBancoDoProcesso(): void
    {
        $token = getenv('TEST_TOKEN');

        if ($token === false || ! ctype_digit($token)) {
            return;
        }

        $config = config('database.connections.pgsql_teste');

        if (! is_array($config)) {
            return;
        }

        $base = is_string($config['database'] ?? null) ? $config['database'] : 'mobycontract_teste';
        $destino = $base.'_'.$token;

        $this->criarBancoSeAusente($destino, $config);

        config(['database.connections.pgsql_teste.database' => $destino]);

        DB::purge('pgsql_teste');
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function criarBancoSeAusente(string $nome, array $config): void
    {
        $host = is_string($config['host'] ?? null) ? $config['host'] : 'db';
        $porta = is_string($config['port'] ?? null) ? $config['port'] : '5432';
        $usuario = is_string($config['username'] ?? null) ? $config['username'] : 'mobycontract';
        $senha = is_string($config['password'] ?? null) ? $config['password'] : '';

        $pdo = new PDO(
            sprintf('pgsql:host=%s;port=%s;dbname=postgres', $host, $porta),
            $usuario,
            $senha,
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $consulta = $pdo->prepare('SELECT 1 FROM pg_database WHERE datname = :nome');
        $consulta->execute(['nome' => $nome]);

        if ($consulta->fetchColumn() === false) {
            $pdo->exec('CREATE DATABASE "'.$nome.'"');
        }
    }

    protected function pdfDeTeste(): string
    {
        $conteudo = 'BT /F1 12 Tf 20 100 Td (Documento de teste TCC) Tj ET';
        $objetos = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 200 200] /Resources << /Font << /F1 5 0 R >> >> /Contents 4 0 R >>',
            '<< /Length '.strlen($conteudo)." >>\nstream\n".$conteudo."\nendstream",
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];
        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objetos as $indice => $objeto) {
            $offsets[] = strlen($pdf);
            $pdf .= ($indice + 1)." 0 obj\n".$objeto."\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 6\n0000000000 65535 f \n";
        foreach (array_slice($offsets, 1) as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        return $pdf."trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n".$xref."\n%%EOF\n";
    }
}
