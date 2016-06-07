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
    protected $concept;
    protected $properties = array();

    /**
     * ToDo:
     * in Optionen auslagern!
     */
    const SCHEMA_URI    = 'http://www.schema.org/';
    const SCHEMA_PREFIX = 'schema';

    public function setConcept($conceptURI)
    {
        if (strpos($conceptURI, self::SCHEMA_URI) !== false) {
            $this->concept = self::SCHEMA_PREFIX . ':' . substr($conceptURI, strlen(self::SCHEMA_URI));
        } else {
            $this->concept = $conceptURI;
        }
    }

    public function getConcept()
    {
        return $this->concept;
    }

    public function setConceptId($id)
    {
        $this->conceptId = $id;
    }

    public function getConceptId()
    {
        return $this->conceptId;
    }

    public function addProperty($predicate, $object)
    {
        $this->properties[$predicate] = $object;
    }

    public function getProperty($type)
    {
        if (array_key_exists($type, $this->properties)) {
            return $this->properties[$type];
        }
        return array();
    }

    public function getAllProperties()
    {
        return $this->properties;
    }

}
