<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Shared\Validation\Rules\SenhaForte;
use App\Shared\Validation\Rules\ValidaCpf;

it('aceita senha com oito caracteres, letra e numero', function (): void {
    $falhas = [];
    (new SenhaForte)->validate('senha', 'abc12345', function (string $mensagem) use (&$falhas): void {
        $falhas[] = $mensagem;
    });

    expect($falhas)->toBe([]);
});

it('recusa senha curta', function (): void {
    $falhas = [];
    (new SenhaForte)->validate('senha', 'ab1', function (string $mensagem) use (&$falhas): void {
        $falhas[] = $mensagem;
    });

    expect($falhas)->toHaveCount(1);
});

it('recusa senha sem numero', function (): void {
    $falhas = [];
    (new SenhaForte)->validate('senha', 'abcdefgh', function (string $mensagem) use (&$falhas): void {
        $falhas[] = $mensagem;
    });

    expect($falhas)->toHaveCount(1);
});

it('recusa senha sem letra', function (): void {
    $falhas = [];
    (new SenhaForte)->validate('senha', '12345678', function (string $mensagem) use (&$falhas): void {
        $falhas[] = $mensagem;
    });

    expect($falhas)->toHaveCount(1);
});

it('aceita cpf valido com e sem mascara', function (string $cpf): void {
    $falhas = [];
    (new ValidaCpf)->validate('cpf', $cpf, function (string $mensagem) use (&$falhas): void {
        $falhas[] = $mensagem;
    });

    expect($falhas)->toBe([]);
})->with([
    '529.982.247-25',
    '52998224725',
]);

it('recusa cpf invalido', function (string $cpf): void {
    $falhas = [];
    (new ValidaCpf)->validate('cpf', $cpf, function (string $mensagem) use (&$falhas): void {
        $falhas[] = $mensagem;
    });

    expect($falhas)->toHaveCount(1);
})->with([
    '111.111.111-11',
    '529.982.247-24',
    '123',
    'abcdefghijk',
]);
