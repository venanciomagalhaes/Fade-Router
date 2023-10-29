<?php

namespace Venancio\Fade\Core\Traits;

trait ParamsMiddleware
{
    private array $params = [];
    public function setParams($params): void
    {
        $this->params = $params;
    }
}