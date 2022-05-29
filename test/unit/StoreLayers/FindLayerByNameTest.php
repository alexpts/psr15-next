<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PTS\NextRouter\Layer;
use PTS\NextRouter\Resolver\LayerResolver;
use PTS\NextRouter\StoreLayers;
use PTS\Psr7\Response\JsonResponse;

class FindLayerByNameTest extends TestCase
{

    protected StoreLayers $store;

    public function setUp(): void
    {
        parent::setUp();

        $this->store = new StoreLayers(new LayerResolver);
    }

    public function testFindLayerByName(): void
    {
        $this->store
            ->method('GET', '/', fn($request, $next) => new JsonResponse(['status' => 'a']), ['name' => 'a'])
            ->method('GET', '/', fn($request, $next) => new JsonResponse(['status' => 'b']), ['name' => 'b']);

        $this->assertInstanceOf(Layer::class, $this->store->findLayerByName('a'));
        $this->assertInstanceOf(Layer::class, $this->store->findLayerByName('b'));
        $this->assertNull($this->store->findLayerByName('c'));
    }
}
