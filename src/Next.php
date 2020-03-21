<?php
declare(strict_types=1);

namespace PTS\NextRouter;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use PTS\Events\EventsInterface;
use PTS\NextRouter\Resolver\LayerResolver;
use PTS\NextRouter\Resolver\LayerResolverInterface;
use Throwable;

class Next implements RequestHandlerInterface
{
	use EmitterTrait;

    public const EVENT_BEFORE_HANDLE = 'app.before.handle';
    public const EVENT_AFTER_HANDLE = 'app.after.handle';

    protected StoreLayers $store;
    protected Runner $runner;

    public function __construct(LayerResolverInterface $resolver = null, StoreLayers $routerStore = null)
    {
        $resolver ??= new LayerResolver;
        $this->store = $routerStore ?? new StoreLayers($resolver);
        $this->runner = new Runner;
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        return $this->handle($request);
    }

    /**
     * @param EventsInterface $events
     *
     * @return $this
     */
    public function setEvents(EventsInterface $events)
    {
        $this->events = $events;
        $this->runner->setEvents($events);
        return $this;
    }

    public function getRouterStore(): StoreLayers
    {
        return $this->store;
    }

    /**
     * Merge/mount external router to current router
     *
     * @param Next $app
     * @param string|null $path
     *
     * @return $this
     */
    public function mount(Next $app, string $path = null)
    {
        foreach ($app->store->getLayers() as $layer) {
            if (null === $path) {
                $this->store->addLayer($layer);
                continue;
            }

            $clone = clone $layer;
            $clone->path = !$layer->path ? $path.'/.*' : $path.$layer->path;
            $this->store->addLayer($clone);
        }

        return $this;
    }

	/**
	 * @inheritdoc
	 *
	 * @throws Throwable
	 */
	public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $allowLayers = $this->store->getLayersForRequest($request);
        $this->runner->setLayers($allowLayers);

        $this->emit(self::EVENT_BEFORE_HANDLE, [$request, $allowLayers]);
        $response = $this->runner->handle($request);
		$this->emit(self::EVENT_AFTER_HANDLE, [$request, $allowLayers, $response]);

		return $response;
    }
}
