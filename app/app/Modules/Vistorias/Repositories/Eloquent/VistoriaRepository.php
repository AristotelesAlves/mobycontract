<?php

declare(strict_types=1);

namespace App\Modules\Vistorias\Repositories\Eloquent;

use App\Modules\Vistorias\Contracts\VistoriaRepositoryInterface;
use App\Modules\Vistorias\Domain\Enums\StatusVistoria;
use App\Modules\Vistorias\DTOs\Input\ListarVistoriasFiltros;
use App\Modules\Vistorias\DTOs\Output\VistoriaFotoResult;
use App\Modules\Vistorias\DTOs\Output\VistoriaResult;
use App\Modules\Vistorias\Models\Vistoria;
use App\Modules\Vistorias\Models\VistoriaFoto;
use App\Modules\Vistorias\Models\VistoriaItem;
use App\Shared\Application\DTOs\PaginatedResult;

final readonly class VistoriaRepository implements VistoriaRepositoryInterface
{
    private const array RELACOES_EXIBICAO = ['itens', 'fotos', 'contrato.imovel'];

    public function __construct(
        private Vistoria $modelo,
    ) {}

    /**
     * @return PaginatedResult<VistoriaResult>
     */
    public function paginar(ListarVistoriasFiltros $filtros): PaginatedResult
    {
        $consulta = $this->modelo->newQuery()
            ->with(self::RELACOES_EXIBICAO)
            ->where('contrato_id', $filtros->contratoId);

        if ($filtros->status instanceof StatusVistoria) {
            $consulta->where('status', $filtros->status->value);
        }

        $pagina = $consulta
            ->orderBy($filtros->colunaOrdenacao, $filtros->direcaoOrdenacao)
            ->paginate($filtros->tamanhoPagina, ['*'], 'pagina', $filtros->pagina);

        /** @var PaginatedResult<VistoriaResult> $resultado */
        $resultado = PaginatedResult::fromPaginator(
            $pagina,
            static fn (Vistoria $vistoria): VistoriaResult => VistoriaResult::fromModel($vistoria),
        );

        return $resultado;
    }

    public function buscarPorId(int $vistoriaId): ?VistoriaResult
    {
        $vistoria = $this->modelo->newQuery()
            ->with(self::RELACOES_EXIBICAO)
            ->find($vistoriaId);

        return $vistoria === null ? null : VistoriaResult::fromModel($vistoria);
    }

    /**
     * @param  array<string, mixed>  $atributos
     */
    public function criar(array $atributos): VistoriaResult
    {
        $vistoria = $this->modelo->newQuery()->create($atributos);

        return VistoriaResult::fromModel($vistoria->refresh()->loadMissing(self::RELACOES_EXIBICAO));
    }

    /**
     * @param  array<string, mixed>  $atributos
     */
    public function atualizar(int $vistoriaId, array $atributos): void
    {
        $vistoria = $this->modelo->newQuery()->findOrFail($vistoriaId);
        $vistoria->fill($atributos)->save();
    }

    public function excluir(int $vistoriaId): void
    {
        $this->modelo->newQuery()->whereKey($vistoriaId)->delete();
    }

    public function proximoNumero(int $contratoId, string $ano): string
    {
        $maior = 0;
        $numeros = $this->modelo->newQuery()->where('contrato_id', $contratoId)->pluck('numero');

        foreach ($numeros as $numero) {
            if (is_string($numero) && preg_match('/VST (\d+)\//', $numero, $captura) === 1) {
                $maior = max($maior, (int) $captura[1]);
            }
        }

        return sprintf('VST %03d/%s', $maior + 1, $ano);
    }

    public function buscarPorChaveAcesso(string $chaveAcesso): ?VistoriaResult
    {
        $vistoria = $this->modelo->newQuery()
            ->with(self::RELACOES_EXIBICAO)
            ->where('chave_acesso', $chaveAcesso)
            ->first();

        return $vistoria === null ? null : VistoriaResult::fromModel($vistoria);
    }

    /**
     * @param  list<array{ambiente: string, item: string, estado: string, observacao: string|null}>  $itens
     */
    public function substituirItens(int $vistoriaId, array $itens): void
    {
        VistoriaItem::query()->where('vistoria_id', $vistoriaId)->delete();

        foreach ($itens as $atributos) {
            VistoriaItem::query()->create([...$atributos, 'vistoria_id' => $vistoriaId]);
        }
    }

    /**
     * @param  array<string, mixed>  $atributos
     */
    public function criarFoto(int $vistoriaId, array $atributos): VistoriaFotoResult
    {
        $foto = VistoriaFoto::query()->create([...$atributos, 'vistoria_id' => $vistoriaId]);

        return new VistoriaFotoResult(
            id: (int) $foto->id,
            vistoriaId: (int) $foto->vistoria_id,
            descricao: $foto->descricao === null ? null : (string) $foto->descricao,
            nomeAnexo: (string) $foto->nome_anexo,
            anexo: $foto->anexo === null ? null : (string) $foto->anexo,
        );
    }

    public function excluirFoto(int $vistoriaId, int $fotoId): void
    {
        VistoriaFoto::query()
            ->where('vistoria_id', $vistoriaId)
            ->whereKey($fotoId)
            ->delete();
    }
}
