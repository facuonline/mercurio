<?php
/**
 * MroVista class
 * @package Mercurio
 * @subpackage Included classes
 * 
 * View (vista) models management and initialization
 * 
 * @var string $vistaFolder Folder name of active vista inside /vistas/ folder
 */

class utils_MroVista extends MroDB {
    private $vistaFolder, $vista, $defaults;

    public function __construct() {
        $this->conn();
        $this->vistaFolder = $this->getConfig('vistaActive');
        $this->init();
    }

    /**
     * Reads and loads the Vista settings
     * 
     */
    private function init() {
        if (file_exists(MROVISTAS.'/'.$vistaFolder.'/vista.json')) {
            $json = json_decode(file_get_contents(MROVISTAS.'/'.$vistaFolder.'/vista.json'));
            $this->vista = $json['vista'];
            $this->defaults = $json['defaults'];
        } else {
            throw new Exception("VISTA FAILURE: Required file vista.json is not at current vista main folder.", 1);
            die();
        }
    }
    
    /**
     * Get default values of Vista
     * @param string $setting Name of default setting
     * @return mixed
     */
    public function default(string $setting) {
        return $this->defaults[$setting];
    }

}