<?php
declare(strict_types=1);

namespace PTS\NextRouter;

use Psr\Http\Server\MiddlewareInterface;
use PTS\NextRouter\Extra\EndPoint\DynamicPoint;
use PTS\NextRouter\Extra\EndPoint\EndPoint;
use PTS\NextRouter\Extra\PipeStack;
use PTS\NextRouter\Resolver\LayerResolver;
use PTS\NextRouter\Resolver\LayerResolverInterface;

class LayerFactory
{

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
		$layer->priority = $options['priority'] ?? $layer->priority ;

		return $layer;
    }

    public function makeLayer(MiddlewareInterface $md, string $path = null): Layer
    {
        $layer = new Layer($path, $md);
		$layer->regexp = $this->resolver->makeRegExp($layer);

		return $layer;
    }

    public function endPoint(array $params, array $options = []): Layer
    {
        $endpoint = new EndPoint($params);
        return $this->middleware($endpoint, array_merge(['type' => Layer::TYPE_ROUTE], $options));
    }

    public function dynamicEndPoint(array $params, array $options = []): Layer
    {
        $endpoint = new DynamicPoint($params);
        return $this->middleware($endpoint, array_merge(['type' => Layer::TYPE_ROUTE], $options));
    }

    /**
     * @param callable[] $handlers
     * @param array $options
     *
     * @return Layer
     */
    public function pipe(array $handlers, array $options = []): Layer
    {
        $pipe = new PipeStack;
        foreach ($handlers as $handler) {
            $pipe->add(new CallableToMiddleware($handler));
        }

        return $this->middleware($pipe, $options);
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
        if (array_key_exists('callable', $params)) {
            $callable = $this->prepareCallable($params['callable']);
            $md = new CallableToMiddleware($callable);
        } elseif (array_key_exists('endpoint', $params)) {
            $md = new EndPoint($params['endpoint']);
        } elseif (array_key_exists('dynamicPoint', $params)) {
            $md = new DynamicPoint($params['dynamicPoint']);
        } else {
            throw new RouterException('Unknown type');
        }

        return $md;
    }

    protected function prepareCallable($handler): callable
    {
        if (is_string($handler) && !is_callable($handler)) {
            $handler = $this->symfonyCallableFormat($handler) ?? new $handler;
        } elseif (is_array($handler) && is_string($handler[0])) {
            $handler[0] = new $handler[0];
        }

        return $handler;
    }

    protected function symfonyCallableFormat($handler): ?callable
    {
        if (is_string($handler) && strpos($handler, ':')) {
            $handler = explode(':', $handler);
            $handler[0] = new $handler[0];
            return $handler;
        }

        return null;
    }
}
