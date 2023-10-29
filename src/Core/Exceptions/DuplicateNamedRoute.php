<?php

namespace Venancio\Fade\Core\Exceptions;

use Venancio\Fade\Exceptions\Throwable;

final class DuplicateNamedRoute extends \Exception
{
    public function __construct(string $message = "Duplicate named route", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}