<?php

namespace Venancio\Fade\Core\Interfaces;

interface Middleware
{
    /**
     * Handle the request using this middleware.
     *
     * Implementing classes must define the behavior of this method to process the request.
     *
     * @return void
     */
    public function handle(): void;

    /**
     * Set parameters for the middleware.
     *
     * Implementing classes should use this method to set any parameters needed for request processing.
     *
     * @param array $params An associative array of parameters.
     *
     * @return void
     */
    public function setParams(array $params): void;
}