<?php
declare(strict_types=1);

namespace PTS\NextRouter;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use PTS\NextRouter\Extra\PipeStack;

class Runner implements RequestHandlerInterface
{
	use EmitterTrait;

    public const EVENT_BEFORE_NEXT = 'router.runner.before.next';
    public const EVENT_AFTER_NEXT = 'router.runner.after.next';

    /** @var Layer[] */
    protected $layers = [];
    /** @var int */
    protected $index = 0;


    public function setLayers(array $activeLayers): void
    {
        $this->layers = $activeLayers;
    }

    /**
     * @inheritdoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $middleware = $this->getNextMiddleware();
        $request = $this->withParams($request);

		$this->emit(self::EVENT_BEFORE_NEXT, [$request, $this]);
        $this->index++;

        try {
            $response = $middleware->process($request, $this);
        } finally {
            $this->index--;
        }

		$this->emit(self::EVENT_AFTER_NEXT, [$request, $this, $response]);
        return $response;
    }

    protected function withParams(ServerRequestInterface $request): ServerRequestInterface
    {
        $layer = $this->getCurrentLayer();
        if ($layer->matches) {
            $old = $request->getAttribute('params', []);
            $request = $request->withAttribute('params', array_merge($old, $layer->matches));
        }

        return $request->withAttribute('router.current.layer', $layer);
    }

    protected function getNextMiddleware(): MiddlewareInterface
    {
        $middleware = $this->layers[$this->index]->md;
        if ($middleware instanceof PipeStack) {
            $middleware->setNext($this);
        }

        return $middleware;
    }

    public function getCurrentLayer(): Layer
    {
        return $this->layers[$this->index];
    }
}
