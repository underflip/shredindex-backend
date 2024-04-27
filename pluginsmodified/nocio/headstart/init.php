<?php

if(!function_exists("join_paths")) {
    function join_paths()
    {
        $paths = array();

        foreach (func_get_args() as $arg) {
            if ($arg !== '') {
                $paths[] = $arg;
            }
        }

        return preg_replace('#/+#', '/', join('/', $paths));
    }
}
