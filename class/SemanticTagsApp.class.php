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
     * ToDo
     * - keep this in options
     */
    const VOCABULARY        = 'https://ckannet-storage.commondatastorage.googleapis.com/2015-03-18T17:25:40.358Z/schema-org.ttl';
    const VOCABULARY_PREFIX = 'schema';
    /**
     * The main routine of the plugin
     * @return void
     */
    public static function main()
    {

        //check if schema file exists, if not than download to upload dir
        if (!file_exists(SEMANTICTAGS_PATH . '/../../uploads/' . SEMANTICTAGS_PLUGIN_NAME . '/' . self::VOCABULARY_PREFIX . '.ttl')) {
            if (!is_dir(SEMANTICTAGS_PATH . '/../../uploads/' . SEMANTICTAGS_PLUGIN_NAME)) {
                mkdir(SEMANTICTAGS_PATH . '/../../uploads/' . SEMANTICTAGS_PLUGIN_NAME);
            }
            $vocabulary = file_get_contents(self::VOCABULARY);
            file_put_contents(SEMANTICTAGS_PATH . '/../../uploads/' . SEMANTICTAGS_PLUGIN_NAME . '/' . self::VOCABULARY_PREFIX . '.ttl', $vocabulary);
        }

        //load language
        add_action('plugins_loaded', array(
            'SemanticTagsApp',
            'semanticTagsLoadTextdomain',
        ));

        //add headfiles if on editor
        add_action('admin_enqueue_scripts', array('SemanticTagsApp', 'addHeadfiles'));

        //hook into saving/editing posts
        add_action('save_post', array('SemanticTagsApp', 'processSemanticTags'));

        //load configured semanticTags
        add_action('dbx_post_sidebar', array('SemanticTagsApp', 'loadConfiguredTags'));

        //on plugin activation (create db tables)
        register_activation_hook(SEMANTICTAGS_FILE, array(
            'SemanticTagsSetup',
            'installDatabase',
        ));

        //on plugin deactivation (remove db tables)
        register_deactivation_hook(SEMANTICTAGS_FILE, array(
            'SemanticTagsSetup',
            'uninstallDatabase',
        ));

        /**
         * register AJAX handles
         */

        //retrieving all datatypes from vocabulary
        add_action('wp_ajax_semantictags_retrieve_datatypes', array('ConceptTypeChecker', 'getAllConcepts'));

    }

    /**
     * Loads the language files
     * @return void
     */
    public static function semanticTagsLoadTextdomain()
    {
        load_plugin_textdomain('semantictags', false, dirname(plugin_basename(SEMANTICTAGS_FILE)) . '/languages');
    }

    /**
     * Inserts Javascript and CSS files to header on backend
     * @return void
     */
    public static function addHeadfiles()
    {
        global $pagenow;
        if ('post.php' == $pagenow) {
            wp_enqueue_script('semantictags_tagconfigurationonpost_js', plugin_dir_url(SEMANTICTAGS_FILE) . 'js/tagConfiguratorOnPost.js');
            wp_enqueue_style('semantictags_tagconfigurationonpost_css', plugin_dir_url(SEMANTICTAGS_FILE) . 'css/tagConfiguratorOnPost.css');
        }
    }

    /**
     * Processes configured semanticTags after editing a post
     * @param integer $post_id
     * @return void
     */
    public static function processSemanticTags($post_id)
    {
        if (array_key_exists('semantictagsdata', $_REQUEST)) {
            $postedSemanticTagData = $_REQUEST['semantictagsdata'];
            $tempData              = str_replace("\\", "", $postedSemanticTagData);
            $postedSemanticTagData = json_decode($tempData);

            foreach ($postedSemanticTagData as $data) {
                $term        = get_term_by('name', $data->name, 'post_tag');
                $semanticTag = new SemanticTag();
                $semanticTag->setConcept($data->type);
                $semanticTag->setConceptId($term->term_id);
                $semanticTag->addProperty('rdfs:label', $data->name);
                $semanticTag->addProperty('rdfs:comment', $data->desc);
                $dh = DataHandler::getInstance();
                $dh->saveTag($semanticTag);
            }
        }
    }

    /**
     * Loads existing and configured semantic data for tags before loading post editor
     * @param type $post
     * @return void
     */
    public static function loadConfiguredTags($post)
    {
        $tags       = SemanticTagsHelper::getSemanticTagsByPostId($post->ID);
        $simplified = array();
        foreach ($tags as $tag) {
            $simplified[] = (object) array('name' => $tag->getProperty('rdfs:label'), 'type' => $tag->getConcept(), 'desc' => $tag->getProperty('rdfs:comment'));
        }

        echo "<input type=\"hidden\" name=\"semantictagsdata\" value='" . json_encode($simplified) . "' />";
    }

}
