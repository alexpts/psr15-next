<?php
declare(strict_types=1);

namespace PTS\NextRouter;

use Psr\Http\Server\MiddlewareInterface;

class Layer
{

    public const TYPE_MIDDLEWARE = 2;
    public const TYPE_ROUTE = 1;

    /** @var string|null */
    public ?string $name = '';

    public array $methods = [];
    public array $matches = [];
    public array $restrictions = [];

    public ?string $regexp = '';

    public int $priority = 50;
    public int $type = self::TYPE_MIDDLEWARE;

    public array $context = [];

    public function __construct(
        public ?string $path,
        public MiddlewareInterface $md
    ) {
    }

    public function __clone()
    {
        $this->matches = [];
    }
}
