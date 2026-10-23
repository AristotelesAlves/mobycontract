<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Logging;

use App\Shared\Application\Exceptions\ApiException;
use App\Shared\Contracts\RegistradorDeErrosApi;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Psr\Log\LoggerInterface;
use Throwable;

final readonly class RegistradorDeErrosComLog implements RegistradorDeErrosApi
{
    public function __construct(
        private LoggerInterface $log,
    ) {}

    public function registrarFalhaDeDominio(Throwable $erro, string $contexto): void
    {
        if (! $erro instanceof ApiException) {
            $this->registrarFalhaInesperada($erro, $contexto);

            return;
        }

        $this->log->warning('api.falha_de_dominio', [
            'contexto' => $contexto,
            'codigo' => $erro->codigo(),
            'requisicao_id' => $this->requisicaoId(),
            'usuario_id' => $this->usuarioId(),
        ]);
    }

    public function registrarFalhaInesperada(Throwable $erro, string $contexto): void
    {
        $this->log->error('api.falha_inesperada', [
            'contexto' => $contexto,
            'requisicao_id' => $this->requisicaoId(),
            'usuario_id' => $this->usuarioId(),
            'excecao' => $erro::class,
            'mensagem' => $erro->getMessage(),
            'arquivo' => $erro->getFile(),
            'linha' => $erro->getLine(),
        ]);
    }

    private function requisicaoId(): ?string
    {
        if (! app()->bound('request')) {
            return null;
        }

        $identificador = app(Request::class)->header('X-Request-Id');

        return is_string($identificador) ? $identificador : null;
    }

    private function usuarioId(): int|string|null
    {
        $usuario = $this->usuario();

        if (! $usuario instanceof Authenticatable) {
            return null;
        }

        $identificador = $usuario->getAuthIdentifier();

        return is_int($identificador) || is_string($identificador) ? $identificador : null;
    }

    private function usuario(): ?Authenticatable
    {
        if (! app()->bound('request')) {
            return null;
        }

        return app(Request::class)->user();
    }
}
