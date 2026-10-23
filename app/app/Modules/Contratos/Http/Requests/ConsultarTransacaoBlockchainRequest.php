<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Http\Requests;

use App\Shared\Http\Requests\RequisicaoBase;
use App\Shared\Validation\Rules\HashDeTransacao;

final class ConsultarTransacaoBlockchainRequest extends RequisicaoBase
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'hashTransacao' => ['required', new HashDeTransacao],
        ];
    }
}
