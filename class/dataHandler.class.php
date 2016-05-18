<?php

//check if wordpress is loaded
if (!function_exists('add_filter')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

class DataHandler
{
    protected $instance = null;

    protected function __construct()
    {

    }

    /**
     * Returns a singleton instance of DataHandler object
     * @return DataHandler
     */
    public function getInstance()
    {

    }

    /**
     * Saves a semanticTag object in the database
     * @param SemanticTag $tag
     * @return void
     */
    public function saveTag(SemanticTag $tag)
    {

    }

    /**
     * Removes a semanticTag object from the database
     * @param SemanticTag $tag
     * @return void
     */
    public function deleteTag(SemanticTag $tag)
    {

    }

    /**
     * Retrieves data based on concept ID from the database
     * @param int $conceptId
     * @return SemanticTag
     */
    public function getDataByConceptId(int $conceptId)
    {

    }

}
