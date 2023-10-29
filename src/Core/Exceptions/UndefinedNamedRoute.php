<?php

namespace Venancio\Fade\Core\Exceptions;

use Venancio\Fade\Exceptions\Throwable;

final class UndefinedNamedRoute extends \Exception
{
  public function __construct(string $message = "Undefined named route", int $code = 0, ?Throwable $previous = null)
  {
      parent::__construct($message, $code, $previous);
  }
}