<?php
declare(strict_types=1);

namespace PTS\NextRouter;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CallableToMiddleware implements MiddlewareInterface
{
    /** @var callable */
    protected $realHandler;

    public function __construct(callable $handler)
    {
        $this->realHandler = $handler;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $next): ResponseInterface
    {
        return \call_user_func($this->realHandler, $request, $next);
    }
}
