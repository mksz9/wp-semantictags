<?php

//check if wordpress is loaded
if (!function_exists('add_filter')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

class SemanticTagsApp
{
    /**
     * The main routine of the plugin
     * @return void
     */
    public static function main()
    {
        //load language
        add_action('plugins_loaded', array(
            'SemanticTagsApp',
            'semanticTagsLoadTextdomain',
        ));
    }

    /**
     * Loads the language files
     * @return void
     */
    public static function semanticTagsLoadTextdomain()
    {
        load_plugin_textdomain('semantictags', false, dirname(plugin_basename(SEMANTICTAGS_FILE)) . '/languages');
    }

}
