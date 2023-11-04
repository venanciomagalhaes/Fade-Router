<?php

namespace Venancio\Fade\Core\Traits;

trait ParamsMiddleware
{
    /**
     * @var array $params An associative array to store parameters.
     */
    private array $params = [];

    /**
     * Set parameters for the middleware.
     *
     * This method sets the parameters needed for middleware processing.
     *
     * @param array $params An associative array of parameters.
     * @return void
     */
    public function setParams(array $params): void
    {
        $this->params = $params;
    }
}