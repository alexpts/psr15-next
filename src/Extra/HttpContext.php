<?php
declare(strict_types=1);

namespace PTS\NextRouter\Extra;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HttpContext
{
    /** @var ServerRequestInterface */
    public $request;
    /** @var ResponseInterface|null */
    public $response;
    /** @var array */
    public $state = [];

    public function setRequest(ServerRequestInterface $request): void
    {
        $this->request = $request;
    }

    public function setResponse(ResponseInterface $response): void
    {
        $this->response = $response;
    }

    /**
     * @param string $name
     * @param mixed $state
     *
     * @return $this
     */
    public function replaceState(string $name, $state)
    {
        $this->state[$name] = $state;
        return $this;
    }

    /**
     * @param string $name
     *
     * @return mixed|null
     */
    public function getState(string $name)
    {
        return $this->state[$name] ?? null;
    }
}