<?php

namespace Stack;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\TerminableInterface;

class LazyTerminableHttpKernel extends LazyHttpKernel implements TerminableInterface
{
    public function terminate(Request $request, Response $response)
    {
        // don't instantiate the wrapped lazy kernel but do terminate it if it's been instantiated already
        if ($this->app instanceof TerminableInterface) {
            return $this->app->terminate($request, $response);
        }
    }
}
