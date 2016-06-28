<?php

//check if wordpress is loaded
if (!function_exists('add_filter')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

class SearchDataHandler extends DataHandler implements SearchAlgorithm
{

    /**
     * Returns a singleton instance of SearchDataHandler object
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
     * Searches in concepts on a given keyword and gives relevant concepts
     * @param string $keyword
     * @return array
     */
    public static function searchConceptsByKeyword($keyword)
    {
        $store     = SemanticTagsHelper::getARC2Store();
        $vocabular = SemanticTagsOptions::getVocabularConfiguration();

        $returnConcepts = array();

        //lookup for the keyword
        $query = 'prefix ' . $vocabular['prefix'] . ': <' . $vocabular['uri'] . '>
                SELECT *
                WHERE { ?s ?p "' . $keyword . '"}';
        $concepts        = $store->query($query);
        $conceptsResults = $concepts['result']['rows'];

        //check if something could be found
        if (count($conceptsResults) != 0) {
            foreach ($conceptsResults as $conceptsResult) {
                //check if the result is property of a tag
                if (preg_match('@http://tag#([0-9]*)@', $conceptsResult['s'], $matches)) {
                    //the tag ID (concept ID) where the keyword is present
                    $tagId = $matches[1];
                    //make a query for all relational concepts of this found concept
                    $query = 'prefix ' . $vocabular['prefix'] . ': <' . $vocabular['uri'] . '>
                        SELECT *
                        WHERE { <//tag#' . $tagId . '> ?p ?o
                        }';
                    $relations       = $store->query($query);
                    $relationResults = $relations['result']['rows'];
                    //store all relational concept ID's in array
                    foreach ($relationResults as $relationResult) {
                        if ($relationResult['o type'] == 'uri' && (preg_match('@http://tag#([0-9]*)@', $relationResult['o'], $matches))) {
                            $returnConcepts[] = $matches[1];
                        }
                    }
                }
            }
            //return all concepts - duplicates may happen, that's achieved
            return $returnConcepts;
        }

    }

}
