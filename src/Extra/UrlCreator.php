<?php
declare(strict_types=1);

namespace PTS\NextRouter\Extra;

use PTS\NextRouter\Layer;
use PTS\NextRouter\Next;

class UrlCreator
{
    protected Next $app;

    public function __construct(Next $app)
    {
        $this->app = $app;
    }

    public function url(string $name, array $placeholders = [], array $options = []): ?string
    {
        $layer = $this->app->getStoreLayers()->findLayerByName($name);
        return $layer ? $this->create($layer, $placeholders, $options) : null;
    }

    protected function create(Layer $layer, array $placeholders, array $options): string
    {
        $placeholders = $this->prepareUrlPlaceholder($placeholders);

        $url = str_replace(array_keys($placeholders), $placeholders, $layer->path);

        if (isset($options['query'])) {
            $url .= '?'.http_build_query($options['query']);
        }

        return $url;
    }

    protected function prepareUrlPlaceholder(array $placeholders): array
    {
        $prepared = [];

        foreach ($placeholders as $name => $value) {
            $prepared['{'.$name.'}'] = $value;
        }

        return $prepared;
    }
}