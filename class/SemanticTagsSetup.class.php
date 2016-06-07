<?php

//check if wordpress is loaded
if (!function_exists('add_filter')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

class SemanticTagsSetup
{
    /**
     * Setup for the database tables
     * @return void
     */
    public static function installDatabase()
    {
        $store = SemanticTagsHelper::getARC2Store();

        if (!$store->isSetUp()) {
            $store->setUp();
        }
    }

    /**
     * Removes the database tables
     * @return void
     */
    public static function uninstallDatabase()
    {
        $store = SemanticTagsHelper::getARC2Store();
        if ($store->isSetUp()) {
            $store->drop();
        }
    }
}
