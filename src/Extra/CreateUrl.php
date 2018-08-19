<?php
declare(strict_types=1);

namespace PTS\NextRouter\Extra;

use PTS\NextRouter\Layer;

class CreateUrl
{

    public function create(Layer $layer, array $placeholders, array $options): string
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