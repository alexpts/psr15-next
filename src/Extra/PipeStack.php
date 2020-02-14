<?php
declare(strict_types=1);

namespace PTS\NextRouter\Extra;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function count;

class PipeStack implements MiddlewareInterface, RequestHandlerInterface
{
    /** @var MiddlewareInterface[] */
    protected array $middlewares = [];
    protected int $index = 0;
    protected RequestHandlerInterface $next;

    public function add(MiddlewareInterface $middleware): void
    {
        $this->middlewares[] = $middleware;
    }

    public function setNext(RequestHandlerInterface $next): void
    {
        $this->next = $next;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $next): ResponseInterface
    {
        return $this->handle($request);
    }

    /**
     * @inheritdoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $middleware = $this->middlewares[$this->index];
        $this->index++;

        $next = $this->index === count($this->middlewares) ? $this->next : $this;
        $response = $middleware->process($request, $next);

        $this->index--;
        return $response;
    }
}
