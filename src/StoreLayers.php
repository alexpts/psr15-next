<?php
declare(strict_types=1);

namespace PTS\NextRouter;

use function count;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

class StoreLayers
{
    use FastMethods;
    use EmitterTrait;

    public const EVENT_ADD_LAYER = 'store.layers.add';

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

    protected $sorted = false;

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
        $this->emit(self::EVENT_ADD_LAYER, [$layer, $this]);

        $this->autoincrement++;
        $this->sorted = false;

        return $this;
    }

    public function use(callable $handler, array $options = []): self
    {
        $md = new CallableToMiddleware($handler);
        return $this->middleware($md, $options);
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
        if ($this->sorted === false) {
            $this->sortByPriority();
        }

        return $this->layers;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return Layer[]
     */
    public function getLayersForRequest(ServerRequestInterface $request): array
    {
        return $this->resolver->findActiveLayers($this->getLayers(), $request);
    }

    public function findLayerByName(string $name): ?Layer
    {
        return $this->resolver->findLayerByName($this->getLayers(), $name);
    }

    public function method(string $method, string $path, callable $handler, array $options = []): self
    {
        $options = array_merge(
            ['type' => Layer::TYPE_ROUTE],
            $options,
            ['path' => $path, 'method' => (array)$method]
        );
        $layer = $this->getLayerFactory()->middleware(new CallableToMiddleware($handler), $options);
        return $this->addLayer($layer);
    }

	protected function sortByPriority(): self
	{
	    if ($this->layers) {
            $sorted = [];

            foreach ($this->layers as $layer) {
                $sorted[$layer->priority][] = $layer;
            }

            ksort($sorted, SORT_NUMERIC);
            $this->layers = array_merge(...$sorted);
        }

        $this->sorted = true;
        return $this;
	}

    public function getLastLayer(): ?Layer
    {
        $layers = $this->getLayers();
        $count = count($layers);
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
