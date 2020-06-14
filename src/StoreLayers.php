<?php
declare(strict_types=1);

namespace PTS\NextRouter;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use PTS\NextRouter\Factory\LayerFactory;
use PTS\NextRouter\Resolver\LayerResolver;
use PTS\NextRouter\Resolver\LayerResolverInterface;
use function count;

class StoreLayers
{
	use FastMethods;

	protected LayerResolverInterface $resolver;
	/** @var Layer[] */
	protected array $layers = [];
	protected int $autoincrement = 0;
	protected LayerFactory $layerFactory;
	protected string $prefix = '';

	protected bool $sorted = false;

	public function __construct(LayerResolverInterface $resolver = null)
	{
		$this->resolver = $resolver ?? new LayerResolver;
		$this->layerFactory = new LayerFactory;
	}

	public function getResolver(): LayerResolver
	{
		return $this->resolver;
	}

	/**
	 * @param string $prefix
	 *
	 * @return $this
	 */
	public function setPrefix(string $prefix)
	{
		$this->prefix = $prefix;
		return $this;
	}

	/**
	 * @param Layer $layer
	 *
	 * @return $this
	 */
	public function addLayer(Layer $layer): self
	{
		$this->layers[] = $this->normalizerLayer($layer);

		$this->autoincrement++;
		$this->sorted = false;

		return $this;
	}

	/**
	 * @param callable $handler
	 * @param array $options
	 *
	 * @return $this
	 */
	public function use(callable $handler, array $options = []): self
	{
		$md = new CallableToMiddleware($handler);
		return $this->middleware($md, $options);
	}

	/**
	 * @param MiddlewareInterface $md
	 * @param array $options
	 *
	 * @return $this
	 */
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
	 * For async application need cache for custom key
	 *
	 * @param ServerRequestInterface $request
	 *
	 * @return Layer[]
	 */
	public function getLayersForRequest(ServerRequestInterface $request): array
	{
		$activeLayers = [];
		foreach ($this->getLayers() as $i => $layer) {
			if ($this->resolver->isActiveLayer($layer, $request)) {
				$activeLayers[] = $layer;
			}
		}

		return $activeLayers;
	}

	public function findLayerByName(string $name): ?Layer
	{
		foreach ($this->getLayers() as $layer) {
			if ($layer->path && $layer->name === $name) {
				return $layer;
			}
		}

		return null;
	}

	/**
	 * @param string $method
	 * @param string $path
	 * @param callable $handler
	 * @param array $options
	 *
	 * @return $this
	 */
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

	/**
	 * @return $this
	 */
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
		return $count ? $this->layers[$count - 1] : null;
	}

	public function getLayerFactory(): LayerFactory
	{
		return $this->layerFactory;
	}

	protected function normalizerLayer(Layer $layer): Layer
	{
		$layer->path = $this->getFullPath($layer->path);
		$layer->name ??= 'layer-' . $this->autoincrement;
		$layer->regexp = $this->resolver->makeRegExp($layer);
		return $layer;
	}

	protected function getFullPath(string $path = null): ?string
	{
		return null === $path ? null : $this->prefix . $path;
	}
}
