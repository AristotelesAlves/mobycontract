<?php

declare(strict_types=1);

namespace App\Modules\Modelos\Http\Resources;

use App\Modules\Modelos\DTOs\Output\ModeloResult;
use App\Modules\Modelos\DTOs\Output\VariavelConhecidaResult;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ModeloResult
 */
final class ModeloResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'podeAlterar' => ! $this->vinculadoAContrato,
            'vinculadoAContrato' => $this->vinculadoAContrato,
            'codigo' => $this->codigo,
            'titulo' => $this->titulo,
            'tipo' => $this->tipo,
            'ativo' => $this->ativo,
            'versao' => $this->versao,
            'conteudoHtml' => $this->conteudoHtml,
            'garantia' => $this->garantia,
            'margens' => $this->margens,
            'tipografia' => $this->tipografia,
            'criadoEm' => $this->criadoEm,
            'atualizadoEm' => $this->atualizadoEm,
            'caminhosConhecidos' => array_map(
                static fn (VariavelConhecidaResult $variavel): array => $variavel->paraArray(),
                $this->caminhosConhecidos,
            ),
        ];
    }
}
