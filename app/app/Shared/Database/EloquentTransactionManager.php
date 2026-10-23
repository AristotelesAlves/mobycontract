<?php

declare(strict_types=1);

namespace App\Shared\Database;

use App\Shared\Contracts\TransactionManager;
use Closure;
use Illuminate\Database\DatabaseManager;

final readonly class EloquentTransactionManager implements TransactionManager
{
    public function __construct(
        private DatabaseManager $banco,
    ) {}

    public function executar(Closure $operacao): mixed
    {
        return $this->banco->transaction($operacao);
    }
}
