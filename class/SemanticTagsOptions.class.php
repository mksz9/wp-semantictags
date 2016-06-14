<?php

//check if wordpress is loaded
if (!function_exists('add_filter')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

class SemanticTagsOptions
{
    public static function addAdminMenu()
    {
        $options = get_option('semantictags_settings_vocabulary');
        add_options_page(__('SemanticTags Options', 'semantictags'), 'SemanticTags', 'manage_options', 'semantictags', array('SemanticTagsOptions', 'renderOptionsPage'));
    }

    public static function renderOptionsPage()
    {
        echo '<form action="options.php" method="post">';
        echo '<h1>' . __('SemanticTags Options', 'semantictags') . '</h1>';
        settings_fields('semantictags_settings');
        do_settings_sections('semantictags_settings_vocabulary');
        submit_button();
        echo '</form>';
    }

    public static function initializeSettings()
    {
        register_setting('semantictags_settings', 'semantictags_settings_vocabulary');

        add_settings_section(
            'semantictags_settings_vocabulary_section',
            __('Vocabulary settings', 'semantictags'),
            array('SemanticTagsOptions', 'renderSectionVocabular'),
            'semantictags_settings_vocabulary'
        );

        add_settings_field(
            'semantictags_settings_vocabulary_uri',
            __('URI of the used vocabulary', 'semantictags'),
            array('SemanticTagsOptions', 'renderUri'),
            'semantictags_settings_vocabulary',
            'semantictags_settings_vocabulary_section'
        );

        add_settings_field(
            'semantictags_settings_vocabulary_prefix',
            __('Prefix of the used vocabular URI', 'semantictags'),
            array('SemanticTagsOptions', 'renderPrefix'),
            'semantictags_settings_vocabulary',
            'semantictags_settings_vocabulary_section'
        );

        add_settings_field(
            'semantictags_settings_vocabulary_ttl',
            __('URL of the turtle (.ttl) file for the vocabulary', 'semantictags'),
            array('SemanticTagsOptions', 'renderTtl'),
            'semantictags_settings_vocabulary',
            'semantictags_settings_vocabulary_section'
        );
    }

    public static function removeOptions()
    {
        delete_option('semantictags_settings_vocabulary');
    }

    public static function renderUri()
    {
        $options = get_option('semantictags_settings_vocabulary');
        echo '<input type="text" name="semantictags_settings_vocabulary[semantictags_settings_vocabulary_uri]"" value="' . $options['semantictags_settings_vocabulary_uri'] . '">';
    }

    public static function renderPrefix()
    {
        $options = get_option('semantictags_settings_vocabulary');
        echo '<input type="text" name="semantictags_settings_vocabulary[semantictags_settings_vocabulary_prefix]"" value="' . $options['semantictags_settings_vocabulary_prefix'] . '">';
    }

    public static function renderTtl()
    {
        $options = get_option('semantictags_settings_vocabulary');
        echo '<input type="text" name="semantictags_settings_vocabulary[semantictags_settings_vocabulary_ttl]"" value="' . $options['semantictags_settings_vocabulary_ttl'] . '">';
    }

    public static function renderSectionVocabular()
    {
        echo __('Please make the configuration of the intended vocabulary before you can use the semantictags plugin.', 'semantictags');
    }

    public static function getVocabularConfiguration()
    {
        $options = get_option('semantictags_settings_vocabulary');
        return array(
            'uri'    => $options['semantictags_settings_vocabulary_uri'],
            'prefix' => $options['semantictags_settings_vocabulary_prefix'],
            'remote' => $options['semantictags_settings_vocabulary_ttl'],
        );
    }
}
