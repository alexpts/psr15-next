<?php
declare(strict_types=1);

namespace PTS\NextRouter\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function call_user_func;

class LazyCallableToMiddleware implements MiddlewareInterface
{
    /** @var callable */
    protected $realHandler;

    protected string $class;
    protected ?string $action = null;

    public function __construct(string $class, ?string $action = null)
    {
        $this->class = $class;
        $this->action = $action;
    }

    protected function getRealHandler(): callable
    {
        if (!$this->realHandler) {
            $instance = new $this->class;
            $this->realHandler = $this->action ? [$instance, $this->action] : $instance;
            unset($this->class, $this->action);
        }

        return $this->realHandler;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $next): ResponseInterface
    {
        return call_user_func($this->getRealHandler(), $request, $next);
    }
}
