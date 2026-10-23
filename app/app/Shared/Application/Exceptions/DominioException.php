<?php

declare(strict_types=1);

namespace App\Shared\Application\Exceptions;

use RuntimeException;

/**
 * Raiz de toda falha de regra de negócio que não é erro de transporte.
 * Existe para que o formatador de erro e o log distingam domínio de defeito.
 */
abstract class DominioException extends RuntimeException {}
