<?php

//check if wordpress is loaded
if (!function_exists('add_filter')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

class SemanticTagsApp implements SemanticTagsEnums
{

    /**
     * The main routine of the plugin
     * @return void
     */
    public static function main()
    {
        $vocabular = SemanticTagsOptions::getVocabularConfiguration();

        //check if schema file exists, if not than download it from remote to upload dir
        if (!file_exists(SEMANTICTAGS_PATH . SemanticTagsEnums::UPLOAD_DIR . SEMANTICTAGS_PLUGIN_NAME . '/' . $vocabular['prefix'] . '.ttl')) {
            //take responsibility to provide the plugins upload folder
            if (!is_dir(SEMANTICTAGS_PATH . SemanticTagsEnums::UPLOAD_DIR . SEMANTICTAGS_PLUGIN_NAME)) {
                mkdir(SEMANTICTAGS_PATH . SemanticTagsEnums::UPLOAD_DIR . SEMANTICTAGS_PLUGIN_NAME);
            }
            //retrieving vocabulary:
            $vocabulary = file_get_contents($vocabular['remote']);
            //storing vocabulary:
            file_put_contents(SEMANTICTAGS_PATH . '/../../uploads/' . SEMANTICTAGS_PLUGIN_NAME . '/' . $vocabular['prefix'] . '.ttl', $vocabulary);
        }

        //load language
        add_action('plugins_loaded', array(
            'SemanticTagsApp',
            'semanticTagsLoadTextdomain',
        ));

        //load and register settings
        add_action('admin_menu', array('SemanticTagsOptions', 'addAdminMenu'));
        add_action('admin_init', array('SemanticTagsOptions', 'initializeSettings'));

        //add headfiles if on editor
        add_action('admin_enqueue_scripts', array('SemanticTagsApp', 'addHeadfiles'));

        //hook into saving/editing posts
        add_action('save_post', array('SemanticTagsApp', 'processSemanticTags'));

        //load configured semanticTags
        add_action('dbx_post_sidebar', array('SemanticTagsApp', 'loadConfiguredTags'));

        //load semanticTags options on tag editing page
        add_action('edit_tag_form_fields', array('SemanticTagsApp', 'hookTagEditPage'));

        //save semanticTags options on tag editing page
        add_filter('wp_update_term_parent', array('SemanticTagsApp', 'processSemanticTagsOnTagEdit'), 10, 5);

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
        if ('post.php' == $pagenow || 'post-new.php' == $pagenow) {
            wp_enqueue_script('semantictags_tagconfigurationonpost_js', plugin_dir_url(SEMANTICTAGS_FILE) . 'js/tagConfiguratorOnPost.js');
            //localization of loaded script-handle:
            wp_localize_script('semantictags_tagconfigurationonpost_js', 'objectL10n', array(
                'edit'             => __('edit', 'semantictags'),
                'overlay_headline' => __('Semantic configuration', 'semantictags'),
                'semantictag_name' => __('Name', 'semantictags'),
                'semantictag_type' => __('Datatype', 'semantictags'),
                'semantictag_desc' => __('Description', 'semantictags'),
                'semantictag_save' => __('Save', 'semantictags'),
            ));
            wp_enqueue_style('semantictags_tagconfigurationonpost_css', plugin_dir_url(SEMANTICTAGS_FILE) . 'css/tagConfiguratorOnPost.css');
        } else if ('edit-tags.php' == $pagenow) {
            wp_enqueue_script('semantictags_tagconfigurationontagedit_js', plugin_dir_url(SEMANTICTAGS_FILE) . 'js/tagConfiguratorOnTagEdit.js');
        }
    }

    /**
     * Processes configured SemanticTags after editing a post
     * @param integer $post_id
     * @return void
     */
    public static function processSemanticTags($post_id)
    {
        if (array_key_exists('semantictagsdata', $_REQUEST)) {
            $postedSemanticTagData = $_REQUEST['semantictagsdata'];
            //unescaping data:
            $tempData = str_replace("\\", "", $postedSemanticTagData);
            //decode JSON data:
            $postedSemanticTagData = json_decode($tempData);

            foreach ($postedSemanticTagData as $data) {
                //get the tag information to each configured SemanticTag:
                $term = get_term_by('name', $data->name, 'post_tag');
                //create the new SemanticTag object which gets stored:
                $semanticTag = new SemanticTag();
                //set its informations to store:
                $semanticTag->setConcept($data->type);
                $semanticTag->setConceptId($term->term_id);
                $semanticTag->addProperty('rdfs:label', $data->name);
                $semanticTag->addProperty('rdfs:comment', $data->desc);
                //save the SemanticTag with the DataHandler:
                $dh = DataHandler::getInstance();
                $dh->saveTag($semanticTag);
            }
        }
    }

    /**
     * Processes configured SemanticTags after editing a tag on its editing page
     * @param int $args_parent
     * @param int $term_id
     * @param string $taxonomy
     * @param array $parsed_args
     * @param array $args
     * @return int
     */
    public function processSemanticTagsOnTagEdit($args_parent, $term_id, $taxonomy, $parsed_args, $args)
    {
        $semanticTag = new SemanticTag();
        $dh          = DataHandler::getInstance();
        if (isset($args['st_type']) && $args['st_type'] != '') {
            //set its informations to store:
            $semanticTag->setConcept($args['st_type']);
            $semanticTag->setConceptId($args['term_id']);
            $semanticTag->addProperty('rdfs:label', $args['name']);
            $semanticTag->addProperty('rdfs:comment', $args['st_desc']);
            //save the SemanticTag with the DataHandler:
            $dh->saveTag($semanticTag);
        } else {
            $semanticTag->setConceptId($args['term_id']);
            $dh->deleteTag($semanticTag);
        }
        return $args_parent;
    }

    /**
     * Loads existing and configured semantic data for tags before loading post editor
     * @param type $post
     * @return void
     */
    public static function loadConfiguredTags($post)
    {
        //retrieve all SemanticTags which belong to a post:
        $tags = SemanticTagsHelper::getSemanticTagsByPostId($post->ID);
        //build the structure which is required in frontend, for processing with JS:
        $simplified = array();
        foreach ($tags as $tag) {
            $simplified[] = (object) array('name' => $tag->getProperty('rdfs:label'), 'type' => $tag->getConcept(), 'desc' => $tag->getProperty('rdfs:comment'));
        }
        echo "<input type=\"hidden\" name=\"semantictagsdata\" value='" . json_encode($simplified) . "' />";
    }

    /**
     * Provides the semanticTag options on the tag editing page on backend of Wordpress
     * @param WP_Term $tag
     * @return void
     */
    public static function hookTagEditPage($tag)
    {
        $dh = DataHandler::getInstance();

        $currentSemanticDataType = $currentSemanticDescription = $currentSemanticConncetions = '';

        //retrieve the semanticTag object to the post tag
        if ($semanticTag = $dh->loadTagByConceptId($tag->term_id)) {

            $properties                 = $semanticTag->getAllProperties();
            $currentSemanticDataType    = $semanticTag->getConcept();
            $currentSemanticDescription = $properties['rdfs:comment'];
            //fill with the conncetions
            $connections = array();
            foreach ($properties as $property => $val) {
                if ($property != 'rdfs:comment' && $property != 'rdfs:label') {
                    $conncetions[] = array('property' => $property, 'value' => $val);
                }
            }
            $currentSemanticConncetions = json_encode($connections);
        }

        //output the form fields
        echo '<tr class="form-field term-description-wrap"><th scope="row"><label for="description">' . __('Datatype', 'semantictags') . '</label></th><td><select name="st_type">' . self::generateDataTypeSelect($currentSemanticDataType) . '</select><p class="description">' . __('The datatype of the tag.', 'semantictags') . '</p></td>
        </tr>';
        echo '<tr class="form-field term-description-wrap"><th scope="row"><label for="description">' . __('Description', 'semantictags') . '</label></th><td><textarea name="st_desc">' . $currentSemanticDescription . '</textarea><p class="description">' . __('Internal description for the semantic tag.', 'semantictags') . '</p>
            <input type="hidden" name="st_connections" value="' . $currentSemanticConncetions . '" /></td>
        </tr>';
    }

    /**
     * Generates the select input depending on the current choosed datatype + all available datatypes in vocabulary
     * @param string $current
     * @return string
     */
    public static function generateDataTypeSelect($current)
    {
        $vocabularyClasses = SemanticTagsHelper::getVocabularyClasses();
        $returnOptions     = '<option value=""></option>';
        foreach ($vocabularyClasses as $class) {
            $splitted = explode(':', $class);
            $selected = ($class == $current) ? ' selected' : '';
            $returnOptions .= '<option value="' . $class . '"' . $selected . '>' . $splitted[1] . '</option>';
        }
        return $returnOptions;
    }

}
