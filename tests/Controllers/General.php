<?php

namespace Venancio\Fade\Tests\Controllers;

class General
{
    public function index(): true
    {
        echo "OK" . PHP_EOL;
        return true;
    }

    public function show()
    {

    }

    public function store()
    {

    }

    public function edit()
    {
        echo "OK";
    }

    public function update()
    {

    }

    public function destroy()
    {

    }

    public function forcingNotFound()
    {
        throw new \Venancio\Fade\Exceptions\NotFound();
    }

    public function internalServerError()
    {
        throw new \Exception('');
    }
}