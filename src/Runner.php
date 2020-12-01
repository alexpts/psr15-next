<?php
declare(strict_types=1);

namespace PTS\NextRouter;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use PTS\Events\EventBusTrait;

class Runner implements RequestHandlerInterface
{
    use EventBusTrait;

    public const EVENT_BEFORE_NEXT = 'router.runner.before.next';
    public const EVENT_AFTER_NEXT = 'router.runner.after.next';

    /** @var Layer[] */
    protected array $layers = [];
    protected int $index = 0;

    public function setLayers(array $activeLayers): void
    {
        $this->layers = $activeLayers;
    }

    public function getLayers(): array
    {
        return $this->layers;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $layer = $this->getCurrentLayer();

        $this->emit(self::EVENT_BEFORE_NEXT, [$request, $this, $layer]);
        $this->index++;

        try {
            $response = $layer->md->process($request, $this);
        } finally {
            $this->index--;
        }

        $this->emit(self::EVENT_AFTER_NEXT, [$request, $this, $response]);
        return $response;
    }

    public function getCurrentLayer(): Layer
    {
        return $this->layers[$this->index];
    }
}
