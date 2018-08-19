<?php
declare(strict_types=1);

namespace PTS\NextRouter;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use PTS\Events\EventsInterface;

class Runner implements RequestHandlerInterface
{
    public const EVENT_BEFORE_NEXT = 'router.runner.before.next';
    public const EVENT_AFTER_NEXT = 'router.runner.after.next';

    /** @var Layer[] */
    protected $layers = [];
    /** @var EventsInterface */
    protected $events;
    /** @var int */
    protected $index = 0;

    /**
     * @param EventsInterface $events
     */
    public function __construct(EventsInterface $events)
    {
        $this->events = $events;
    }

    public function setLayers(array $activeLayers): void
    {
        $this->layers = $activeLayers;
    }

    /**
     * @inheritdoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $layer = $this->layers[$this->index];

        $this->events->emit(self::EVENT_BEFORE_NEXT, [$request, $this]);

        $this->index++;
        $response = $layer->md->process($request, $this);
        $this->index--;

        $this->events->emit(self::EVENT_AFTER_NEXT, [$request, $this, $response]);

        return $response;
    }

    public function getCurrentLayer(): Layer
    {
        return $this->layers[$this->index];
    }
}