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

    }
}
