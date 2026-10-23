<?php

declare(strict_types=1);

namespace App\Modules\Vistorias\Http\Requests;

use App\Shared\Http\Requests\RequisicaoBase;

final class ExcluirVistoriaRequest extends RequisicaoBase
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
