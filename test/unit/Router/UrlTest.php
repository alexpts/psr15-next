<?php

use PHPUnit\Framework\TestCase;
use PTS\Events\Events;
use PTS\NextRouter\LayerResolver;
use PTS\NextRouter\Router;
use PTS\NextRouter\RouterException;

/**
 * @covers \PTS\NextRouter\Router::url()
 */
class UrlTest extends TestCase
{

    public function testWithoutCreatorUrl(): void
    {
        $router = new Router(new LayerResolver, new Events);
        $this->expectException(RouterException::class);
        $this->expectExceptionMessage('Need inject url creator via method `setUrlCreator`');

        $router->url('some', ['id' => 11]);
    }

}
