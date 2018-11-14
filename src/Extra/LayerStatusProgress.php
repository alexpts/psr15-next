<?php
declare(strict_types=1);

namespace PTS\NextRouter\Extra;

use Psr\Http\Message\ServerRequestInterface;
use PTS\NextRouter\Layer;
use PTS\NextRouter\Runner;

class LayerStatusProgress
{

    public function withActiveLayers(ServerRequestInterface $request, array $allowLayers): void
    {
        $context = $request->getAttribute('context');
        $context->replaceState('router.layers.active', array_map(function(Layer $layer) {
            return [
                'name' => $layer->name,
                'type' => $layer->meta['type'] ?? Layer::TYPE_MIDDLEWARE,
                'regexp' => $layer->regexp,
                'status' => 'pending'
            ];
        }, $allowLayers));
    }

    public function setProgressLayer(ServerRequestInterface $request, Runner $runner): HttpContext
    {
        $currentLayer = $runner->getCurrentLayer();

        /** @var HttpContext $context */
        $context = $request->getAttribute('context');
        return $context->replaceState('router.layers.active', array_map(function(array $layer) use ($currentLayer) {
            $layer['status'] = $currentLayer->name === $layer['name'] ? 'progress' : $layer['status'];
            return $layer;
        }, $context->getState('router.layers.active')));
    }

    public function setCompleteLayer(ServerRequestInterface $request, Runner $runner): HttpContext
    {
        $currentLayer = $runner->getCurrentLayer();

        /** @var HttpContext $context */
        $context = $request->getAttribute('context');
        return $context->replaceState('router.layers.active', array_map(function(array $layer) use ($currentLayer) {
            $layer['status'] = $currentLayer->name === $layer['name'] ? 'complete' : $layer['status'];
            return $layer;
        }, $context->getState('router.layers.active')));
    }
}