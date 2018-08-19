<?php

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use PTS\Events\Events;
use PTS\Events\EventsInterface;
use PTS\NextRouter\Extra\LayerStatusProgress;
use PTS\NextRouter\LayerResolver;
use PTS\NextRouter\Router;
use PTS\NextRouter\Runner;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\ServerRequest;

class LayerStatusProgressTest extends TestCase
{

    /** @var Router */
    protected $router;
    /** @var EventsInterface */
    protected $events;

    public function setUp()
    {
        parent::setUp();

        $this->events = new Events;
        $this->router = new Router(new LayerResolver, $this->events);
    }


//$context = new HttpContext;
//$request = $request->withAttribute('context', $context);
//$context->setRequest($request);

    public function testProgressStatus(): void
    {
        $request = new ServerRequest([], [], '/');
        $progressService = new LayerStatusProgress;

        $this->events->on($this->router::EVENT_BEFORE_HANDLE, [$progressService, 'withActiveLayers']);
        $this->events->on(Runner::EVENT_BEFORE_NEXT, [$progressService, 'setProgressLayer']);
        $this->events->on(Runner::EVENT_AFTER_NEXT, [$progressService, 'setCompleteLayer']);

        /** @var JsonResponse $response */
        $response = $this->router
            ->use(function (ServerRequestInterface $request, RequestHandlerInterface $next) {
                /** @var JsonResponse $response */
                $response = $next->handle($request);
                $activeLayers = $request->getAttribute('context')->getState('router.layers.active');
                return $response->withPayload(['activeLayers' => $activeLayers]);
            })
            ->get('/', function (ServerRequestInterface $request, $next) {
                return new JsonResponse(['activeLayers' => []]);
            }, 'main-page')
            ->use(function (ServerRequestInterface $request, RequestHandlerInterface $next) {
                return $next->handle($request);
            }, '/not-active-layer', 'bad-md')
            ->use(function ($request, $next) {
                return new JsonResponse(['status' => 'otherwise']);
            })
            ->handle($request);

        $this->assertCount(3, $response->getPayload()['activeLayers']);
        $this->assertSame([
            0 => [
                'name' => 'layer-0',
                'type' => 'middleware',
                'regexp' => null,
                'status' => 'progress'
            ],
            1 =>[
                'name' => 'main-page',
                'type' => 'route',
                'regexp' => '/',
                'status' => 'complete'
            ],
            2 => [
                'name' => 'layer-3',
                'type' => 'middleware',
                'regexp' => null,
                'status' => 'pending'
            ]
        ], $response->getPayload()['activeLayers']);
    }
}
