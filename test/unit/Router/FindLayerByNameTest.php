<?php

use PHPUnit\Framework\TestCase;
use PTS\Events\Events;
use PTS\NextRouter\Extra\CreateUrl;
use PTS\NextRouter\LayerResolver;
use PTS\NextRouter\Router;
use PTS\NextRouter\RouterException;

/**
 * @covers \PTS\NextRouter\Router::findLayerByName()
 */
class FindLayerByNameTest extends TestCase
{

    /** @var Router */
    protected $router;

    public function setUp()
    {
        parent::setUp();

        $this->router = new Router(new LayerResolver, new Events);
        $this->router->setUrlCreator(new CreateUrl);
    }

    public function testCantFind(): void
    {
        $name = 'unknown2';
        $this->expectException(RouterException::class);
        $this->expectExceptionMessage("Layer with name {$name} not found");

        $this->router->url($name, ['id' => 11]);
    }

}
