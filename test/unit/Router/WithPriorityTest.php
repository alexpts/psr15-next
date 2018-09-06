<?php

use PHPUnit\Framework\TestCase;
use PTS\NextRouter\CallableToMiddleware;
use PTS\NextRouter\Layer;
use PTS\NextRouter\Next;
use Zend\Diactoros\Response\JsonResponse;

class WithPriorityTest extends TestCase
{

    /** @var Next */
    protected $router;

    public function setUp()
    {
        parent::setUp();
        $this->router = new Next;
    }

    public function testPriorityLayers(): void
    {
        $this->router->getStoreLayers()
            ->use(function ($request, $next) {
                return new JsonResponse(['status' => 200]);
            });

        foreach (['a' => 20, 'b' => 50, 'c' => 50, 'd' => 40] as $name => $priority) {
            $layer = new Layer('/', new CallableToMiddleware(function () use($name) {
                return new JsonResponse(['name' => $name]);
            }), $name);
            $layer->setPriority($priority);
            $this->router->getStoreLayers()->addLayer($layer);
        }

        $this->router->getStoreLayers()->sortByPriority();

        /** @var JsonResponse $response */
        $layers = $this->router->getStoreLayers()->getLayers();
        $actual = array_map(function (Layer $layer) {
            return $layer->name;
        }, $layers);

        $this->assertSame(['a', 'd', 'layer-0', 'b', 'c'], $actual);
    }
}
