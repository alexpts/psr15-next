<?php
declare(strict_types=1);

namespace PTS\NextRouter;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

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

    protected function getFullPath(string $path = null): ?string
    {
        return null === $path ? null : $this->prefix.$path;
    }

    protected function makeLayer(MiddlewareInterface $md, string $path = null, string $name = null): Layer
    {
        return new Layer($this->getFullPath($path), $md, $name);
    }
}
