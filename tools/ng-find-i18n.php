<?php
    $found = array();

    function recurse_read($dir_or_file){
        if(is_file($dir_or_file)){
            read_file($dir_or_file);
        } else {
            recurse_dir($dir_or_file);
        }
    }

    function recurse_dir($dir){
        foreach(glob("$dir/*") as $file){
            recurse_read($file);
        }
    }

    function read_file($file){
        $ext = preg_replace("/^.*\./","",$file);
        switch($ext){
            case "html":
                translate_strings(get_strings_from_html_file($file),$file);
                break;
            case "js":
                translate_strings(get_strings_from_javascript_file($file),$file);
                break;
        }
    }

    function translate_strings($strings,$filename){
        global $found;
        foreach($strings as $string){
            if(!array_key_exists($string,$found)) $found[$string] = array();
            $found[$string][]=$filename;
        }
    }
    function get_strings_from_html_file($filename){
        $file = file_get_contents($filename);
        preg_match_all("/<i18n[^>]*>(.*?)<\/i18n>/s",$file,$matches,PREG_SET_ORDER);
        $strings = array();
        foreach($matches as $match){
            $strings[] = $match[1];
        }
        return $strings;
    }
    function get_strings_from_javascript_file($filename){
        $file = file_get_contents($filename);
        preg_match_all("/__\([\"'](.*?)[\"']\)/s",$file,$matches,PREG_SET_ORDER);
        $strings = array();
        foreach($matches as $match){
            $strings[] = $match[1];
        }
        return $strings;
    }

    foreach(array_slice($_SERVER['argv'],1) as $dir)
        recurse_read($dir);

    print "<?php /** Auto-generated translation file */\n
        \$translations = array(";
    foreach($found as $word=>$files){
        print " ".json_encode($word)." => __(".json_encode($word)."),/*".join(", ",$files)." */\n";
    }
    print "); ?>";
?>
