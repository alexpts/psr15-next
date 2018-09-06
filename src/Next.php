<?php
declare(strict_types=1);

namespace PTS\NextRouter;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use PTS\Events\EventsInterface;

class Next implements RequestHandlerInterface
{
    public const EVENT_BEFORE_HANDLE = 'router.before.handle';

    /** @var StoreLayers */
    protected $store;
    /** @var EventsInterface|null */
    protected $events;
    /** @var Runner */
    protected $runner;

    public function __construct(LayerResolver $resolver = null)
    {
        $resolver = $resolver ?? new LayerResolver;
        $this->store = new StoreLayers($resolver);
        $this->runner = new Runner;
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        return $this->handle($request);
    }

    public function setEvents(EventsInterface $events): self
    {
        $this->events = $events;
        $this->runner->setEvents($events);
        return $this;
    }

    public function getStoreLayers(): StoreLayers
    {
        return $this->store;
    }

    /**
     * Merge/mount external router to current router
     *
     * @param Next $app
     * @param string|null $path
     *
     * @return Next
     */
    public function mount(Next $app, string $path = null): self
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

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $allowLayers = $this->store->getLayersForRequest($request);
        $this->runner->setLayers($allowLayers);

        $this->events && $this->events->emit(self::EVENT_BEFORE_HANDLE, [$request, $allowLayers]);
        return $this->runner->handle($request);
    }
}
