<?php

namespace Venancio\Fade\Exceptions;

final class UndefinedNamedRoute extends \Exception
{
  public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
  {
      parent::__construct($message, $code, $previous);
  }
}