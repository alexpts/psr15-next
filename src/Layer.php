<?php
declare(strict_types=1);

namespace PTS\NextRouter;

use Psr\Http\Server\MiddlewareInterface;

class Layer
{

    public const TYPE_MIDDLEWARE = 2;
    public const TYPE_ROUTE = 1;

    public ?string $path;
    public MiddlewareInterface $md;

    /** @var string|null */
    public ?string $name = '';

    public array $methods = [];
    public array $matches = [];
    public array $restrictions = [];

    /** @var string - regexp от path */
    public ?string $regexp = '';

	public int $priority = 50;
	public int $type = self::TYPE_MIDDLEWARE;

	public array $context = [];

    public function __construct(?string $path, MiddlewareInterface $md)
    {
        $this->path = $path;
        $this->md = $md;
    }

    public function __clone()
    {
        $this->matches = [];
    }
}
