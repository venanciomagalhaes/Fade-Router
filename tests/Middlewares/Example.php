<?php

namespace Venancio\Fade\Tests\Middlewares;

use Venancio\Fade\Core\Interfaces\Middleware;

class Example implements Middleware
{
    public function handle():void
    {
        echo ('passou') . PHP_EOL;die;
    }
}