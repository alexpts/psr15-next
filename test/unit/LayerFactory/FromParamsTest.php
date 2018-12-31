<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use PTS\NextRouter\CallableToMiddleware;
use PTS\NextRouter\Extra\EndPoint\DynamicPoint;
use PTS\NextRouter\Extra\EndPoint\EndPoint;
use PTS\NextRouter\Layer;
use PTS\NextRouter\LayerFactory;
use PTS\NextRouter\Next;
use PTS\NextRouter\RouterException;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\ServerRequest;

class FromParamsTest extends TestCase
{
    public static function staticAction(): ResponseInterface
    {
        return new JsonResponse(['message' => 'staticAction']);
    }

    public function __invoke()
    {
        return new JsonResponse(['message' => 'invoke']);
    }

    public function action(): ResponseInterface
    {
        return new JsonResponse(['message' => 'action']);
    }

    public function testCreate(): void
    {
        $factory = new LayerFactory;
        $config = [
            'name' => 'mainPage',
            'priority' => 150,
            'path' => '/users/{id}',
            'methods' => ['GET'],
            'restrictions' => [
                'id' => '\d+',
            ],
            'callable' => function($request) {},
        ];

        $layer = $factory->create($config);

        static::assertSame('mainPage', $layer->name);
        static::assertSame('/users/(?<id>[^\/]+)', $layer->regexp);
        static::assertSame(150, $layer->priority);
        static::assertSame(Layer::TYPE_MIDDLEWARE, $layer->type);
        static::assertSame(['GET'], $layer->methods);
        static::assertSame([], $layer->matches);
        static::assertSame(['id' => '\d+'], $layer->restrictions);
        static::assertInstanceOf(CallableToMiddleware::class, $layer->md);
    }

    public function testCreateEndpoint(): void
    {
        $factory = new LayerFactory;
        $config = [
            'path' => '/users/{id}',
            'endpoint' => [],
        ];

        $layer = $factory->create($config);
        static::assertInstanceOf(EndPoint::class, $layer->md);
    }

    public function testCreateDynamicPoint(): void
    {
        $factory = new LayerFactory;
        $config = [
            'path' => '/users/{id}',
            'dynamicPoint' => [],
        ];

        $layer = $factory->create($config);
        static::assertInstanceOf(DynamicPoint::class, $layer->md);
    }

    public function testCreateStaticMethod(): void
    {
        $factory = new LayerFactory;
        $config = [
            'path' => '/',
            'callable' => 'FromParamsTest::staticAction',
        ];

        $layer = $factory->create($config);
        static::assertInstanceOf(CallableToMiddleware::class, $layer->md);

        $app = new Next;
        $app->getStoreLayers()->addLayer($layer);
        $request = new ServerRequest([], [], '/');
        $response = $app->handle($request);
        static::assertSame('{"message":"staticAction"}', $response->getBody()->getContents());
    }

    public function testCreateFromMethod(): void
    {
        $factory = new LayerFactory;
        $config = [
            'path' => '/',
            'callable' => ['FromParamsTest', 'action'],
        ];

        $layer = $factory->create($config);
        static::assertInstanceOf(CallableToMiddleware::class, $layer->md);

        $app = new Next;
        $app->getStoreLayers()->addLayer($layer);
        $request = new ServerRequest([], [], '/');
        $response = $app->handle($request);
        static::assertSame('{"message":"action"}', $response->getBody()->getContents());
    }

    public function testCreateFromSymfonyFormatMethod(): void
    {
        $factory = new LayerFactory;
        $config = [
            'path' => '/',
            'callable' => 'FromParamsTest:action',
        ];

        $layer = $factory->create($config);
        static::assertInstanceOf(CallableToMiddleware::class, $layer->md);

        $app = new Next;
        $app->getStoreLayers()->addLayer($layer);
        $request = new ServerRequest([], [], '/');
        $response = $app->handle($request);
        static::assertSame('{"message":"action"}', $response->getBody()->getContents());
    }

    public function testCreateFromObject(): void
    {
        $factory = new LayerFactory;
        $config = [
            'path' => '/',
            'callable' => 'FromParamsTest',
        ];

        $layer = $factory->create($config);
        static::assertInstanceOf(CallableToMiddleware::class, $layer->md);

        $app = new Next;
        $app->getStoreLayers()->addLayer($layer);
        $request = new ServerRequest([], [], '/');
        $response = $app->handle($request);
        static::assertSame('{"message":"invoke"}', $response->getBody()->getContents());
    }

    public function testCreateUnknown(): void
    {
        $factory = new LayerFactory;
        $config = [
            'path' => '/users/{id}',
        ];

        $this->expectException(RouterException::class);
        $factory->create($config);
    }
}