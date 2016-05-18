<?php

//check if wordpress is loaded
if (!function_exists('add_filter')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

interface TypeChecker
{
    /**
     * Gives answer about correctness of a vocabulary type
     * @return bool
     */
    public function check()
    {

    }
}
