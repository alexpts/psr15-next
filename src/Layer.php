<?php
declare(strict_types=1);

namespace PTS\NextRouter;

use Psr\Http\Server\MiddlewareInterface;

class Layer
{

    public const TYPE_MIDDLEWARE = 'middleware';
    public const TYPE_ROUTE = 'route';

    /** @var string|null */
    public $path;
    /** @var MiddlewareInterface */
    public $md;

    /** @var array */
    public $methods = [];
    /** @var array */
    public $matches = [];
    /** @var array */
    public $restrictions = [];
    /** @var string */
    public $type = self::TYPE_MIDDLEWARE;
    /** @var string|null */
    public $name;
    /** @var string - regexp от path */
    public $regexp;

    public function __construct(?string $path, MiddlewareInterface $md, string $name = null)
    {
        $this->path = $path;
        $this->name = $name;
        $this->md = $md;
    }

    public function setMethods(array $methods = []): self
    {
        $this->methods = $methods;
        return $this;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function makeRegExp(LayerResolver $resolver): self
    {
        $this->regexp = $resolver->makeRegExp($this);
        return $this;
    }

    public function setRestrictions(array $restrictions): self
    {
        $this->restrictions = $restrictions;
        return $this;
    }

    public function __clone()
    {
        $this->matches = [];
    }
}