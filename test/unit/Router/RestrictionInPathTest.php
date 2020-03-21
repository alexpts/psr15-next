<?php

use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\TestCase;
use PTS\NextRouter\CallableToMiddleware;
use PTS\NextRouter\Layer;
use PTS\NextRouter\Next;
use PTS\NextRouter\Resolver\LayerResolver;

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

    	$request = new ServerRequest([], [], '/users/alex/');
        $request2 = new ServerRequest([], [], '/users/5/');

        $layer = new Layer('/users/{id}/', new CallableToMiddleware(function ($request, $next) {
            return new JsonResponse(['status' => 'user route']);
        }));
		$layer->restrictions = ['id' => '\d+'];
		$layer->regexp = $resolver->makeRegExp($layer);

        $this->app->getRouterStore()
            ->addLayer($layer)
            ->use(fn($request, $next) => new JsonResponse(['status' => 'otherwise']));

        /** @var JsonResponse $response */
        $response = $this->app->handle($request);
        /** @var JsonResponse $response2 */
        $response2 = $this->app->handle($request2);

        $this->assertSame(['status' => 'otherwise'], $response->getPayload());
        $this->assertSame(['status' => 'user route'], $response2->getPayload());
    }
}
