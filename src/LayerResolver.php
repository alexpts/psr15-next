<?php

namespace PTS\NextRouter;

use Psr\Http\Message\RequestInterface;

class LayerResolver
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

    public function filterMatchMethod(Layer $layer, RequestInterface $request): bool
    {
        $method = $request->getMethod();
        return $this->isAllowMethod($layer, $method);
    }

    public function matchLayer(Layer $layer, RequestInterface $request): ?Layer
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

    public function isAllowMethod(Layer $layer, string $method): bool
    {
        $allows = $layer->methods;
        return \count($allows) ? \in_array($method, $allows, true) : true;
    }

    /**
     * @param Layer[] $layers
     * @param RequestInterface $request
     * @param bool $checkMethod
     *
     * @return array
     */
    public function findActiveLayers(array $layers, RequestInterface $request, bool $checkMethod = true): array
    {
        $activeLayers = array_filter($layers, function(Layer $layer) use($checkMethod, $request) {
            return $this->isActiveLayer($layer, $request, $checkMethod);
        });

        return array_values($activeLayers);
    }

    /**
     * @param Layer[] $layers
     * @param string $name
     *
     * @return Layer|null
     */
    public function findLayerByName(array $layers, string $name): ?Layer
    {
        foreach ($layers as $layer) {
            if ($layer->path && $layer->name === $name) {
                return $layer;
            }
        }

        return null;
    }

    public function isActiveLayer(Layer $layer, RequestInterface $request, $checkMethod = true): bool
    {
        if ($checkMethod && !$this->filterMatchMethod($layer, $request)) {
            return false;
        }

        if ($layer->path === null) {
            return true;
        }

        return null !== $this->matchLayer($layer, $request);
    }
}
