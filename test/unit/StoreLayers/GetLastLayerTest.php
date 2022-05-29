<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PTS\NextRouter\Layer;
use PTS\NextRouter\Resolver\LayerResolver;
use PTS\NextRouter\StoreLayers;
use PTS\Psr7\Response\JsonResponse;

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
            ->get('/', fn($request, $next) => new JsonResponse(['status' => 'a']), ['name' => 'a'])
            ->get('/', fn($request, $next) => new JsonResponse(['status' => 'b']), ['name' => 'b']);

        $layer = $this->store->getLastLayer();
        $this->assertInstanceOf(Layer::class, $layer);
        $this->assertSame('b', $layer->name);
    }
}
