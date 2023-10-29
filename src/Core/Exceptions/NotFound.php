<?php

namespace Venancio\Fade\Core\Exceptions;

use Venancio\Fade\Exceptions\Throwable;

final class NotFound extends \Exception
{
    public function __construct(string $message = "Not Found", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}