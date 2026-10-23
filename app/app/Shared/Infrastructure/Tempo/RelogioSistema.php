<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Tempo;

use App\Shared\Contracts\Relogio;
use Carbon\CarbonImmutable;

final class RelogioSistema implements Relogio
{
    public function hoje(): string
    {
        return CarbonImmutable::now()->toDateString();
    }

    public function instante(): CarbonImmutable
    {
        return CarbonImmutable::now();
    }
}
