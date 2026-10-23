<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Http\Controllers;

use App\Modules\Contratos\Models\Contrato;
use App\Modules\Contratos\Models\ContratoAditivo;
use App\Shared\Contracts\ArmazenadorDeArquivo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\HeaderUtils;

final readonly class DownloadDocumentoController
{
    public function __construct(private ArmazenadorDeArquivo $arquivos) {}

    public function autorizarExportacao(int $id): JsonResponse
    {
        Contrato::query()->findOrFail($id);

        return response()->json(['dados' => [
            'autorizacao' => URL::temporarySignedRoute(
                'contratos.exportacao-pdf', now()->addMinute(), ['id' => $id], absolute: false,
            ),
        ]])->header('Cache-Control', 'private, no-store');
    }

    public function verificarExportacao(int $id): Response
    {
        Contrato::query()->findOrFail($id);

        return response('', 204)->header('Cache-Control', 'private, no-store');
    }

    public function contrato(int $id): Response
    {
        $contrato = Contrato::query()->findOrFail($id);

        return $this->responder($contrato->anexo_contrato, $contrato->anexo_contrato_nome);
    }

    public function garantia(int $id): Response
    {
        $contrato = Contrato::query()->findOrFail($id);

        return $this->responder($contrato->comprovante_garantia, $contrato->comprovante_garantia_nome);
    }

    public function aditivo(int $contratoId, int $aditivoId): Response
    {
        $aditivo = ContratoAditivo::query()->where('contrato_id', $contratoId)->findOrFail($aditivoId);

        return $this->responder(is_string($aditivo->getAttribute('anexo')) ? $aditivo->getAttribute('anexo') : null, $aditivo->anexo_nome);
    }

    private function responder(?string $caminho, ?string $nome): Response
    {
        abort_if($caminho === null || $caminho === '', 404, 'Arquivo não disponível.');
        $conteudo = $this->arquivos->ler($caminho);
        abort_if($conteudo === '', 404, 'Arquivo não disponível.');
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($conteudo) ?: 'application/octet-stream';

        return response($conteudo, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => HeaderUtils::makeDisposition('attachment', basename(str_replace('\\', '/', $nome ?? 'documento')), 'documento'),
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
