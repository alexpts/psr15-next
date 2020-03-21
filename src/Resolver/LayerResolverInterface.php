<?php

namespace PTS\NextRouter\Resolver;

use Psr\Http\Message\RequestInterface;
use PTS\NextRouter\Layer;

interface LayerResolverInterface
{

    public function makeRegExp(Layer $layer): ?string;

	/**
	 * You can extend this method and to add any custom filter for $layer->context,
	 * Example locale or domain
	 *
	 * @param Layer $layer
	 * @param RequestInterface $request
	 * @param bool $checkMethod
	 *
	 * @return bool
	 */
    public function isActiveLayer(Layer $layer, RequestInterface $request, $checkMethod = true): bool;
}
