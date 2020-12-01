<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PTS\NextRouter\Next;
use PTS\NextRouter\Resolver\LayerResolver;
use PTS\Psr7\Response\JsonResponse;
use PTS\Psr7\ServerRequest;
use PTS\Psr7\Uri;

class HttpMethodTest extends TestCase
{

    protected Next $app;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app = new Next(new LayerResolver);
    }

    public function testSimple(): void
    {
        $request = new ServerRequest('GET', new Uri('/'));

        $this->app->getRouterStore()
            // expected skip by http method
            ->post('/', fn($request, $next) => new JsonResponse(['method' => 'post']) )
            ->patch('/', fn($request, $next) => new JsonResponse(['method' => 'patch']) )
            ->get('/', fn($request, $next) => new JsonResponse(['method' => 'get']) );

        /** @var JsonResponse $response */
        $response = $this->app->handle($request);
        static::assertSame(['method' => 'get'], $response->getData());
    }
}
