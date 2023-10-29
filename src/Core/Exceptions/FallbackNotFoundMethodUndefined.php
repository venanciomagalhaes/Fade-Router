<?php

namespace Venancio\Fade\Core\Exceptions;

use Venancio\Fade\Exceptions\Throwable;

final class FallbackNotFoundMethodUndefined extends \Exception
{
    public function __construct(string $message = "Fallback not found method undefined", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}