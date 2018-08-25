<?php
declare(strict_types=1);

namespace PTS\NextRouter;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use PTS\NextRouter\Extra\PipeStack;

class StoreLayers
{
    /** @var LayerResolver */
    protected $resolver;
    /** @var array */
    protected $layers = [];
    /** @var int */
    protected $autoincrement = 0;
    /** @var string */
    protected $prefix = '';

    public function __construct(LayerResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public function setPrefix(string $prefix): self
    {
        $this->prefix = $prefix;
        return $this;
    }

    public function addLayer(Layer $layer): self
    {
        $layer->name = $layer->name ?? 'layer-'.$this->autoincrement;
        $layer->makeRegExp($this->resolver);
        $this->layers[] = $layer;

        $this->autoincrement++;
        return $this;
    }

    public function use(callable $handler, string $path = null, string $name = null): self
    {
        $this->middleware(new CallableToMiddleware($handler), $path, $name);
        return $this;
    }

    public function middleware(MiddlewareInterface $md, string $path = null, string $name = null): self
    {
        $layer = $this->makeLayer($md, $path, $name)->setType('middleware');
        $this->addLayer($layer);

        return $this;
    }

    /**
     * @return Layer[]
     */
    public function getLayers(): array
    {
        return $this->layers;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return Layer[]
     */
    public function getLayersForRequest(ServerRequestInterface $request): array
    {
        return $this->resolver->findActiveLayers($this->layers, $request);
    }

    public function findLayerByName(string $name): ?Layer
    {
        foreach ($this->layers as $layer) {
            if ($layer->path && $layer->name === $name) {
                return $layer;
            }
        }

        return null;
    }

    public function method(string $method, string $path, callable $handler, string $name = null): self
    {
        $md = new CallableToMiddleware($handler);
        $layer = $this->makeLayer($md, $path, $name)->setType('route')->setMethods([$method]);
        return $this->addLayer($layer);
    }

    public function get(string $path, callable $handler, string $name = null): self
    {
        return $this->method('GET', $path, $handler, $name);
    }

    public function delete(string $path, callable $handler, string $name = null): self
    {
        return $this->method('DELETE', $path, $handler, $name);
    }

    public function post(string $path, callable $handler, string $name = null): self
    {
        return $this->method('POST', $path, $handler, $name);
    }

    public function put(string $path, callable $handler, string $name = null): self
    {
        return $this->method('PUT', $path, $handler, $name);
    }

    public function patch(string $path, callable $handler, string $name = null): self
    {
        return $this->method('PATCH', $path, $handler, $name);
    }

    public function makeLayer(MiddlewareInterface $md, string $path = null, string $name = null): Layer
    {
        return new Layer($this->getFullPath($path), $md, $name);
    }

    /**
     * @param string $path
     * @param callable[] $handlers
     * @param string $method
     * @param string|null $name
     *
     * @return $this
     */
    public function pipeMethod(string $method, string $path, array $handlers, string $name = null): self
    {
        $pipe = new PipeStack;
        foreach ($handlers as $handler) {
            $pipe->add(new CallableToMiddleware($handler));
        }

        $layer = $this->makeLayer($pipe, $path, $name)->setType('route')->setMethods([$method]);
        return $this->addLayer($layer);
    }

    protected function getFullPath(string $path = null): ?string
    {
        return null === $path ? null : $this->prefix.$path;
    }
}
