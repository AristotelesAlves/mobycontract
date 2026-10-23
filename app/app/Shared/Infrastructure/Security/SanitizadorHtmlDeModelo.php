<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Security;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Remove conteúdo executável do HTML produzido pelo editor de modelos.
 *
 * O conteúdo continua sendo HTML para a composição do documento, mas a
 * política é deliberadamente pequena: não há scripts, mídia remota, eventos
 * HTML ou estilos arbitrários.
 */
final class SanitizadorHtmlDeModelo
{
    /** @var list<string> */
    private const ELEMENTOS_PERMITIDOS = [
        'p', 'div', 'span', 'br', 'hr', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'strong', 'b', 'em', 'i', 'u', 's', 'strike', 'code', 'pre',
        'blockquote', 'ul', 'ol', 'li', 'label', 'input',
    ];

    public function sanitizar(string $html): string
    {
        if ($html === '') {
            return '';
        }

        $documento = new DOMDocument('1.0', 'UTF-8');
        $anterior = libxml_use_internal_errors(true);
        $documento->loadHTML(
            '<?xml encoding="UTF-8"><div id="conteudo-modelo">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD,
        );
        libxml_clear_errors();
        libxml_use_internal_errors($anterior);

        $raiz = $documento->getElementById('conteudo-modelo');
        if (! $raiz instanceof DOMElement) {
            return '';
        }

        $this->sanitizarFilhos($raiz);

        $resultado = '';
        foreach ($raiz->childNodes as $filho) {
            $resultado .= $documento->saveHTML($filho);
        }

        return $resultado;
    }

    private function sanitizarFilhos(DOMElement $elemento): void
    {
        /** @var list<DOMNode> $filhos */
        $filhos = iterator_to_array($elemento->childNodes);

        foreach ($filhos as $filho) {
            if (! $filho instanceof DOMElement) {
                continue;
            }

            if (! in_array(strtolower($filho->tagName), self::ELEMENTOS_PERMITIDOS, true)) {
                $this->substituirPorFilhos($filho);

                continue;
            }

            $this->sanitizarAtributos($filho);
            $this->sanitizarFilhos($filho);
        }
    }

    private function sanitizarAtributos(DOMElement $elemento): void
    {
        /** @var list<string> $nomes */
        $nomes = [];
        foreach ($elemento->attributes as $atributo) {
            $nomes[] = $atributo->name;
        }

        foreach ($nomes as $nome) {
            $valor = $elemento->getAttribute($nome);
            $permitido = match ($nome) {
                'style' => $this->estiloPermitido($valor),
                'data-type' => in_array($valor, ['taskList', 'taskItem'], true),
                'data-checked' => in_array($valor, ['true', 'false'], true),
                'type' => strtolower($elemento->tagName) === 'input' && $valor === 'checkbox',
                'checked', 'disabled' => strtolower($elemento->tagName) === 'input' && $valor === $nome,
                default => false,
            };

            if (! $permitido) {
                $elemento->removeAttribute($nome);
            }
        }
    }

    private function estiloPermitido(string $valor): bool
    {
        return (bool) preg_match(
            '/^\s*(?:text-align\s*:\s*(?:left|right|center|justify)|line-height\s*:\s*(?:1|1\.25|1\.5|1\.75|2))\s*;?\s*$/iD',
            $valor,
        );
    }

    private function substituirPorFilhos(DOMElement $elemento): void
    {
        $pai = $elemento->parentNode;
        if (! $pai instanceof DOMNode) {
            return;
        }

        while ($elemento->firstChild !== null) {
            $pai->insertBefore($elemento->firstChild, $elemento);
        }
        $pai->removeChild($elemento);
    }
}
