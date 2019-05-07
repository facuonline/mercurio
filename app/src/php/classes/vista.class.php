<?php
/**
 * Vista class
 * @package Mercurio
 * @subpackage Included classes
 * 
 * View models (vista) management and initialization
 * 
 * @var string $vistaFolder Folder name of active vista inside /vistas/ folder
 * @var string $vistaUrl URL path to active vista of app, not url inside vista json
 * @var array $vista Values of vista index in vista json
 * @var array $defaults Default values of vista in default index in vista json
 * @var string $htmlTitle 
 */

namespace Mercurio;
class Vista {
    
    public static $notices;
    private static $vistaFolder, $vistaUrl, $vista, $defaults, $htmlTitle;

    /**
     * Compile and start vista model
     */
    public static function start() {
        self::init();
        $URL = new Utils\URLHandler;
        // include vista functions
        $functions = MROVISTAS
            .'/'.self::$vistaFolder
            .'/functions.php';
        if (file_exists($functions)) include $functions;
        // include vista files
        if ($URL->getUrl()['referrer']) {
            include MROVISTAS
                .'/'.self::$vistaFolder
                .'/'.self::default('templates')
                .$URL->getUrl()['referrer'].'.php';
        } else {
            include MROVISTAS
                .'/'.self::$vistaFolder
                .'/main.php';
        }
    }

    /**
     * Reads and loads the Vista settings
     */
    private static function init() {
        $vistaFolder = self::getVista();
        if (file_exists(MROVISTAS.'/'.$vistaFolder.'/vista.json')) {
            $json = json_decode(
                file_get_contents(MROVISTAS.'/'.$vistaFolder.'/vista.json'), 
                true
            );
            self::$vistaUrl = getenv('APP_URL')
                .'app/vistas/'.$vistaFolder.'/';
            self::$vista = $json['vista'];
            self::$defaults = $json['defaults'];
            self::$htmlTitle = false;
        } else {
            trigger_error("VISTA FAILURE: Required file vista.json is not at current vista main folder:\n$vistaFolder\n");
            die();
        }
    }
    
    /**
     * Get vista folder name
     * @return string
     */
    public static function getVista(){
        if (getenv('APP_VISTA')) {
            self::$vistaFolder = getenv('APP_VISTA');
            return self::$vistaFolder;
        } else {
            if (glob(MROVISTAS.'/*', GLOB_ONLYDIR)[0]) {
                $vista = glob(MROVISTAS.'/*', GLOB_ONLYDIR)[0];
                putenv("APP_VISTA=$vista");
                self::getVista();
            } else {
                trigger_error("VISTA FAILURE: No view model (vista) folder found on app/vistas directory.\nMercurio requires a vista model in order to work.");
                die();
            }
        }
    }

    public static function getVistaUrl(){
        return self::$vistaUrl;
    }

    /**
     * Get default values of Vista
     * @param string $setting Name of default setting
     * @param string $subsetting Optional name for nested values
     * @return false|string
     */
    public static function default(string $setting, string $subsetting = '') {
        if (array_key_exists($setting, self::$defaults)) {
            if (!empty($subsetting)
            && array_key_exists($subsetting, self::$defaults[$setting])) {
                return self::$defaults[$setting][$subsetting];
            } else {
                return self::$defaults[$setting];
            }
        } else {
            return false;
        }
    }

    /**
     * Sets html headers
     * @param array $tags Associative array to configurate following:
     *  UTF charset
     *  Default title value
     *  CSS stylesheet location
     *  JS functions 
     */
    public static function htmlHead(array $tags = []) {
        // load default values to fill gaps
        $defaults = [
            'utf' => 'utf-8',
            'title' => 'My Mercurio app',
            'css' => 'styles.css',
            'js' => 'app.js'
        ];
        foreach ($defaults as $key => $value) {
            if (!array_key_exists($key, $tags)) {
                $tags[$key] = $value;
            }
        }
        // store to easier variables
        foreach ($tags as $key => $value) {
            $$key = $value;
        }
        $css = self::$vistaUrl.$css;
        $js = self::$vistaUrl.$js;
        // make dynamic title loading
        if (self::$htmlTitle) {
            $title = self::$htmlTitle;
        } else {
            $title = $tags['title'];
        }
        echo "<meta charset=$utf>
        <meta http-equiv='X-UA-Compatible' content='IE=edge'>
        <title>$title</title>
        <meta name='viewport' content='width=device-width, initial-scale=1'>
        <link rel='stylesheet' type='text/css' media='screen' href='$css'>
        <script src='$js'></script>";
    }

    /**
     * Set a title
     * @param string $title Title to be displayed
     */
    public static function htmlTitle(string $title) {
        self::$htmlTitle = $title;
    }
}