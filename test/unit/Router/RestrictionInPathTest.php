<?php

use PHPUnit\Framework\TestCase;
use PTS\Events\Events;
use PTS\NextRouter\CallableToMiddleware;
use PTS\NextRouter\LayerResolver;
use PTS\NextRouter\Layer;
use PTS\NextRouter\Router;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\ServerRequest;

class RestrictionInPathTest extends TestCase
{

    /** @var Router */
    protected $router;

    public function setUp()
    {
        parent::setUp();

        $this->router = new Router(new LayerResolver, new Events);
    }

    public function testSimple(): void
    {
        $request = new ServerRequest([], [], '/users/alex/');
        $request2 = new ServerRequest([], [], '/users/5/');

        $route = new Layer('/users/{id}/', new CallableToMiddleware(function ($request, $next) {
            return new JsonResponse(['status' => 'user route']);
        }));
        $route->setRestrictions(['id' => '\d+']);

        $this->router
            ->addLayer($route)
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