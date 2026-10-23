<?php

declare(strict_types=1);

namespace App\Shared\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Base de todo FormRequest da API.
 *
 * Implementa R-9.9: uma Rule dependente de repository é declarada pelo nome
 * da classe e resolvida pelo container, preservando a injeção de dependência
 * sem recorrer a `new` dentro de rules().
 */
abstract class RequisicaoBase extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    protected function validationRules()
    {
        /** @var array<string, mixed> $regras */
        $regras = parent::validationRules();

        return $this->resolverRegras($regras);
    }

    /**
     * @param  array<string, mixed>  $regras
     * @return array<string, mixed>
     */
    private function resolverRegras(array $regras): array
    {
        foreach ($regras as $campo => $conjunto) {
            $regras[$campo] = $this->resolverConjunto($conjunto);
        }

        return $regras;
    }

    private function resolverConjunto(mixed $conjunto): mixed
    {
        if (is_array($conjunto)) {
            return array_map($this->resolverRegra(...), $conjunto);
        }

        return $this->resolverRegra($conjunto);
    }

    private function resolverRegra(mixed $regra): mixed
    {
        if (is_string($regra) && class_exists($regra) && is_a($regra, ValidationRule::class, true)) {
            return $this->container->make($regra);
        }

        return $regra;
    }
}
