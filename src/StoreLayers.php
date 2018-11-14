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
    /** @var LayerFactory */
    protected $layerFactory;

    public function __construct(LayerResolver $resolver = null)
    {
        $this->resolver = $resolver ?? new LayerResolver;
        $this->layerFactory = new LayerFactory;
    }

    public function getResolver(): LayerResolver
    {
        return $this->resolver;
    }

    public function setPrefix(string $prefix): self
    {
        $this->layerFactory->setPrefix($prefix);
        return $this;
    }

    public function addLayer(Layer $layer): self
    {
        $layer->name = $layer->name ?? 'layer-'.$this->autoincrement;
        $layer->regexp = $this->resolver->makeRegExp($layer);
        $this->layers[] = $layer;

        $this->autoincrement++;
        return $this;
    }

    public function use(callable $handler, array $options = []): self
    {
        return $this->middleware(new CallableToMiddleware($handler), $options);
    }

    public function middleware(MiddlewareInterface $md, array $options = []): self
    {
        $layer = $this->layerFactory->middleware($md, $options);
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

    public function method(string $method, string $path, callable $handler, array $options = []): self
    {
        $options = array_merge(
            ['type' => Layer::TYPE_ROUTE],
            $options,
            ['path' => $path, 'method' => (array)$method]
        );
        $layer = $this->layerFactory->middleware(new CallableToMiddleware($handler), $options);
        return $this->addLayer($layer);
    }

    public function get(string $path, callable $handler, string $name = null): self
    {
        return $this->method('GET', $path, $handler, ['name' => $name]);
    }

    public function delete(string $path, callable $handler, string $name = null): self
    {
        return $this->method('DELETE', $path, $handler, ['name' => $name]);
    }

    public function post(string $path, callable $handler, string $name = null): self
    {
        return $this->method('POST', $path, $handler, ['name' => $name]);
    }

    public function put(string $path, callable $handler, string $name = null): self
    {
        return $this->method('PUT', $path, $handler, ['name' => $name]);
    }

    public function patch(string $path, callable $handler, string $name = null): self
    {
        return $this->method('PATCH', $path, $handler, ['name' => $name]);
    }

	public function sortByPriority(): self
	{
		usort($this->layers, function (Layer $a, Layer $b) {
			$priorityA = $a->meta['priority'] ?? 50;
			$priorityB = $b->meta['priority'] ?? 50;

			if ($priorityA === $priorityB) {
				return 0;
			}

			return $priorityA < $priorityB ? -1 : 1;
		});

		return $this;
	}

    public function getLastLayer(): ?Layer
    {
        $count = \count($this->layers);
        return $count ? $this->layers[$count -1] : null;
    }

    /**
     * @param string|null $path
     * @param callable[] $handlers
     * @param array $options
     *
     * @return $this
     */
    public function pipe(array $handlers, array $options = [], string $path = null): self
    {
        $layer = $this->layerFactory->pipe($handlers, array_merge([
            'path' => $path,
            'type' => Layer::TYPE_ROUTE,
        ], $options));

        return $this->addLayer($layer);
    }

    public function getLayerFactory(): LayerFactory
    {
        return $this->layerFactory;
    }
}
