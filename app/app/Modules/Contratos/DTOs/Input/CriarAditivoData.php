<?php

declare(strict_types=1);

namespace App\Modules\Contratos\DTOs\Input;

use App\Modules\Contratos\Domain\Enums\MotivoAditivo;
use App\Shared\Application\Support\DadosDeEntrada;

final readonly class CriarAditivoData
{
    /**
     * @param  list<MotivoAditivo>  $motivos
     * @param  array<string, mixed>  $campos
     */
    private function __construct(
        public int $contratoId,
        public string $titulo,
        public ?string $descricao,
        public ?string $data,
        public int $modeloId,
        public array $motivos,
        private array $campos,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(int $contratoId, array $validated): self
    {
        return new self(
            contratoId: $contratoId,
            titulo: DadosDeEntrada::texto($validated, 'titulo'),
            descricao: DadosDeEntrada::textoOpcional($validated, 'descricao'),
            data: DadosDeEntrada::textoOpcional($validated, 'data'),
            modeloId: DadosDeEntrada::inteiro($validated, 'modeloId'),
            motivos: self::motivosDe($validated),
            campos: $validated,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function camposDosMotivos(): array
    {
        return $this->campos;
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return list<MotivoAditivo>
     */
    private static function motivosDe(array $validated): array
    {
        $informados = $validated['motivos'] ?? null;

        if (! is_array($informados)) {
            return [];
        }

        $resultado = [];

        foreach ($informados as $motivo) {
            if (is_string($motivo) && MotivoAditivo::tryFrom($motivo) instanceof MotivoAditivo) {
                $resultado[] = MotivoAditivo::from($motivo);
            }
        }

        return $resultado;
    }
}
