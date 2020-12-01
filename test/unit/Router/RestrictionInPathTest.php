<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PTS\NextRouter\CallableToMiddleware;
use PTS\NextRouter\Layer;
use PTS\NextRouter\Next;
use PTS\NextRouter\Resolver\LayerResolver;
use PTS\Psr7\Response\JsonResponse;
use PTS\Psr7\ServerRequest;
use PTS\Psr7\Uri;

class RestrictionInPathTest extends TestCase
{

    protected Next $app;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app = new Next(new LayerResolver);
    }

    public function testSimple(): void
    {
        $resolver = new LayerResolver;

        $request = new ServerRequest('GET', new Uri('/users/alex/'));
        $request2 = new ServerRequest('GET', new Uri('/users/5/'));

        $layer = new Layer(
            '/users/{id}/',
            new CallableToMiddleware(fn() => new JsonResponse(['status' => 'user route'])
        ));
        $layer->restrictions = ['id' => '\d+'];
        $layer->regexp = $resolver->makeRegExp($layer);

        $this->app->getRouterStore()
            ->addLayer($layer)
            ->use(fn($request, $next) => new JsonResponse(['status' => 'otherwise']));

        /** @var JsonResponse $response */
        $response = $this->app->handle($request);
        /** @var JsonResponse $response2 */
        $response2 = $this->app->handle($request2);

        static::assertSame(['status' => 'otherwise'], $response->getData());
        static::assertSame(['status' => 'user route'], $response2->getData());
    }
}
