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
     * Setter for the concept class (URI) of the SemanticTag object
     * @param string $conceptURI
     * @return void
     */
    public function setConcept($conceptURI)
    {
        $vocabular = SemanticTagsOptions::getVocabularConfiguration();

        //check whether the conceptURI comes prefixed or full descriptive and convert before storing
        if (strpos($conceptURI, $vocabular['uri']) !== false) {
            $this->concept = $vocabular['prefix'] . ':' . substr($conceptURI, strlen($vocabular['uri']));
        } else {
            $this->concept = $conceptURI;
        }
    }

    /**
     * Getter of concept URI
     * @return string
     */
    public function getConcept()
    {
        return $this->concept;
    }

    /**
     * Setter of ConceptId
     * @param int $id
     * @return void
     */
    public function setConceptId($id)
    {
        $this->conceptId = $id;
    }

    /**
     * Getter of ConceptId
     * @return int
     */
    public function getConceptId()
    {
        return $this->conceptId;
    }

    /**
     * Adds a property to the current SemanticTag object
     * @param string $predicate
     * @param string $object
     * @return void
     */
    public function addProperty($predicate, $object)
    {
        $this->properties[$predicate] = $object;
    }

    /**
     * Returns the value of a given property type
     * @param string $type
     * @return array
     */
    public function getProperty($type)
    {
        if (array_key_exists($type, $this->properties)) {
            return $this->properties[$type];
        }
        return array();
    }

    /**
     * Returns all given property of the current SemanticTag object
     * @return array
     */
    public function getAllProperties()
    {
        return $this->properties;
    }

}
