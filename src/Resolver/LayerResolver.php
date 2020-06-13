<?php

namespace PTS\NextRouter\Resolver;

use Psr\Http\Message\RequestInterface;
use PTS\NextRouter\Layer;
use function count;
use function in_array;

class LayerResolver implements LayerResolverInterface
{

    public function makeRegExp(Layer $layer): ?string
    {
        $regexp = $layer->path;
        $placeholders = [];

        if (preg_match_all('~{(.*)}~Uu', $regexp, $placeholders)) {
            foreach ($placeholders[0] as $index => $match) {
                $name = $placeholders[1][$index];
                $replace = array_key_exists($name, $layer->restrictions) ? $layer->restrictions[$name] : '[^\/]+';
                $replace = '(?<'.$name.'>'.$replace.')';
                $regexp = str_replace($match, $replace, $regexp);
            }
        }

        return $regexp;
    }

    protected function filterIsAllowMethod(Layer $layer, RequestInterface $request): bool
    {
	    return count($layer->methods) ? in_array($request->getMethod(), $layer->methods, true) : true;
    }

    protected function matchRegexpLayer(Layer $layer, RequestInterface $request): ?Layer
    {
        $uri = $request->getUri()->getPath();
        $regexp = $layer->regexp;

        if (preg_match('~^'.$regexp.'$~Uiu', $uri, $values)) {
            $filterValues = array_filter(array_keys($values), '\is_string');
            $matches = array_intersect_key($values, array_flip($filterValues));
            $layer->matches = $matches; // side affect нормально, если воркер не асинхронный
            return $layer;
        }

        return null;
    }

    public function isActiveLayer(Layer $layer, RequestInterface $request, $checkMethod = true): bool
    {
        if ($checkMethod && !$this->filterIsAllowMethod($layer, $request)) {
            return false;
        }

	    if ($layer->path === null) {
		    return true;
	    }

        return null !== $this->matchRegexpLayer($layer, $request);
    }
}
