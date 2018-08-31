<?php
declare(strict_types=1);

namespace PTS\NextRouter;

use Psr\Http\Server\MiddlewareInterface;
use PTS\NextRouter\Extra\EndPoint\DynamicPoint;
use PTS\NextRouter\Extra\EndPoint\EndPoint;
use PTS\NextRouter\Extra\PipeStack;

class LayerFactory
{
    /** @var string */
    protected $prefix = '';

    public function setPrefix(string $prefix = ''): void
    {
        $this->prefix = $prefix;
    }

    public function callable(callable $handler, array $options = []): Layer
    {
        return $this->middleware(new CallableToMiddleware($handler), $options);
    }

    public function middleware(MiddlewareInterface $md, array $options = []): Layer
    {
        $name = $options['name'] ?? null;
        $path = $options['path'] ?? null;
        $priority = $options['priority'] ?? 50;
        $method = $options['method'] ?? [];
        $type = $options['type'] ?? Layer::TYPE_MIDDLEWARE;

        return $this->makeLayer($md, $path, $name)
            ->setPriority($priority)
            ->setType($type)
            ->setMethods((array)$method);
    }

    public function makeLayer(MiddlewareInterface $md, string $path = null, string $name = null): Layer
    {
        return new Layer($this->getFullPath($path), $md, $name);
    }

    public function endPoint(array $params, array $options = []): Layer
    {
        $endpoint = new EndPoint($params);
        return $this->middleware($endpoint, array_merge(['type' => Layer::TYPE_ROUTE], $options));
    }

    public function dynamicEndPoint(array $params, array $options = []): Layer
    {
        $endpoint = new DynamicPoint($params);
        return $this->middleware($endpoint, array_merge(['type' => Layer::TYPE_ROUTE], $options));
    }

    /**
     * @param callable[] $handlers
     * @param array $options
     *
     * @return Layer
     */
    public function pipe(array $handlers, array $options = []): Layer
    {
        $pipe = new PipeStack;
        foreach ($handlers as $handler) {
            $pipe->add(new CallableToMiddleware($handler));
        }

        return $this->middleware($pipe, $options);
    }

    protected function getFullPath(string $path = null): ?string
    {
        return null === $path ? null : $this->prefix.$path;
    }
}
