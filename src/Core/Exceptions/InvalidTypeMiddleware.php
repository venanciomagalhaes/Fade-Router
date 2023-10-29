<?php

namespace Venancio\Fade\Core\Exceptions;

use Venancio\Fade\Exceptions\Throwable;

final class InvalidTypeMiddleware extends \Exception
{
    public function __construct(
        string $message = "it is necessary to implement the Venancio\Fade\Core\Interfaces\Middleware interface in all your Middleware",
        int $code = 0,
        ?Throwable
        $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}