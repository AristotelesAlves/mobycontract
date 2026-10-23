<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Contracts;

use App\Modules\Contratos\DTOs\Output\DividaResult;

interface DividaRepositoryInterface
{
    /**
     * @return list<DividaResult>
     */
    public function listarPorContrato(int $contratoId): array;

    public function buscarPorId(int $contratoId, int $dividaId): ?DividaResult;

    /**
     * @param  array<string, mixed>  $atributos
     */
    public function criar(int $contratoId, array $atributos): DividaResult;

    public function quitar(int $contratoId, int $dividaId, string $dataQuitacao): ?DividaResult;

    /**
     * @return list<DividaResult>
     */
    public function listarEmAbertoPorContrato(int $contratoId): array;
}
