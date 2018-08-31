<?php

use PHPUnit\Framework\TestCase;
use PTS\NextRouter\Controller\UserController;
use PTS\NextRouter\Router;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\ServerRequest;

class EndPointTest extends TestCase
{

    /** @var Router */
    protected $router;

    public function setUp()
    {
        parent::setUp();
        $this->router = new Router;
    }

    public function testEndPoint(): void
    {
        $request = new ServerRequest([], [], '/');
        $store = $this->router->getStore();

        $endPoint = ['controller' => UserController::class];
        $layer = $store->getLayerFactory()->endPoint($endPoint, [
            'path' => '/'
        ]);
        $store->addLayer($layer);

        /** @var JsonResponse $response */
        $response = $this->router->handle($request);
        $this->assertSame(['action' => 'main'], $response->getPayload());
    }

    public function testBacController(): void
    {
        $request = new ServerRequest([], [], '/');
        $store = $this->router->getStore();

        $endPoint = ['controller' => 'UnknownClass', 'nextOnError' => false];
        $layer = $store->getLayerFactory()->endPoint($endPoint, [
            'path' => '/'
        ]);
        $store->addLayer($layer);

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Controller not found');
        $this->router->handle($request);
    }

    public function testBacAction(): void
    {
        $request = new ServerRequest([], [], '/');
        $store = $this->router->getStore();

        $endPoint = ['controller' => UserController::class, 'action' => 'unknown', 'nextOnError' => false];
        $layer = $store->getLayerFactory()->endPoint($endPoint, [
            'path' => '/'
        ]);
        $store->addLayer($layer);

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Action not found');
        $this->router->handle($request);
    }

    public function testBacControllerNext(): void
    {
        $request = new ServerRequest([], [], '/');
        $store = $this->router->getStore();

        $endPoint = ['controller' => 'UnknownClass', 'nextOnError' => true];
        $layer = $store->getLayerFactory()->endPoint($endPoint, [
            'path' => '/'
        ]);
        $store->addLayer($layer)->use(function (){
            return new JsonResponse(['action' => 'otherwise']);
        });

        /** @var JsonResponse $response */
        $response = $this->router->handle($request);
        $this->assertSame(['action' => 'otherwise'], $response->getPayload());
    }

    public function testFilterMatches(): void
    {
        $request = new ServerRequest([], [], '/');
        $store = $this->router->getStore();

        $endPoint = ['controller' => UserController::class];
        $layer = $store->getLayerFactory()->endPoint($endPoint, [
            'path' => '/'
        ]);
        $store->addLayer($layer);

        $request = $request->withAttribute('params', [
            '_action' => 'get',
            'id' => 4
        ]);

        /** @var JsonResponse $response */
        $response = $this->router->handle($request);
        $this->assertSame(['action' => 'get'], $response->getPayload());
    }
}