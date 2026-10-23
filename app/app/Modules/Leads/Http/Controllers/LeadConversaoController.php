<?php

declare(strict_types=1);

namespace App\Modules\Leads\Http\Controllers;

use App\Modules\Inquilinos\Http\Resources\InquilinoResource;
use App\Modules\Leads\Contracts\LeadServiceInterface;
use App\Modules\Leads\Http\Requests\ConverterLeadRequest;
use App\Shared\Application\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Throwable;

final class LeadConversaoController extends ApiController
{
    public function __construct(
        private readonly LeadServiceInterface $leads,
    ) {}

    public function converter(ConverterLeadRequest $request, int $id): JsonResponse
    {
        try {
            $dados = $request->toDto();
            $locatario = $this->leads->converter($id, $dados, $this->identificadorDoAutor());

            return $this->resposta($request, new InquilinoResource($locatario), HttpResponse::HTTP_CREATED);
        } catch (Throwable $erro) {
            return $this->falha($erro, 'leads.converter');
        }
    }

    private function identificadorDoAutor(): int
    {
        return (int) auth()->id();
    }
}
