<?php

declare(strict_types=1);

namespace App\Modules\Usuarios\Http\Requests;

use App\Shared\Http\Requests\RequisicaoBase;

final class RefreshTokenRequest extends RequisicaoBase
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
        return [
            'refresh_token' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'refresh_token.required' => 'Informe o refresh token.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'refresh_token' => 'refresh token',
        ];
    }
}
