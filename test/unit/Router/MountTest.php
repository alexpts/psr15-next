<?php

use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\TestCase;
use PTS\NextRouter\LayerResolver;
use PTS\NextRouter\Next;

class MountTest extends TestCase
{

    protected Next $app;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app = new Next(new LayerResolver);
    }

    public function testMount(): void
    {
        $request = new ServerRequest([], [], '/');
        $router2 = clone $this->app;

        $router2->getStoreLayers()
            ->use(function ($request, $next) {
                return new JsonResponse(['status' => 401]);
            }, ['path' => '/admin/.*'])
            ->use(function ($request, $next) {
                return new JsonResponse(['status' => 200]);
            }, ['path' => '/']);

        /** @var JsonResponse $response */
        $response = $this->app
            ->mount($router2, '/api')
            ->handle($request);

        $this->assertSame(['status' => 200], $response->getPayload());
    }

    public function testMountWithoutPath(): void
    {
        $request = new ServerRequest([], [], '/');
        $router2 = clone $this->app;

        $router2->getStoreLayers()
            ->use(function ($request, $next) {
                return new JsonResponse(['status' => 401]);
            }, ['path' => '/admin/.*'])
            ->use(function ($request, $next) {
                return new JsonResponse(['status' => 200]);
            }, ['path' => '/']);

        /** @var JsonResponse $response */
        $response = $this->app
            ->mount($router2)
            ->handle($request);

        $this->assertSame(['status' => 200], $response->getPayload());
    }
}
