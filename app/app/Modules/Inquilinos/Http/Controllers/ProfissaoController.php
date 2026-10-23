<?php

declare(strict_types=1);

namespace App\Modules\Inquilinos\Http\Controllers;

use App\Modules\Inquilinos\Services\ProfissaoService;
use Illuminate\Http\JsonResponse;

final readonly class ProfissaoController
{
    public function __construct(private ProfissaoService $profissoes) {}

    public function __invoke(): JsonResponse
    {
        return response()->json(['dados' => $this->profissoes->listar()]);
    }
}
