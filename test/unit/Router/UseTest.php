<?php

use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;
use PTS\NextRouter\LayerResolver;
use PTS\NextRouter\Next;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\ServerRequest;

class UseTest extends TestCase
{

    /** @var Next */
    protected $router;

    public function setUp()
    {
        parent::setUp();

        $this->router = new Next(new LayerResolver);
    }

    public function testMethod(): void
    {
        $request = new ServerRequest();

        $this->router->getStoreLayers()
            ->use(function ($request, $next) {
                return new JsonResponse(['status' => 200]);
            });

        /** @var JsonResponse $response */
        $response = $this->router->handle($request);

        $this->assertSame(['status' => 200], $response->getPayload());
    }

    public function testChainMiddlewares(): void
    {
        $request = new ServerRequest();

        $this->router->getStoreLayers()
            ->use(function ($request, RequestHandlerInterface $next) {
                return $next->handle($request);
            })
            ->use(function ($request, $next) {
                return new JsonResponse(['status' => 202]);
            });

        /** @var JsonResponse $response */
        $response = $this->router->handle($request);

        $this->assertSame(['status' => 202], $response->getPayload());
    }

    public function testPathMiddlewares(): void
    {
        $request = new ServerRequest();

        $this->router->getStoreLayers()
            ->use(function ($request, $next) {
                return new JsonResponse(['name' => 'A']);
            }, ['path' => '/blog'])
            ->use(function ($request, $next) {
                return new JsonResponse(['name' => 'B']);
            });

        /** @var JsonResponse $response */
        $response = $this->router->handle($request);

        $this->assertSame(['name' => 'B'], $response->getPayload());
    }
}
