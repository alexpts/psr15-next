<?php

use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;
use PTS\NextRouter\Next;

class PipeMethodTest extends TestCase
{

    protected Next $app;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app = new Next;
    }

    public function testMethod(): void
    {
        $request = new ServerRequest([], [], '/profile', 'GET');

        $this->app->getRouterStore()
            ->get('/user', fn ($request, $next) => new JsonResponse(['status' => 'user']) )
            ->pipe([
                function($request, RequestHandlerInterface $next) {
                    /** @var JsonResponse $response */
                    $response = $next->handle($request);
                    $body = array_merge($response->getPayload(), ['pipe1' => true]);
                    return new JsonResponse($body);
                },
                function($request, RequestHandlerInterface $next) {
                    $response = $next->handle($request);
                    $body = array_merge($response->getPayload(), ['pipe2' => true]);
                    return new JsonResponse($body);
                },
            ], ['method' => ['GET'], 'path' => '/profile'])
	        ->use(fn() => new JsonResponse(['status' => 404]), ['name' => 'otherwise']);

        /** @var JsonResponse $response */
        $response = $this->app->handle($request);

        $this->assertSame(['status' => 404, 'pipe2' => true, 'pipe1' => true], $response->getPayload());
    }

    public function testMethod2(): void
    {
        $request = new ServerRequest([], [], '/profile', 'GET');

        $this->app->getRouterStore()
            ->get('/user', fn($request, $next) => new JsonResponse(['status' => 'user']))
            ->pipe([
                function($request, RequestHandlerInterface $next) {
                    /** @var JsonResponse $response */
                    $response = $next->handle($request);
                    $body = array_merge($response->getPayload(), ['pipe1' => true]);
                    return new JsonResponse($body);
                },
                fn($request, $next) => new JsonResponse(['pipe2' => true]),
            ], ['method' => ['GET'], 'path' => '/profile']);

        /** @var JsonResponse $response */
        $response = $this->app->handle($request);

        $this->assertSame(['pipe2' => true, 'pipe1' => true], $response->getPayload());
    }
}
