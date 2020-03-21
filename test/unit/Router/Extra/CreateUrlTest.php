<?php

use Laminas\Diactoros\Response\JsonResponse;
use PHPUnit\Framework\TestCase;
use PTS\NextRouter\Extra\UrlCreator;
use PTS\NextRouter\Next;

class CreateUrlTest extends TestCase
{

    protected Next $router;
    protected UrlCreator $urlCreator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->router = new Next;
        $this->urlCreator = new UrlCreator($this->router);
    }

    public function testSimple(): void
    {
        $this->router->getRouterStore()
            ->get('/users/{id}/', fn($request, $next) => new JsonResponse(['status' => 200]), ['name' => 'user']);

        $query = ['format' => 'json', 'rel' => 'site'];
        $path = $this->urlCreator->url('user', ['id' => 34], ['query' => $query]);
        $expected = '/users/34/?format=json&rel=site';
        $this->assertSame($expected, $path);
    }
}
