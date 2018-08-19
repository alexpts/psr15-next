<?php

use PHPUnit\Framework\TestCase;
use PTS\Events\Events;
use PTS\NextRouter\Extra\CreateUrl;
use PTS\NextRouter\LayerResolver;
use PTS\NextRouter\Router;
use PTS\NextRouter\RouterException;
use Zend\Diactoros\Response\JsonResponse;

class CreateUrlTest extends TestCase
{

    /** @var Router */
    protected $router;

    public function setUp()
    {
        parent::setUp();

        $this->router = new Router(new LayerResolver, new Events);
        $this->router->setUrlCreator(new CreateUrl);
    }

    /**
     * @throws RouterException
     */
    public function testSimple(): void
    {
        $this->router
            ->get('/users/{id}/', function ($request, $next) {
                return new JsonResponse(['status' => 200]);
            }, 'user');
        $query = ['format' => 'json', 'rel' => 'site'];

        $path = $this->router->url('user', ['id' => 34], ['query' => $query]);
        $expected = '/users/34/?format=json&rel=site';
        $this->assertSame($expected, $path);
    }
}
