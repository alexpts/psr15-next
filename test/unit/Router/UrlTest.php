<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PTS\NextRouter\Extra\UrlCreator;
use PTS\NextRouter\Next;
use PTS\Psr7\Response\JsonResponse;

class UrlTest extends TestCase
{

    protected Next $app;
    protected UrlCreator $urlCreator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app = new Next;
        $this->urlCreator = new UrlCreator($this->app);
    }

    public function testGood(): void
    {
        $this->app->getRouterStore()
            ->get('/users/{id}/', fn() => new JsonResponse(['status' => 200]), ['name' => 'user']);

        $query = ['format' => 'json', 'rel' => 'site'];
        $path = $this->urlCreator->url('user', ['id' => 34], ['query' => $query]);
        $expected = '/users/34/?format=json&rel=site';
        static::assertSame($expected, $path);
    }

}
