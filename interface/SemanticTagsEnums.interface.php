<?php
if (!function_exists('add_filter')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

interface SemanticTagsEnums
{
    //table data
    const TABLE_PREFIX = 'semantictags';
    //the directory of the wp upload dir relative to the plugin folder
    const UPLOAD_DIR = '/../../uploads/';
}
