<?php

use PHPUnit\Framework\TestCase;
use PTS\NextRouter\CallableToMiddleware;
use PTS\NextRouter\Layer;
use PTS\NextRouter\Next;
use Zend\Diactoros\Response\JsonResponse;

class WithPriorityTest extends TestCase
{

    /** @var Next */
    protected $app;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app = new Next;
    }

    public function testPriorityLayers(): void
    {
        $this->app->getStoreLayers()
            ->use(function ($request, $next) {
                return new JsonResponse(['status' => 200]);
            });

        $data = ['a' => 20, 'b' => 50, 'c' => 50, 'd' => 40, 'e' => 0];
        foreach ($data as $name => $priority) {
            $layer = new Layer('/', new CallableToMiddleware(function () use($name) {
                return new JsonResponse(['name' => $name]);
            }));
            $layer->name = $name;
            $layer->priority = $priority;
            $this->app->getStoreLayers()->addLayer($layer);
        }

        /** @var JsonResponse $response */
        $layers = $this->app->getStoreLayers()->getLayers();
        $actual = array_map(function (Layer $layer) {
            return $layer->name;
        }, $layers);

        $this->assertSame(['e', 'a', 'd', 'layer-0', 'b', 'c'], $actual);
    }
}
