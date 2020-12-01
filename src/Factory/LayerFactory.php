<?php
declare(strict_types=1);

namespace PTS\NextRouter\Factory;

use Psr\Http\Server\MiddlewareInterface;
use PTS\Events\EventBusTrait;
use PTS\NextRouter\CallableToMiddleware;
use PTS\NextRouter\Layer;
use PTS\NextRouter\Middleware\LazyCallableToMiddleware;
use PTS\NextRouter\Resolver\LayerResolver;
use PTS\NextRouter\Resolver\LayerResolverInterface;
use PTS\NextRouter\RouterException;

class LayerFactory
{
    use EventBusTrait;

    public const FILTER_CREATE_FROM_CONFIG = 'factory.layer.create.from.config';

    protected LayerResolver $resolver;

    public function __construct(LayerResolverInterface $resolver = null)
    {
        $this->resolver = $resolver ?? new LayerResolver;
    }

    public function callable(callable $handler, array $options = []): Layer
    {
        return $this->middleware(new CallableToMiddleware($handler), $options);
    }

    public function middleware(MiddlewareInterface $md, array $options = []): Layer
    {
        $path = $options['path'] ?? null;
        $method = $options['method'] ?? [];

        $layer = $this->makeLayer($md, $path);
        $layer->name = $options['name'] ?? null;
        $layer->methods = (array)$method;
        $layer->type = $options['type'] ?? Layer::TYPE_MIDDLEWARE;
        $layer->priority = $options['priority'] ?? $layer->priority;

        return $layer;
    }

    public function makeLayer(MiddlewareInterface $md, string $path = null): Layer
    {
        $layer = new Layer($path, $md);
        $layer->regexp = $this->resolver->makeRegExp($layer);

        return $layer;
    }

    public function create(array $params): Layer
    {
        $md = $this->getMiddlewareFromParams($params);
        $layer = $this->makeLayer($md, $params['path'] ?? null);

        foreach ($params as $name => $value) {
            if (property_exists($layer, $name)) {
                $layer->{$name} = $value;
            }
        }

        return $layer;
    }

    protected function getMiddlewareFromParams(array $params): MiddlewareInterface
    {
        // you can add new strategy via filter handler
        $md = $this->filter(self::FILTER_CREATE_FROM_CONFIG, null, $params);

        if (!($md instanceof MiddlewareInterface)) {
            if (array_key_exists('callable', $params)) {
                $md = new CallableToMiddleware($params['callable']);
            } elseif (array_key_exists('lazy-callable', $params)) {
                $class = $params['lazy-callable'][0];
                $action = $params['lazy-callable'][1] ?? null;
                $md = new LazyCallableToMiddleware($class, $action);
            } else {
                throw new RouterException('Can`t create layer from config');
            }
        }

        return $md;
    }
}
