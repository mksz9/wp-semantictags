<?php

//check if wordpress is loaded
if (!function_exists('add_filter')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

class SemanticTagsHelper implements SemanticTagsEnums
{

    /**
     * Returns an instance of the ARC2 store model
     * @return ARC2_Store
     */
    public static function getARC2Store()
    {
        ob_start();
        global $wpdb;

        $config = array(
            'ns'         => array(
                'schema' => 'http://www.schema.org',
            ),
            'db_host'    => DB_HOST,
            'db_name'    => DB_NAME,
            'db_user'    => DB_USER,
            'db_pwd'     => DB_PASSWORD,
            'store_name' => $wpdb->prefix . SemanticTagsEnums::TABLE_PREFIX,
        );
        return ARC2::getStore($config);
    }

    /**
     * Returns all classes of the configured vocabulary in alphabetical order
     * @return array
     */
    public static function getVocabularyClasses()
    {
        $vocabular              = SemanticTagsOptions::getVocabularConfiguration();
        $semanticVocabularyFile = SEMANTICTAGS_PATH . SemanticTagsEnums::UPLOAD_DIR . SEMANTICTAGS_PLUGIN_NAME . '/' . $vocabular['prefix'] . '.ttl';
        //get the ARC2 parser and parse the vocabulary
        $parser = ARC2::getRDFParser();
        $parser->parse($semanticVocabularyFile);
        $triples = $parser->getTriples();
        $classes = array();
        foreach ($triples as $triple) {
            //only extract informations where predicate is declared as a RDF Type and the object is type of a RDF Class
            if ($triple['p'] == 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' && $triple['o'] == 'http://www.w3.org/2000/01/rdf-schema#Class') {
                $classes[] = $vocabular['prefix'] . ':' . substr($triple['s'], strlen($vocabular['uri']));
            }
        }
        //eliminate duplicate entries (maybe possible at some vocabularies), and sort them before returning:
        $classes = array_unique($classes);
        sort($classes);
        return $classes;
    }

    /**
     * Fetches all SemanticTags by a given post ID
     * @param integer $id
     * @return array
     */
    public static function getSemanticTagsByPostId($id)
    {
        $semanticTags = array();
        //get all the tag IDs belonging to a post:
        $tagIds = wp_get_post_tags($id, array('fields' => 'ids'));
        $dh     = DataHandler::getInstance();
        foreach ($tagIds as $id) {
            if ($dh->conceptIdExists($id)) {
                //loading information about concept from Triple-Store
                $semanticTags[] = $dh->loadTagByConceptId($id);
            }
        }
        return $semanticTags;
    }
}
