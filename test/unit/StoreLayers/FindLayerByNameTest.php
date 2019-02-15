<?php

use PHPUnit\Framework\TestCase;
use PTS\NextRouter\Layer;
use PTS\NextRouter\LayerResolver;
use PTS\NextRouter\StoreLayers;
use Zend\Diactoros\Response\JsonResponse;

class FindLayerByNameTest extends TestCase
{

    /** @var StoreLayers */
    protected $store;

    public function setUp(): void
    {
        parent::setUp();

        $this->store = new StoreLayers(new LayerResolver);
    }

    public function testFindLayerByName(): void
    {
        $this->store
            ->method('GET', '/', function ($request, $next) {
                return new JsonResponse(['status' => 'a']);
            }, ['name' => 'a'])
            ->method('GET', '/', function ($request, $next) {
                return new JsonResponse(['status' => 'b']);
            }, ['name' => 'b']);

        $this->assertInstanceOf(Layer::class, $this->store->findLayerByName('a'));
        $this->assertInstanceOf(Layer::class, $this->store->findLayerByName('b'));
        $this->assertNull($this->store->findLayerByName('c'));
    }
}
