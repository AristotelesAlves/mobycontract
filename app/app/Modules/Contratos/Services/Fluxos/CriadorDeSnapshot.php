<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Services\Fluxos;

use App\Modules\Contratos\DTOs\Input\CriarContratoData;
use App\Modules\Contratos\DTOs\Input\FiadorData;
use App\Modules\Imoveis\DTOs\Output\ImovelResult;
use App\Modules\Inquilinos\DTOs\Output\InquilinoResult;
use App\Modules\Modelos\DTOs\Output\ModeloResult;

final class CriadorDeSnapshot
{
    /**
     * @return array<string, mixed>
     */
    public function criar(ImovelResult $imovel, InquilinoResult $inquilino, ModeloResult $modelo, CriarContratoData $dados, float $multaRescisao): array
    {
        return [
            'versao' => 1,
            'capturadoEm' => date(DATE_ATOM),
            'imovel' => [
                'titulo' => $imovel->titulo, 'endereco' => $imovel->endereco,
                'cidade' => $imovel->cidade, 'matricula' => $imovel->matricula,
                'valor_aluguel' => $imovel->valorAluguel, 'valor_iptu' => $imovel->valorIptu,
                'valor_condominio' => $imovel->valorCondominio,
                'area' => $imovel->area, 'quartos' => $imovel->quartos, 'banheiros' => $imovel->banheiros, 'vagasGaragem' => $imovel->vagasGaragem,
            ],
            'inquilino' => [
                'nome' => $inquilino->nome, 'cpf' => $inquilino->cpf, 'rg' => $inquilino->rg,
                'email' => $inquilino->email, 'telefone' => $inquilino->telefone,
                'endereco' => $inquilino->endereco, 'estado_civil' => $inquilino->estadoCivil, 'renda' => $inquilino->renda,
            ],
            'locador' => ['nome' => $imovel->nomeProprietario ?: $dados->nomeLocador],
            'fiadores' => $modelo->garantia === 'Fiador' ? array_map(static fn (FiadorData $fiador): array => [
                'nome' => $fiador->nome, 'cpf' => $fiador->cpf, 'telefone' => $fiador->telefone,
            ], $dados->fiadores) : [],
            'modelo' => [
                'titulo' => $modelo->titulo, 'versao' => $modelo->versao,
                'conteudo_html' => $modelo->conteudoHtml, 'margens' => $modelo->margens,
                'tipografia' => $modelo->tipografia,
            ],
            'condicoesIniciais' => [
                'valorMensal' => $imovel->valorAluguel, 'descontoMensal' => $dados->descontoMensal,
                'garantia' => $modelo->garantia ?? 'Sem garantia',
                'valorCaucao' => $modelo->garantia === 'Caução' ? $dados->valorCaucao : null,
                'dataInicio' => $dados->dataInicio, 'dataFim' => $dados->dataFim,
                'prazoVigencia' => $dados->prazoVigencia->value, 'indiceReajuste' => $dados->indiceReajuste->value,
                'multaPorAtraso' => $dados->multaPorAtraso, 'multaRescisao' => $multaRescisao,
                'diaVencimentoParcela' => $dados->diaVencimentoParcela, 'observacoes' => $dados->observacoes,
            ],
        ];
    }
}
