<?php
declare(strict_types=1);

namespace PTS\NextRouter\Extra\EndPoint;

use Psr\Http\Message\ServerRequestInterface;

class DynamicPoint extends EndPoint
{
    /** @var string */
    protected $prefix = '';

    protected function getControllerClass(ServerRequestInterface $request): string
    {
        $matches = $request->getAttribute('params', []);
        if (!array_key_exists('_controller', $matches)) {
            throw new \BadMethodCallException('Not found controller name for dynamic controller point');
        }

        return $this->prefix . $this->normalizeClassFromUrl($matches['_controller']);
    }

    protected function normalizeClassFromUrl(string $class): string
    {
        return array_reduce(explode('-', $class), function ($prev, $item) {
            return $prev . ucfirst($item);
        });
    }

    protected function getAction(ServerRequestInterface $request): ?string
    {
        return parent::getAction($request) ?? strtolower($request->getMethod());
    }
}
