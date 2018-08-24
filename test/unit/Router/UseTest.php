<?php

use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;
use PTS\NextRouter\LayerResolver;
use PTS\NextRouter\Router;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\ServerRequest;

class UseTest extends TestCase
{

    /** @var Router */
    protected $router;

    public function setUp()
    {
        parent::setUp();

        $this->router = new Router(new LayerResolver);
    }

    public function testMethod(): void
    {
        $request = new ServerRequest();
        /** @var JsonResponse $response */

        $response = $this->router
            ->use(function ($request, $next) {
                return new JsonResponse(['status' => 200]);
            })
            ->handle($request);

        $this->assertSame(['status' => 200], $response->getPayload());
    }

    public function testChainMiddlewares(): void
    {
        $request = new ServerRequest();
        /** @var JsonResponse $response */

        $response = $this->router
            ->use(function ($request, RequestHandlerInterface $next) {
                return $next->handle($request);
            })
            ->use(function ($request, $next) {
                return new JsonResponse(['status' => 202]);
            })
            ->handle($request);

        $this->assertSame(['status' => 202], $response->getPayload());
    }

    public function testPathMiddlewares(): void
    {
        $request = new ServerRequest();
        /** @var JsonResponse $response */

        $response = $this->router
            ->use(function ($request, $next) {
                return new JsonResponse(['name' => 'A']);
            }, '/blog')
            ->use(function ($request, $next) {
                return new JsonResponse(['name' => 'B']);
            })
            ->handle($request);

        $this->assertSame(['name' => 'B'], $response->getPayload());
    }
}
