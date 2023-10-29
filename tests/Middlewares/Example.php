<?php

namespace Venancio\Fade\Tests\Middlewares;

use Venancio\Fade\Core\Interfaces\Middleware;
use Venancio\Fade\Core\Traits\ParamsMiddleware;

class Example implements Middleware
{
    use ParamsMiddleware;
    public function handle():void
    {
    }

}