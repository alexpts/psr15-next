<?php
declare(strict_types=1);

namespace PTS\NextRouter;

trait FastMethods
{

    /**
     * @param string $path
     * @param callable $handler
     * @param array $options
     *
     * @return $this
     */
    public function get(string $path, callable $handler, array $options = [])
    {
        return $this->method('GET', $path, $handler, $options);
    }

    /**
     * @param string $path
     * @param callable $handler
     * @param array $options
     *
     * @return $this
     */
    public function delete(string $path, callable $handler, array $options = [])
    {
        return $this->method('DELETE', $path, $handler, $options);
    }

    /**
     * @param string $path
     * @param callable $handler
     * @param array $options
     *
     * @return $this
     */
    public function post(string $path, callable $handler, array $options = [])
    {
        return $this->method('POST', $path, $handler, $options);
    }

    /**
     * @param string $path
     * @param callable $handler
     * @param array $options
     *
     * @return $this
     */
    public function put(string $path, callable $handler, array $options = [])
    {
        return $this->method('PUT', $path, $handler, $options);
    }

    /**
     * @param string $path
     * @param callable $handler
     * @param array $options
     *
     * @return $this
     */
    public function patch(string $path, callable $handler, array $options = [])
    {
        return $this->method('PATCH', $path, $handler, $options);
    }
}
