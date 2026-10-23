<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\Http\Resources;

use App\Modules\Imoveis\DTOs\Output\ImovelResult;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ImovelResult
 */
final class ImovelResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'nomeProprietario' => $this->nomeProprietario,
            'tipo' => $this->tipo,
            'situacao' => $this->situacao,
            'cep' => $this->cep,
            'logradouro' => $this->logradouro,
            'numero' => $this->numero,
            'complemento' => $this->complemento,
            'bairro' => $this->bairro,
            'cidade' => $this->cidade,
            'estado' => $this->estado,
            'endereco' => $this->endereco,
            'area' => $this->area,
            'quartos' => $this->quartos,
            'banheiros' => $this->banheiros,
            'vagasGaragem' => $this->vagasGaragem,
            'valorAluguel' => $this->valorAluguel,
            'valorIptu' => $this->valorIptu,
            'valorCondominio' => $this->valorCondominio,
            'aguaInclusa' => $this->aguaInclusa,
            'energiaInclusa' => $this->energiaInclusa,
            'internetInclusa' => $this->internetInclusa,
            'gasIncluso' => $this->gasIncluso,
            'matricula' => $this->matricula,
            'descricao' => $this->descricao,
            'criadoEm' => $this->criadoEm,
            'atualizadoEm' => $this->atualizadoEm,
        ];
    }
}
