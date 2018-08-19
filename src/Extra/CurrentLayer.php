<?php
declare(strict_types=1);

namespace PTS\NextRouter\Extra;

use Psr\Http\Message\ServerRequestInterface;
use PTS\NextRouter\Runner;

class CurrentLayer
{
    public function setCurrentLayer(ServerRequestInterface $request, Runner $runner): void
    {
        /** @var HttpContext $context */
        $context = $request->getAttribute('context');
        $context->replaceState('router.layers.current', $runner->getCurrentLayer());
    }
}