<?php

namespace Mercurio\Utils;

/**
 * HTTP Requests router middleware \
 * Non static Util
 * @package Mercurio
 * @subpackage Utilitary classes
 */
class Router {

    /**
     * Instance of \Mercurio\Utils\Request
     */
    public $request;

    public function __construct(\Mercurio\Utils\Request $request) {
        $this->request = $request;
    }

    /**
     * Resolve request with closure
     * @param string $route
     * @param callable $resolve
     */
    public function resolve($route, $resolve) {
        if ($this->request->route === $route) {
            return $resolve($this->request);
        }
    }

    /**
     * Route GET requests
     * @param string $route Route path to listen for
     * @param callable $resolve Callable function to serve on request \
     *  function (\Mercurio\Utils\Request $request) :
     */
    public function GET(string $route, callable $resolve) {
        if ($this->request->method === 'GET') return $this->resolve($route, $resolve);
    }

    /**
     * Route POST requests
     * @param string $route Route path to listen for
     * @param callable $resolve Callable function to serve on request \
     *  function (\Mercurio\Utils\Request $request) :
     */
    public function POST(string $route, callable $resolve) {
        if ($this->request->method === 'POST') return $this->resolve($route, $resolve);
    }

}
