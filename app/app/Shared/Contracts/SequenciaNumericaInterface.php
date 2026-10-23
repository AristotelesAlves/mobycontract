<?php

declare(strict_types=1);

namespace App\Shared\Contracts;

interface SequenciaNumericaInterface
{
    public function proximo(string $nome): int;
}
