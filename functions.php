<?php

    function wpcfs_strip_hash_keys($data){
        $expressions = array('\$\$hashKey":"[^"]*"','"unsaved": *(true|false)');

        foreach($expressions as $expression){
            $formats = array("/$expression,/","/,$expression/","/{".$expression."}/");
            $data = preg_replace($formats,'',$data);
        }
        return $data;
    }
