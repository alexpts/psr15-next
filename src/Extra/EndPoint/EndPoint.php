<?php
declare(strict_types=1);

namespace PTS\NextRouter\Extra\EndPoint;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class EndPoint implements MiddlewareInterface
{
    /** @var string|null */
    protected $controller;
    /** @var string|null */
    protected $action;
    /** @var bool */
    protected $nextOnError = true;

    public function __construct(array $params = [])
    {
        foreach ($params as $name => $param) {
            if (property_exists($this, $name)) {
                $this->setProperty($name, $param);
            }
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $next
     *
     * @return ResponseInterface
     * @throws \BadMethodCallException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $next): ResponseInterface
    {
        try {
            $endPoint = $this->getPoint($request);
        } catch (\BadMethodCallException $exception) {
            if (!$this->nextOnError) {
                throw $exception;
            }

            return $next->handle($request);
        }

        return $endPoint($request, $next);
    }

    protected function setProperty(string $name, $value): void
    {
        $this->{$name} = $value;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return callable
     * @throws \BadMethodCallException
     */
    protected function getPoint(ServerRequestInterface $request): callable
    {
        $controller = $this->getControllerClass($request);
        $this->checkController($controller);

        $controller = new $controller;
        $action = $this->getAction($request) ?? 'index';
        $this->checkAction($controller, $action);

        return [$controller, $action];
    }

    protected function getControllerClass(ServerRequestInterface $request): string
    {
        return $this->controller ?? '';
    }

    /**
     * @param string $controller
     *
     * @throws \BadMethodCallException
     */
    protected function checkController(string $controller): void
    {
        if (!class_exists($controller)) {
            throw new \BadMethodCallException('Controller not found');
        }
    }

    /**
     * @param \object $controller
     * @param string $action
     *
     * @throws \BadMethodCallException
     */
    protected function checkAction($controller, string $action): void
    {
        if (!method_exists($controller, $action)) {
            throw new \BadMethodCallException('Action not found');
        }
    }

    protected function getAction(ServerRequestInterface $request): ?string
    {
        $matches = $request->getAttribute('params', []);
        return $matches['_action'] ?? $this->action ?? null;
    }
}
