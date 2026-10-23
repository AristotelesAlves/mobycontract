<?php

declare(strict_types=1);

namespace App\Shared\Contracts;

use Closure;

interface TransactionManager
{
    /**
     * @template T
     *
     * @param  Closure(): T  $operacao
     * @return T
     */
    public function executar(Closure $operacao): mixed;
}
