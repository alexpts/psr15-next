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
use Zend\Diactoros\Response;

class OptionsMiddleware implements MiddlewareInterface
{
    /** @var Layer[]  */
    protected $layers;
    /** @var LayerResolver */
    protected $resolver;

    public function __construct(Next $app)
    {
        $this->layers = $app->getStoreLayers()->getLayers();
        $this->resolver = $app->getStoreLayers()->getResolver();
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $next): ResponseInterface
    {
        $activeLayers = $this->findActiveLayersWithoutHttpMethodCheck($request);
        $supportMethods = array_reduce($activeLayers, function(array $acc, Layer $layer) {
        	if ($layer->type === Layer::TYPE_ROUTE) {
                $acc = array_merge($acc, $layer->methods);
            }

            return $acc;
        }, ['OPTIONS']);

        $supportMethods = array_unique($supportMethods);
        if (\count($supportMethods) <= 1) {
            return $next->handle($request);
        }

        $response = new Response;
        return $response->withHeader('Access-Control-Allow-Methods', implode(', ', $supportMethods));
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return Layer[]
     */
    protected function findActiveLayersWithoutHttpMethodCheck(ServerRequestInterface $request): array
    {
        return $this->resolver->findActiveLayers($this->layers, $request, false);
    }
}
