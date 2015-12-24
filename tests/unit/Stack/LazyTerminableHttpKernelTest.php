<?php

namespace Stack;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\TerminableInterface;

/*
 * Extends the LazyHttpKernelTest to perform the base tests as well, this is for BC.
 *
 * The new tests checks:
 * - if the wrapped kernel gets terminated once the lazy wrapper is.
 * - if the shortcut function returns the correct class when the second argument is provided
 */
class LazyTerminableHttpKernelTest extends LazyHttpKernelTest
{
    public function testTermination()
    {
        $app = null;

        /* @var \Stack\LazyTerminableHttpKernel $kernel */
        $kernel = lazy(function () use (&$app) {
            return $app = $this->createHelloKernel();
        }, true);

        // if the lazy kernel did not handle a request it should not be instantiated nor terminated
        $request = Request::create('/');
        $kernel->terminate($request, Response::create('fake response'));
        $this->assertNull($app);

        // if the kernel handled a request and the underlying instance should be instantiated and terminated
        $response = $kernel->handle($request);
        $this->assertInstanceOf('Stack\CallableTerminableHttpKernel', $app);
        $this->assertFalse($app->isTerminated());
        $kernel->terminate($request, $response);
        $this->assertTrue($app->isTerminated());
    }


    public function testShortcutFunction()
    {
        $kernel = lazy(function () {
            return $this->createHelloKernel();
        }, true);

        $this->assertInstanceOf('Stack\LazyTerminableHttpKernel', $kernel);
        $this->assertSame('Hello World!', $kernel->handle(Request::create('/'))->getContent());
    }

    private function createHelloKernel()
    {
        return $this->createTerminableKernel('Hello World!');
    }

    private function createTerminableKernel($body)
    {
        return new CallableTerminableHttpKernel(function (Request $request) use ($body) {
            return new Response($body);
        });
    }
}

class CallableTerminableHttpKernel extends CallableHttpKernel implements TerminableInterface
{
    private $terminated = false;

    public function terminate(Request $request, Response $response)
    {
        $this->terminated = true;
    }

    public function isTerminated()
    {
        return $this->terminated;
    }
}