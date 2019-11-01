<?php

namespace Keiryo\Routing;

trait RouteBuilderTrait
{

    /**
     * HTTP GET route helper
     *
     * @param string $path
     * @param string|callable $controller
     * @param string $name
     */
    public function get(string $path, $controller, string $name = null)
    {
        return $this->match('GET', $path, $controller, $name);
    }

    /**
     * HTTP POST route helper
     *
     * @param string $path
     * @param string|callable $controller
     * @param string $name
     */
    public function post(string $path, $controller, string $name = null)
    {
        return $this->match('POST', $path, $controller, $name);
    }

    /**
     * HTTP PUT route helper
     *
     * @param string $path
     * @param string|callable $controller
     * @param string $name
     */
    public function put(string $path, $controller, string $name = null)
    {
        return $this->match('PUT', $path, $controller, $name);
    }
    
    /**
     * HTTP DELETE route helper
     *
     * @param string $path
     * @param string|callable $controller
     * @param string $name
     */
    public function delete(string $path, $controller, string $name = null)
    {
        return $this->match('DELETE', $path, $controller, $name);
    }
    
    abstract public function match($methods, $path, $controller, $name = null);
}
