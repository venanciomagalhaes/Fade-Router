<?php

namespace Venancio\Fade\Core\Interfaces;

interface Middleware
{
    public function handle():void;

    public function setParams(array $params):void;
}