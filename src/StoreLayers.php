<?php
declare(strict_types=1);

namespace PTS\NextRouter;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

class StoreLayers
{
    /** @var LayerResolver */
    protected $resolver;
    /** @var Layer[] */
    protected $layers = [];
    /** @var int */
    protected $autoincrement = 0;
    /** @var LayerFactory */
    protected $layerFactory;
    /** @var string */
    protected $prefix = '';

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
        $this->prefix = $prefix;
        return $this;
    }

    public function addLayer(Layer $layer): self
    {
        $this->layers[] = $this->normalizerLayer($layer);
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

    public function get(string $path, callable $handler, array $options = []): self
    {
        return $this->method('GET', $path, $handler, $options);
    }

    public function delete(string $path, callable $handler, array $options = []): self
    {
        return $this->method('DELETE', $path, $handler, $options);
    }

    public function post(string $path, callable $handler, array $options = []): self
    {
        return $this->method('POST', $path, $handler, $options);
    }

    public function put(string $path, callable $handler, array $options = []): self
    {
        return $this->method('PUT', $path, $handler, $options);
    }

    public function patch(string $path, callable $handler, array $options = []): self
    {
        return $this->method('PATCH', $path, $handler, $options);
    }

	public function sortByPriority(): self
	{
		usort($this->layers, function (Layer $a, Layer $b) {
			$priorityA = $a->priority ?? 50;
			$priorityB = $b->priority ?? 50;

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

	protected function normalizerLayer(Layer $layer): Layer
	{
		$layer->path = $this->getFullPath($layer->path);
		$layer->name = $layer->name ?? 'layer-'.$this->autoincrement;
		$layer->regexp = $this->resolver->makeRegExp($layer);
		return $layer;
	}

	protected function getFullPath(string $path = null): ?string
	{
		return null === $path ? null : $this->prefix.$path;
	}
}
