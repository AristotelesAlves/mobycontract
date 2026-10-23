<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Http\Controllers;

use App\Modules\Contratos\Services\VerificadorDocumentoBlockchain;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final readonly class VerificarDocumentoController
{
    public function __construct(private VerificadorDocumentoBlockchain $verificador) {}

    public function __invoke(Request $request, int $id): JsonResponse
    {
        $request->validate(['arquivo' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:25600']]);
        $arquivo = $request->file('arquivo');
        $hash = '0x'.hash_file('sha256', $arquivo->getRealPath());

        return response()->json(['dados' => $this->verificador->verificar($id, $hash)])
            ->header('Cache-Control', 'private, no-store');
    }
}
