<?php

use PHPUnit\Framework\TestCase;
use PTS\NextRouter\Layer;
use PTS\NextRouter\LayerResolver;
use PTS\NextRouter\StoreLayers;
use Zend\Diactoros\Response\JsonResponse;

class GetLastLayerTest extends TestCase
{

    /** @var StoreLayers */
    protected $store;

    public function setUp()
    {
        parent::setUp();

        $this->store = new StoreLayers(new LayerResolver);
    }

    public function testGetLastLayer(): void
    {
        $this->assertNull($this->store->getLastLayer());

        $this->store
            ->get('/', function ($request, $next) {
                return new JsonResponse(['status' => 'a']);
            }, 'a')
            ->get('/', function ($request, $next) {
                return new JsonResponse(['status' => 'b']);
            }, 'b');

        $layer = $this->store->getLastLayer();
        $this->assertInstanceOf(Layer::class, $layer);
        $this->assertSame('b', $layer->name);
    }
}
