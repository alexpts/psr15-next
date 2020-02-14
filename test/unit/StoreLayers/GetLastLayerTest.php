<?php

use Laminas\Diactoros\Response\JsonResponse;
use PHPUnit\Framework\TestCase;
use PTS\NextRouter\Layer;
use PTS\NextRouter\LayerResolver;
use PTS\NextRouter\StoreLayers;

class GetLastLayerTest extends TestCase
{

    protected StoreLayers $store;

    public function setUp(): void
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
            }, ['name' => 'a'])
            ->get('/', function ($request, $next) {
                return new JsonResponse(['status' => 'b']);
            }, ['name' => 'b']);

        $layer = $this->store->getLastLayer();
        $this->assertInstanceOf(Layer::class, $layer);
        $this->assertSame('b', $layer->name);
    }
}
