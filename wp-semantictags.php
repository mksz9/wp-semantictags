<?php
/*
Plugin Name: WP Semantic Tags
Version: 0.1
Description: Gives WordPress the ability to manage semantic informations based on the tag taxonomy
Author: Mark Schatz
Text Domain: semantictags
 */

//check if wordpress is loaded
if (!function_exists('add_filter')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

/**
 * Defining of some constants which are used in the plugin
 */

//Plugin main file:
if (!defined('SEMANTICTAGS_FILE')) {
    define('SEMANTICTAGS_FILE', __FILE__);
}

//Plugin path:
if (!defined('SEMANTICTAGS_PATH')) {
    define('SEMANTICTAGS_PATH', plugin_dir_path(SEMANTICTAGS_FILE));
}

//Plugin name:
if (!defined('SEMANTICTAGS_PLUGIN_NAME')) {
    define('SEMANTICTAGS_PLUGIN_NAME', 'semantictags');
}

/**
 * Autoloading of all used classes and interfaces
 */

function semantictags_auto_load($class)
{
    static $classes = null;

    if ($classes === null) {
        $classes = array(
            'ARC2'                => SEMANTICTAGS_PATH . 'library/arc2/ARC2.php',
            'ConceptTypeChecker'  => SEMANTICTAGS_PATH . 'class/ConceptTypeChecker.class.php',
            'DataHandler'         => SEMANTICTAGS_PATH . 'class/DataHandler.class.php',
            'Property'            => SEMANTICTAGS_PATH . 'class/Property.class.php',
            'PropertyTypeChecker' => SEMANTICTAGS_PATH . 'class/PropertyTypeChecker.class.php',
            'SearchDataHandler'   => SEMANTICTAGS_PATH . 'class/SearchDataHandler.class.php',
            'SemanticTag'         => SEMANTICTAGS_PATH . 'class/SemanticTag.class.php',
            'SemanticTagsApp'     => SEMANTICTAGS_PATH . 'class/SemanticTagsApp.class.php',
            'SemanticTagsHelper'  => SEMANTICTAGS_PATH . 'class/SemanticTagsHelper.class.php',
            'SemanticTagsSetup'   => SEMANTICTAGS_PATH . 'class/SemanticTagsSetup.class.php',
            'TypeChecker'         => SEMANTICTAGS_PATH . 'interface/TypeChecker.interface.php',
            'SemanticTagsEnums'   => SEMANTICTAGS_PATH . 'interface/SemanticTagsEnums.interface.php',
        );
    }

    if (isset($classes[$class])) {
        require_once $classes[$class];
    }
}

if (function_exists('spl_autoload_register')) {
    spl_autoload_register('semantictags_auto_load');
}

/**
 * Calling the plugin
 */

SemanticTagsApp::main();
