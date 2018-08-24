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
    /** @var EventsInterface|null */
    protected $events;
    /** @var int */
    protected $index = 0;


    public function setEvents(EventsInterface $events): void
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
        $this->beforeHandle($request);

        $this->index++;
        $response = $layer->md->process($request, $this);
        $this->index--;

        $this->afterHandle($request, $response);
        return $response;
    }

    protected function beforeHandle(ServerRequestInterface $request): void
    {
        if ($this->events) {
            $this->events->emit(self::EVENT_BEFORE_NEXT, [$request, $this]);
        }
    }

    protected function afterHandle(ServerRequestInterface $request, ResponseInterface $response): void
    {
        if ($this->events) {
            $this->events->emit(self::EVENT_AFTER_NEXT, [$request, $this, $response]);
        }
    }

    public function getCurrentLayer(): Layer
    {
        return $this->layers[$this->index];
    }
}
