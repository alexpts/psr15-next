<?php
declare(strict_types=1);

namespace PTS\NextRouter\Extra;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use PTS\NextRouter\Layer;
use PTS\NextRouter\LayerResolver;
use PTS\NextRouter\Next;
use PTS\NextRouter\StoreLayers;
use Zend\Diactoros\Response;

class OptionsMiddleware implements MiddlewareInterface
{
    protected StoreLayers $store;
    protected LayerResolver $resolver;

    public function __construct(Next $app)
    {
        $this->store = $app->getStoreLayers();
        $this->resolver = $this->store->getResolver();
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $next): ResponseInterface
    {
        $supportMethods = [];

        if ($request->getMethod() === 'OPTIONS') {
            $supportMethods = $this->getSupportMethods($request);
        }

        if ($supportMethods) {
            $response = new Response;
            return $response->withHeader('Access-Control-Allow-Methods', implode(', ', $supportMethods));
        }

        return $next->handle($request);
    }

    protected function getSupportMethods($request): array
    {
        $activeLayers = $this->findActiveLayersWithoutHttpMethodCheck($request);
        $supportMethods = array_reduce($activeLayers, function(array $acc, Layer $layer) {
            if ($layer->type === Layer::TYPE_ROUTE && $layer->methods) {
                array_push($acc, ...$layer->methods);
            }

            return $acc;
        }, []);

        $supportMethods = array_unique($supportMethods);
        if ($supportMethods) {
            $supportMethods[] = 'OPTIONS';
        }

        return $supportMethods;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return Layer[]
     */
    protected function findActiveLayersWithoutHttpMethodCheck(ServerRequestInterface $request): array
    {
        return $this->resolver->findActiveLayers($this->store->getLayers(), $request, false);
    }
}
