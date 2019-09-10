<?php

namespace Mercurio\Utils;

/**
 * Simple HTTP requests 
 * @package Mercurio
 * @package Utilitary classes
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
        $method = $_SERVER['REQUEST_METHOD'];

        // Strip slashes and App basepath from Request URI
        $base = parse_url(\Mercurio\App::getApp('url'))['path'];
        $route = ltrim($_SERVER['REQUEST_URI'], $base);
        $route = rtrim($route, '/');
        if ($route === '') $route = '/';
        
        $this->route = $route;
    }

    /**
     * Filter requests bodies
     * @param array $request PHP Request array\
     *  $_GET, $_POST, etc
     * @param string $method filter_input() filtering type \
     *  Must go along with the request
     * @param $filter The ID of the filter to apply
     */
    public function filterBody(array $request, string $type, $filter = FILTER_DEFAULT) {
        $body = [];
        foreach ($request as $key => $value) {
            $body[$key] = filter_input($type, $key, $filter);
        }
        return $body;
    }

    /**
     * Obtain filtered body of request 
     * @return array Filtered post body
     */
    public function getBody() {
        if ($this->method === 'GET') {
            return $this->filterBody($_GET, INPUT_GET, FILTER_SANITIZE_URL);
        }

        if ($this->method === 'POST') {
            return $this->filterBody($_POST, INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
        }
    }

}
