<?php

use PHPUnit\Framework\TestCase;
use PTS\NextRouter\Extra\UrlCreator;
use PTS\NextRouter\Router;
use PTS\NextRouter\RouterException;
use Zend\Diactoros\Response\JsonResponse;

class CreateUrlTest extends TestCase
{

    /** @var Router */
    protected $router;
    /** @var UrlCreator */
    protected $urlCreator;

    public function setUp()
    {
        parent::setUp();

        $this->router = new Router;
        $this->urlCreator = new UrlCreator($this->router);
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

        $path = $this->urlCreator->url('user', ['id' => 34], ['query' => $query]);
        $expected = '/users/34/?format=json&rel=site';
        $this->assertSame($expected, $path);
    }
}
