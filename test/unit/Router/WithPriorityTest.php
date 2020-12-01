<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PTS\NextRouter\CallableToMiddleware;
use PTS\NextRouter\Layer;
use PTS\NextRouter\Next;
use PTS\Psr7\Response\JsonResponse;

class WithPriorityTest extends TestCase
{

    protected Next $app;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app = new Next;
    }

    public function testPriorityLayers(): void
    {
        $this->app->getRouterStore()
            ->use(fn($request, $next) => new JsonResponse(['status' => 200]));

        $data = ['a' => 20, 'b' => 50, 'c' => 50, 'd' => 40, 'e' => 0];
        foreach ($data as $name => $priority) {
            $layer = new Layer('/', new CallableToMiddleware(function () use($name) {
                return new JsonResponse(['name' => $name]);
            }));
            $layer->name = $name;
            $layer->priority = $priority;
            $this->app->getRouterStore()->addLayer($layer);
        }

        /** @var JsonResponse $response */
        $layers = $this->app->getRouterStore()->getLayers();
        $actual = array_map(fn(Layer $layer) => $layer->name, $layers);

        static::assertSame(['e', 'a', 'd', 'layer-0', 'b', 'c'], $actual);
    }
}
