<?php

use PHPUnit\Framework\TestCase;
use PTS\NextRouter\Extra\UrlCreator;
use PTS\NextRouter\Next;
use Zend\Diactoros\Response\JsonResponse;


class UrlTest extends TestCase
{

    /** @var Next */
    protected $app;
    /** @var UrlCreator */
    protected $urlCreator;

    public function setUp()
    {
        parent::setUp();

        $this->app = new Next;
        $this->urlCreator = new UrlCreator($this->app);
    }

    public function testGood(): void
    {
        $this->app->getStoreLayers()
            ->get('/users/{id}/', function ($request, $next) {
                return new JsonResponse(['status' => 200]);
            }, ['name' => 'user']);
        $query = ['format' => 'json', 'rel' => 'site'];

        $path = $this->urlCreator->url('user', ['id' => 34], ['query' => $query]);
        $expected = '/users/34/?format=json&rel=site';
        $this->assertSame($expected, $path);
    }

}
