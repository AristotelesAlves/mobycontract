<?php

declare(strict_types=1);

namespace App\Modules\Vistorias\Contracts;

use App\Modules\Vistorias\DTOs\Input\AlterarStatusVistoriaData;
use App\Modules\Vistorias\DTOs\Input\AlterarVistoriaData;
use App\Modules\Vistorias\DTOs\Input\CriarVistoriaData;
use App\Modules\Vistorias\DTOs\Input\ListarVistoriasFiltros;
use App\Modules\Vistorias\DTOs\Output\VistoriaFotoResult;
use App\Modules\Vistorias\DTOs\Output\VistoriaResult;
use App\Shared\Application\DTOs\PaginatedResult;

interface VistoriaServiceInterface
{
    /**
     * @return PaginatedResult<VistoriaResult>
     */
    public function listar(ListarVistoriasFiltros $filtros): PaginatedResult;

    public function obter(int $vistoriaId): VistoriaResult;

    public function obterPorChaveDeAcesso(string $chaveAcesso): VistoriaResult;

    public function armazenar(CriarVistoriaData $dados): VistoriaResult;

    public function alterar(int $vistoriaId, AlterarVistoriaData $dados): VistoriaResult;

    public function alterarStatus(int $vistoriaId, AlterarStatusVistoriaData $dados): VistoriaResult;

    public function excluir(int $vistoriaId): void;

    public function registrarFoto(int $vistoriaId, ?string $nomeAnexo, ?string $anexo, ?string $descricao): VistoriaFotoResult;

    public function excluirFoto(int $vistoriaId, int $fotoId): void;
}
