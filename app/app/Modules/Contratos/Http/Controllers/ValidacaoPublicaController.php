<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Http\Controllers;

use App\Modules\Contratos\Models\Contrato;
use App\Shared\Validation\Rules\ValidaCpf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ValidacaoPublicaController
{
    public function __invoke(Request $request): JsonResponse
    {
        /** @var array{cpf: string, numero: string} $dados */
        $dados = $request->validate([
            'cpf' => ['required', 'string', 'max:14', 'regex:/^[0-9.\-]+$/D', new ValidaCpf],
            'numero' => ['required', 'string', 'max:30', 'regex:~^\d{4}/\d{4,20}$~D'],
        ]);
        $cpf = preg_replace('/\D/', '', $dados['cpf']);
        $contrato = Contrato::query()->where('numero', $dados['numero'])->first();
        /** @var array{inquilino?: array{cpf?: string}, fiadores?: list<array{cpf?: string}>}|null $snapshot */
        $snapshot = $contrato?->snapshot;
        $documentos = is_array($snapshot) ? [$snapshot['inquilino']['cpf'] ?? ''] : [];
        foreach ($snapshot['fiadores'] ?? [] as $fiador) {
            $documentos[] = $fiador['cpf'] ?? '';
        }
        $parteAutorizada = false;
        foreach ($documentos as $documento) {
            if ($documento !== '' && hash_equals(preg_replace('/\D/', '', $documento) ?? '', $cpf ?? '')) {
                $parteAutorizada = true;
            }
        }
        /**
         * Legados sem snapshot não permitem confirmar a identidade histórica.
         */
        $reconhecido = $parteAutorizada
            && in_array($contrato?->status, ['Validado', 'Vigente', 'Encerrado', 'Cancelado'], true);

        return response()->json(['dados' => [
            'reconhecido' => $reconhecido,
            'situacao' => $reconhecido ? $contrato?->status : null,
            'mensagem' => $reconhecido
                ? 'Contrato reconhecido pelo sistema. Esta consulta não verifica o arquivo nem a blockchain.'
                : 'Não foi possível confirmar o contrato com os dados informados.',
        ]])->header('Cache-Control', 'no-store, private');
    }
}
