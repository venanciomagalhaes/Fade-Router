<?php

namespace Venancio\Fade\Core\Exceptions;

use Venancio\Fade\Exceptions\Throwable;

final class InsufficientArgumentsForTheRoute extends \Exception
{
    public function __construct(string $message = "Insufficient arguments for the route", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}