<?php

namespace Venancio\Fade\Core\Exceptions;

use Venancio\Fade\Exceptions\Throwable;

final class FallbackInternalServerErrorControllerUndefined extends \Exception
{
    public function __construct(string $message = "Fallback internal server error controller undefined", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}