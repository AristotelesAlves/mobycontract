<?php

declare(strict_types=1);

namespace App\Shared\Application\Exceptions;

use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class ConflitoDeUnicidadeException extends ApiException
{
    /**
     * @param  array<string, string>  $campos
     * @param  array<string, mixed>  $contexto
     */
    public function __construct(
        string $mensagem,
        array $campos = [],
        array $contexto = [],
        ?Throwable $anterior = null,
    ) {
        parent::__construct('CONFLICT', $mensagem, $campos, $contexto, $anterior);
    }

    public static function cpfJaCadastrado(string $cpf, int $inquilinoId): self
    {
        return new self(
            'Já existe locatário com este CPF.',
            ['cpf' => 'Este CPF já está cadastrado.'],
            ['inquilinoId' => $inquilinoId],
        );
    }

    public static function emailJaCadastrado(string $email): self
    {
        return new self(
            'Já existe usuário com este e-mail.',
            ['email' => 'Este e-mail já está cadastrado.'],
            [],
        );
    }

    public function status(): int
    {
        return Response::HTTP_CONFLICT;
    }
}
