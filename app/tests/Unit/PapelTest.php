<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Shared\Domain\Enums\Papel;

it('da acesso total ao administrador', function (): void {
    expect(Papel::Administrador->abilities())->toBe(['*'])
        ->and(Papel::Administrador->permite('excluir'))->toBeTrue()
        ->and(Papel::Administrador->podeExcluir())->toBeTrue()
        ->and(Papel::Administrador->podeValidarContrato())->toBeTrue();
});

it('restringe o operador as abilities do dia a dia', function (): void {
    expect(Papel::Operador->abilities())->toBe(['listar', 'criar', 'alterar', 'consultar'])
        ->and(Papel::Operador->permite('criar'))->toBeTrue()
        ->and(Papel::Operador->permite('listar'))->toBeTrue();
});

it('nega ao operador as abilities de administrador', function (string $habilidade): void {
    expect(Papel::Operador->permite($habilidade))->toBeFalse();
})->with([
    'excluir',
    'validar',
    'registrarBlockchain',
    'estornar',
    'habilidadeInexistente',
]);

it('mantem os valores de string esperados pelo banco e pelo token', function (): void {
    expect(Papel::Administrador->value)->toBe('Administrador')
        ->and(Papel::Operador->value)->toBe('Operador')
        ->and(Papel::from('Operador'))->toBe(Papel::Operador);
});
