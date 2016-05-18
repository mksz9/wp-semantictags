<?php

//check if wordpress is loaded
if (!function_exists('add_filter')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

class SemanticTag
{
    protected $conceptId;
    protected $condept;
    protected $properties;

    /**
     * Loads an existing existing semnanticTag based on a conceptId
     * @param int $conceptId The ID of the concept to load
     * @return void
     */
    public function loadByConceptId(int $conceptId)
    {

    }

}
