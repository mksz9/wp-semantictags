<?php

//check if wordpress is loaded
if (!function_exists('add_filter')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

class DataHandler
{
    protected static $instance = null;

    protected function __construct()
    {}

    /**
     * Returns a singleton instance of DataHandler object
     * @return DataHandler
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * Saves a SemanticTag object in the database
     * @param SemanticTag $tag
     * @return void
     */
    public function saveTag(SemanticTag $tag, $deleteConnections = false)
    {
        $store     = SemanticTagsHelper::getARC2Store();
        $vocabular = SemanticTagsOptions::getVocabularConfiguration();

        //when tag already exists and concept changes, then remove existing entries and properties
        if (($stored = $this->loadTagByConceptId($tag->getConceptId())) && $deleteConnections) {
            //if ($stored->getConcept() != $tag->getConcept()) {
            $this->deleteTag($stored);
            //}
            /**
             * ToDo:
             * Wenn ein neuer Eintrag mit Beziehungen kommt (editing im Tagbearbeitungsmenü), dann nur Beziehungen die zum Tag konfiguriert wurden speichern - vorab alle anderen löschen
             */
        }

        //insert the concept type class
        $query = 'prefix ' . $vocabular['prefix'] . ': <' . $vocabular['uri'] . '>
                INSERT INTO <//tag> {
                <//tag#' . $tag->getConceptId() . '> rdf:Type ' . $tag->getConcept() . '}';
        $store->query($query);

        //inserting all properties of the tag
        foreach ($tag->getAllProperties() as $property_p => $property_o) {
            $o = '';
            if ($property_o['type'] == 'literal') {
                $o = '"' . $property_o['o'] . '"';
            } else if ($property_o['type'] == 'uri') {
                $o = $property_o['o'];
            }
            $query = 'prefix ' . $vocabular['prefix'] . ': <' . $vocabular['uri'] . '>
                INSERT INTO <//tag> {
                <//tag#' . $tag->getConceptId() . '> ' . $property_p . ' ' . $o . '}';

            $store->query($query);
        }
    }

/**
 * Removes a SemanticTag object from the database
 * @param SemanticTag $tag
 * @param string $property optional - whether removing only property from a tag
 * @return void
 */
    public function deleteTag(SemanticTag $tag, $property = null)
    {
        $store = SemanticTagsHelper::getARC2Store();
        $query = 'DELETE { <//tag#' . $tag->getConceptId() . '> ' . (($property != null) ? $property : '?p') . ' ?o }';
        $store->query($query);
    }

/**
 * Checks whether a conceptId is already stored
 * @param integer $conceptId
 * @return boolean
 */
    public function conceptIdExists($conceptId)
    {
        $store     = SemanticTagsHelper::getARC2Store();
        $vocabular = SemanticTagsOptions::getVocabularConfiguration();

        $query = 'prefix ' . $vocabular['prefix'] . ': <' . $vocabular['uri'] . '>
            SELECT *
            WHERE { <//tag#' . $conceptId . '> ?p ?o
            }';
        $result = $store->query($query);

        return (count($result['result']['rows']) > 0);
    }

/**
 * Loads an existing existing SemanticTag based on a conceptId
 * @param int $conceptId The ID of the concept to load
 * @return SemanticTag|boolean
 */
    public function loadTagByConceptId($conceptId)
    {
        $store     = SemanticTagsHelper::getARC2Store();
        $vocabular = SemanticTagsOptions::getVocabularConfiguration();

        //creating the new object to fill with information
        $semanticTag = new SemanticTag();

        $query = 'prefix ' . $vocabular['prefix'] . ': <' . $vocabular['uri'] . '>
            SELECT *
            WHERE { <//tag#' . $conceptId . '> ?p ?o
            }';
        $result = $store->query($query);

        //if tag not exists return false
        if (count($result['result']['rows']) <= 0) {
            return false;
        }

        //setting the conceptId in the object
        $semanticTag->setConceptId($conceptId);

        /**
         * ToDo:
         * -check type (if (is 'uri')...)
         * -what if other structures come in? more flexible!
         */
        foreach ($result['result']['rows'] as $row) {
            switch ($row['p']) {
                case 'http://www.w3.org/1999/02/22-rdf-syntax-ns#Type':
                    $semanticTag->setConcept($row['o']);
                    break;
                case 'http://www.w3.org/2000/01/rdf-schema#label':
                    $semanticTag->addProperty('rdfs:label', $row['o'], $row['o type']);
                    break;
                case 'http://www.w3.org/2000/01/rdf-schema#comment':
                    $semanticTag->addProperty('rdfs:comment', $row['o'], $row['o type']);
                    break;
                default:
                    $semanticTag->addProperty(str_replace($vocabular['uri'], $vocabular['prefix'] . ':', $row['p']), $row['o'], $row['o type']);
                    break;
            }
        }
        return $semanticTag;
    }
}
