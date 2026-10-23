<?php

declare(strict_types=1);

namespace App\Shared\Application\Exceptions;

use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class RecursoNaoEncontradoException extends ApiException
{
    /**
     * @param  array<string, mixed>  $contexto
     */
    public function __construct(
        string $mensagem = 'Recurso não encontrado.',
        array $contexto = [],
        ?Throwable $anterior = null,
    ) {
        parent::__construct('NOT_FOUND', $mensagem, [], $contexto, $anterior);
    }

    public static function contrato(int $contratoId): self
    {
        return new self('Contrato não encontrado.', ['contratoId' => $contratoId]);
    }

    public static function usuario(int $usuarioId): self
    {
        return new self('Usuário não encontrado.', ['usuarioId' => $usuarioId]);
    }

    public static function imovel(int $imovelId): self
    {
        return new self('Imóvel não encontrado.', ['imovelId' => $imovelId]);
    }

    public static function lead(int $leadId): self
    {
        return new self('Lead não encontrado.', ['leadId' => $leadId]);
    }

    public static function inquilino(int $inquilinoId): self
    {
        return new self('Locatário não encontrado.', ['inquilinoId' => $inquilinoId]);
    }

    public static function vistoria(int $vistoriaId): self
    {
        return new self('Vistoria não encontrada.', ['vistoriaId' => $vistoriaId]);
    }

    public static function modelo(int $modeloId): self
    {
        return new self('Documento modelo não encontrado.', ['modeloId' => $modeloId]);
    }

    public function status(): int
    {
        return Response::HTTP_NOT_FOUND;
    }
}
