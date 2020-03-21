<?php

use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\TestCase;
use PTS\NextRouter\Next;

class GetTest extends TestCase
{

	protected Next $app;

	protected function setUp(): void
	{
		parent::setUp();

		$this->app = new Next;
	}

	public function testSimple(): void
	{
		$request = new ServerRequest([], [], '/');

		$this->app->getRouterStore()
			->get('/', fn($request, $next) => new JsonResponse(['status' => 200]) );

		/** @var JsonResponse $response */
		$response = $this->app->handle($request);

		$this->assertSame(['status' => 200], $response->getPayload());
	}

	public function testInvoke(): void
	{
		$request = new ServerRequest([], [], '/');
		$router = $this->app;

		$router->getRouterStore()
			->get('/', fn($request, $next) => new JsonResponse(['status' => 200]) );
		/** @var JsonResponse $response */
		$response = $router($request);

		$this->assertSame(['status' => 200], $response->getPayload());
	}

	public function testSimple2(): void
	{
		$request = new ServerRequest([], [], '/main');

		$this->app->getRouterStore()
			->get('/', function ($request, $next) {
				throw new \Exception('must skip');
			})
			->get('/main', fn($request, $next) => new JsonResponse(['status' => 'main']) );

		/** @var JsonResponse $response */
		$response = $this->app->handle($request);

		$this->assertSame(['status' => 'main'], $response->getPayload());
	}

	public function testFallback(): void
	{
		$request = new ServerRequest([], [], '/otherwise');

		$this->app->getRouterStore()
			->get('/', function ($request, $next) {
				throw new \Exception('must skip');
			})
			->get('/main', fn($request, $next) => new JsonResponse(['status' => 'main']) )
			->use(fn($request, $next) => new JsonResponse(['status' => 'otherwise']) );

		/** @var JsonResponse $response */
		$response = $this->app->handle($request);

		$this->assertSame(['status' => 'otherwise'], $response->getPayload());
	}

	public function testWithPrefix(): void
	{
		$request = new ServerRequest([], [], '/admins/dashboard');
		$router = new Next;

		$router->getRouterStore()
			->setPrefix('/admins')
			->get('/admins/dashboard', function($request, $next) { // /admins/admins/dashboard
				throw new \Exception('must skip');
			})
			->get('/dashboard', fn($request, $next) => new JsonResponse(['status' => 'dashboard']) );

		/** @var JsonResponse $response */
		$response = $router->handle($request);

		$this->assertSame(['status' => 'dashboard'], $response->getPayload());
	}
}
