<?php

use PHPUnit\Framework\TestCase;
use PTS\NextRouter\Extra\UrlCreator;
use PTS\NextRouter\Next;
use Zend\Diactoros\Response\JsonResponse;

class CreateUrlTest extends TestCase
{

    /** @var Next */
    protected $router;
    /** @var UrlCreator */
    protected $urlCreator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->router = new Next;
        $this->urlCreator = new UrlCreator($this->router);
    }

    public function testSimple(): void
    {
        $this->router->getStoreLayers()
            ->get('/users/{id}/', function ($request, $next) {
                return new JsonResponse(['status' => 200]);
            }, ['name' => 'user']);
        $query = ['format' => 'json', 'rel' => 'site'];

        $path = $this->urlCreator->url('user', ['id' => 34], ['query' => $query]);
        $expected = '/users/34/?format=json&rel=site';
        $this->assertSame($expected, $path);
    }
}
