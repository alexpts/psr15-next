<?php

use PHPUnit\Framework\TestCase;
use PTS\NextRouter\LayerResolver;
use PTS\NextRouter\Router;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\ServerRequest;

class MountTest extends TestCase
{

    /** @var Router */
    protected $router;

    public function setUp()
    {
        parent::setUp();

        $this->router = new Router(new LayerResolver);
    }

    public function testMount(): void
    {
        $request = new ServerRequest([], [], '/');
        $router2 = clone $this->router;

        $router2->getStore()
            ->use(function ($request, $next) {
                return new JsonResponse(['status' => 401]);
            }, ['path' => '/admin/.*'])
            ->use(function ($request, $next) {
                return new JsonResponse(['status' => 200]);
            });

        /** @var JsonResponse $response */
        $response = $this->router
            ->mount($router2, '/api')
            ->handle($request);

        $this->assertSame(['status' => 200], $response->getPayload());
    }
}
