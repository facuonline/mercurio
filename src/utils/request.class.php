<?php

namespace Mercurio\Utils;

/**
 * Simple HTTP requests \
 * Non static Util
 * @package Mercurio
 * @subpackage Utilitary classes
 */
class Request {

    /**
     * Request method
     */
    public $method;

    /**
     * Request route
     */
    public $route;

    public function __construct() {
        $this->method = $_SERVER['REQUEST_METHOD'];

        $route = $_SERVER['REQUEST_URI'];
        // Sanitize
        $route = \Mercurio\Utils\Filter::getUrl($route);
        // Strip invalid characters and App basepath from Request URI
        $base = parse_url(\Mercurio\App::getApp('url'))['path'];
        $route = ltrim($route, $base);
        $route = rtrim($route, '/');
        if ($route === '') $route = '/';

        $this->route = $route;
    }

    /**
     * Obtain body of request, unfiltered
     * @return array Request body
     */
    public function getBody() {
        if ($this->method === 'GET') return $_GET;
        if ($this->method === 'POST') return $_POST;
    }

}
