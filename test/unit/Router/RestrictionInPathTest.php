<?php

use PHPUnit\Framework\TestCase;
use PTS\NextRouter\CallableToMiddleware;
use PTS\NextRouter\Layer;
use PTS\NextRouter\LayerResolver;
use PTS\NextRouter\Next;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\ServerRequest;

class RestrictionInPathTest extends TestCase
{

    /** @var Next */
    protected $router;

    public function setUp()
    {
        parent::setUp();

        $this->router = new Next(new LayerResolver);
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

        $this->router->getStoreLayers()
            ->addLayer($layer)
            ->use(function ($request, $next) {
                return new JsonResponse(['status' => 'otherwise']);
            });

        /** @var JsonResponse $response */
        $response = $this->router->handle($request);
        /** @var JsonResponse $response2 */
        $response2 = $this->router->handle($request2);

        $this->assertSame(['status' => 'otherwise'], $response->getPayload());
        $this->assertSame(['status' => 'user route'], $response2->getPayload());
    }
}
