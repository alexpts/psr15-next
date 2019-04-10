<?php
declare(strict_types=1);

namespace PTS\NextRouter;

use Psr\Http\Server\MiddlewareInterface;

class Layer
{

    public const TYPE_MIDDLEWARE = 2;
    public const TYPE_ROUTE = 1;

    /** @var string|null */
    public $path;
    /** @var MiddlewareInterface */
    public $md;

    /** @var string|null */
    public $name;

    /** @var array */
    public $methods = [];
    /** @var array */
    public $matches = [];
    /** @var array */
    public $restrictions = [];

    /** @var string - regexp от path */
    public $regexp = '';

	/** @var int */
	public $priority = 50;
	/** @var int */
	public $type = self::TYPE_MIDDLEWARE;

	/** @var array */
	public $context = [];

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
