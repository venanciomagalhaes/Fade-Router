<?php

namespace Venancio\Fade\Tests\Controllers;

class General
{
    public function index(): void
    {
    }

    public function show():void
    {

    }

    public function store():void
    {

    }

    public function edit():void
    {

    }

    public function update()
    {

    }

    public function destroy():void
    {

    }

    public function forcingNotFound():void
    {
        throw new \Venancio\Fade\Core\Exceptions\NotFound();
    }

    public function internalServerError():void
    {
        throw new \Exception('Testing internal server error');
    }
}