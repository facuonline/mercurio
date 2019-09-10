<?php

namespace Mercurio\Utils;

/**
 * Simple HTTP requests 
 * @package Mercurio
 * @package Utilitary classes
 */
class Request {

    public function __construct() {
        
    }

    /**
     * Filter requests bodies
     * @param array $request Request array\
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
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            return $this->filterBody($_GET, INPUT_GET);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->filterBody($_POST, INPUT_POST);
        }
    }

}
