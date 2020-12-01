<?php
declare(strict_types=1);

namespace PTS\NextRouter;

trait FastMethods
{

    public function get(string $path, callable $handler, array $options = []): static
    {
        return $this->method('GET', $path, $handler, $options);
    }

    public function delete(string $path, callable $handler, array $options = []): static
    {
        return $this->method('DELETE', $path, $handler, $options);
    }

    public function post(string $path, callable $handler, array $options = []): static
    {
        return $this->method('POST', $path, $handler, $options);
    }

    public function put(string $path, callable $handler, array $options = []): static
    {
        return $this->method('PUT', $path, $handler, $options);
    }

    public function patch(string $path, callable $handler, array $options = []): static
    {
        return $this->method('PATCH', $path, $handler, $options);
    }
}
