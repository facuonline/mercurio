<?php
/**
 * URL
 * @package Mercurio
 * @package Utilitary classes
 * 
 * URL handler and worker \
 * not only does rewrites but also manages paths to things and other cool things \
 * Not to be confused with parse_url()
 * 
 * @var array $htacess .htacesss file into an array
 * @var bool $mod_rewrite State of mod_rewrite module, can't make vanities without it
 */

namespace Mercurio\Utils;
class URLTest extends \Mercurio\AppTest {

    protected $htaccess, $mod_rewrite;

    /**
     * Builds and return links for specified targets
     * @param string $page Page name
     * @param mixed $target Target entity identifier, either handle or id
     * @param string $action Target action name \ 
     * Specify '+' as a target for page specific actions
     * @return string
     */
    public static function testGetLinkReturnsString(string $page = 'user', $target = '/verano', string $action = '/edit') {
        $link = [
            'page' => $page,
            'target' => $target,
            'action' => $action
        ];
        self::assertIsString(urlencode(implode('', $link)));
    }

    /**
     * Filter, read and return GET query params
     * @return array 
     */
    public static function testGetUrlParamsReturnsArray() {
        $params = [];
        if (isset($_GET['page'])
        && !empty($_GET['page'])) {
            $page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_STRING);
            if (array_key_exists($page, [
                'user' => NULL,
                'message' => NULL,
                'media' => NULL,
                'collection' => NULL,
                'search' => NULL,
                'admin' => NULL
            ])) {
                $params['page'] = $page;
            }
        } else {
            $params['page'] = false;
        }
        if (isset($_GET['action'])
        && !empty($_GET['action'])) {
            $action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING);
            $params['action'] = $action;
        } else {
            $params['action'] = false;
        }
        if (isset($_GET['target'])
        && !empty($_GET['target'])) {
            $target = filter_input(INPUT_GET, 'target', FILTER_SANITIZE_STRING);
            $params['target'] = $target;
        } else {
            $params['target'] = false;
        }
        self::assertIsIterable($params);
    }

}