<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Contracts;

use App\Modules\Contratos\DTOs\Output\DividaResult;

interface DividaServiceInterface
{
    /**
     * @return list<DividaResult>
     */
    public function listar(int $contratoId): array;

    public function quitar(int $contratoId, int $dividaId, int $autorId): DividaResult;
}
