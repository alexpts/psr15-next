<?php
declare(strict_types=1);

namespace PTS\NextRouter;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use PTS\Events\EventsInterface;
use PTS\NextRouter\Extra\CreateUrl;
use PTS\NextRouter\Extra\HttpContext;

class Router implements RequestHandlerInterface
{
    public const EVENT_BEFORE_HANDLE = 'router.before.handle';

    /** @var LayerResolver */
    protected $resolver;
    /** @var string */
    protected $prefix = '';
    /** @var array */
    protected $layers = [];
    /** @var int */
    protected $autoincrement = 0;
    /** @var CreateUrl|null */
    protected $urlCreator;
    /** @var EventsInterface */
    protected $events;
    /** @var Runner */
    protected $runner;

    public function __construct(LayerResolver $resolver, EventsInterface $events)
    {
        $this->resolver = $resolver;
        $this->events = $events;
        $this->runner = new Runner($this->events);
    }

    public function setUrlCreator(CreateUrl $urlCreator): self
    {
        $this->urlCreator = $urlCreator;
        return $this;
    }

    public function use(callable $handler, string $path = null, string $name = null): self
    {
        return $this->middleware(new CallableToMiddleware($handler), $path, $name);
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

    public function get(string $path, callable $handler, string $name = null): self
    {
        return $this->method('GET', $path, $handler, $name);
    }

    public function delete(string $path, callable $handler, string $name = null): self
    {
        return $this->method('DELETE', $path, $handler, $name);
    }

    public function post(string $path, callable $handler, string $name = null): self
    {
        return $this->method('POST', $path, $handler, $name);
    }

    public function put(string $path, callable $handler, string $name = null): self
    {
        return $this->method('PUT', $path, $handler, $name);
    }

    public function patch(string $path, callable $handler, string $name = null): self
    {
        return $this->method('PATCH', $path, $handler, $name);
    }

    /**
     * Merge/mount external router to current router
     *
     * @param Router $router
     * @param string|null $path
     *
     * @return Router
     */
    public function mount(Router $router, string $path = null): self
    {
        foreach ($router->getLayers() as $layer) {
            if (!$path || !$layer->path) {
                $this->addLayer($layer);
                continue;
            }

            $clone = clone $layer;
            $clone->path = $path.$layer->path;
            $this->addLayer($clone);
        }

        return $this;
    }

    public function url(string $name, array $placeholders = [], array $options = []): string
    {
        if (!$this->urlCreator) {
            throw new RouterException('Need inject url creator via method `setUrlCreator`');
        }

        $layer = $this->findLayerByName($name);
        return $this->urlCreator->create($layer, $placeholders, $options);
    }

    /**
     * @return Layer[]
     */
    public function getLayers(): array
    {
        return $this->layers;
    }

    public function setPrefix(string $prefix): self
    {
        $this->prefix = $prefix;
        return $this;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $allowLayers = $this->resolver->findActiveLayers($this->layers, $request);
        $this->runner->setLayers($allowLayers);

        $request = $this->withContext($request);
        $this->events->emit(self::EVENT_BEFORE_HANDLE, [$request, $allowLayers]);
        return $this->runner->handle($request);
    }

    protected function withContext(ServerRequestInterface $request): ServerRequestInterface
    {
        $context = new HttpContext;
        $context->replaceState('router', $this);
        $request = $request->withAttribute('context', $context);
        $context->setRequest($request);
        return $request;
    }

    protected function getFullPath(string $path = null): ?string
    {
        return null === $path ? null : $this->prefix.$path;
    }

    protected function method(string $method, string $path, callable $handler, string $name = null): self
    {
        $md = new CallableToMiddleware($handler);
        $layer = $this->makeLayer($md, $path, $name)->setType('route')->setMethods([$method]);
        return $this->addLayer($layer);
    }

    protected function makeLayer(MiddlewareInterface $md, string $path = null, string $name = null): Layer
    {
        return new Layer($this->getFullPath($path), $md, $name);
    }

    protected function findLayerByName(string $name): Layer
    {
        foreach ($this->getLayers() as $layer) {
            if ($layer->path && $layer->name === $name) {
                return $layer;
            }
        }

        throw new RouterException("Layer with name {$name} not found");
    }
}
