<?php

namespace Socodo\Framework\Facades;

use Socodo\Http\Enums\HttpMethods;
use Socodo\Router\RouteCollection;

class Route extends FacadeAbstract
{
    /** @var RouteCollection Route collection instance. */
    protected RouteCollection $collection;

    /**
     * Constructor.
     *
     * @param RouteCollection $collection
     */
    public function __construct (RouteCollection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * Add route.
     *
     * @param HttpMethods|array<HttpMethods> $method
     * @param string $path
     * @param mixed $controller
     * @return void
     */
    protected function add (HttpMethods|array $method, string $path, mixed $controller): void
    {
        $route = new \Socodo\Router\Route($method, $path, $controller);
        $this->collection->add($route);
    }

    /**
     * Add GET route.
     *
     * @param string $path
     * @param mixed $controller
     * @return void
     */
    protected function get (string $path, mixed $controller): void
    {
        $this->add(HttpMethods::GET, $path, $controller);
    }

    /**
     * Add POST route.
     *
     * @param string $path
     * @param mixed $controller
     * @return void
     */
    protected function post (string $path, mixed $controller): void
    {
        $this->add(HttpMethods::POST, $path, $controller);
    }

    /**
     * Add PUT route.
     * 
     * @param string $path
     * @param mixed $controller
     * @return void
     */
    protected function put (string $path, mixed $controller): void
    {
        $this->add(HttpMethods::PUT, $path, $controller);
    }

    /**
     * Add PATCH route.
     *
     * @param string $path
     * @param mixed $controller
     * @return void
     */
    protected function patch (string $path, mixed $controller): void
    {
        $this->add(HttpMethods::PATCH, $path, $controller);
    }

    /**
     * Add DELETE route.
     *
     * @param string $path
     * @param mixed $controller
     * @return void
     */
    protected function del (string $path, mixed $controller): void
    {
        $this->add(HttpMethods::DEL, $path, $controller);
    }
}