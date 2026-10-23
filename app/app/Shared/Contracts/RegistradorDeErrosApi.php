<?php

declare(strict_types=1);

namespace App\Shared\Contracts;

interface RegistradorDeErrosApi
{
    public function registrarFalhaDeDominio(\Throwable $erro, string $contexto): void;

    public function registrarFalhaInesperada(\Throwable $erro, string $contexto): void;
}
