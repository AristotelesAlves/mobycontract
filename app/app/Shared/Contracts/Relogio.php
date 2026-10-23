<?php

declare(strict_types=1);

namespace App\Shared\Contracts;

use Carbon\CarbonImmutable;

interface Relogio
{
    public function hoje(): string;

    public function instante(): CarbonImmutable;
}
