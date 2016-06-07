<?php

//check if wordpress is loaded
if (!function_exists('add_filter')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

class SemanticTagsHelper implements SemanticTagsEnums
{

    const SCHEMA_PREFIX_WITHOUT_WWW = 'http://schema.org/';

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

    public static function getVocabularyClasses()
    {
        $semanticVocabularyFile = SEMANTICTAGS_PATH . '../../uploads/' . SEMANTICTAGS_PLUGIN_NAME . '/' . SemanticTagsApp::VOCABULARY_PREFIX . '.ttl';
        $parser                 = ARC2::getRDFParser();
        $parser->parse($semanticVocabularyFile);
        $triples = $parser->getTriples();
        $classes = array();
        foreach ($triples as $triple) {
            if ($triple['p'] == 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' && $triple['o'] == 'http://www.w3.org/2000/01/rdf-schema#Class') {
                /**
                 * Get information about vocabulary from options
                 */
                $classes[] = SemanticTag::SCHEMA_PREFIX . ':' . substr($triple['s'], strlen(self::SCHEMA_PREFIX_WITHOUT_WWW));
            }
        }
        $classes = array_unique($classes);
        sort($classes);
        return $classes;
    }

    /**
     * Fetches all semantic tags by a post ID
     * @param integer $id
     * @return Array
     */
    public static function getSemanticTagsByPostId($id)
    {
        $semanticTags = array();
        $tagIds       = wp_get_post_tags($id, array('fields' => 'ids'));
        $dh           = DataHandler::getInstance();

        foreach ($tagIds as $id) {
            if ($dh->conceptIdExists($id)) {
                $semanticTags[] = $dh->loadTagByConceptId($id);
            }
        }
        return $semanticTags;
    }
}
