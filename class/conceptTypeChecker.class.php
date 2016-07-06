<?php

//check if wordpress is loaded
if (!function_exists('add_filter')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

/**
 * ToDo:
 * -Remove interface, rename "TypeChecker" and remove PropertytypeChecker
 */
class ConceptTypeChecker
{
    /**
     * Retrieving all concepts from used vocabulary
     * @return void
     */
    public static function getAllConcepts()
    {
        $conceptClasses = SemanticTagsHelper::getVocabularyClasses();
        echo json_encode($conceptClasses);
        die();
    }

    public static function getAllTags()
    {
        $tags = SemanticTagsHelper::getAllTagsInSelectbox();
        echo json_encode($tags);
        die();
    }
}
