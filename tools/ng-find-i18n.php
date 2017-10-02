<?php
    $base_dir = $_SERVER['argv'][1];
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
                read_html_file($file);
                break;
            case "js":
                //read_js_file($file);
                break;
        }
    }

    function read_html_file($filename){
        global $found;
        $file = file_get_contents($filename);
        preg_match_all("/<i18n[^>]*>(.*?)<\/i18n>/s",$file,$matches,PREG_SET_ORDER);
        foreach($matches as $match){
            if(!array_key_exists($match[1],$found)) $found[$match[1]] = array();
            $found[$match[1]][] = $filename;
        }
    }
    recurse_read($base_dir);

    print "<?php /** Auto-generated translation file */\n
        \$translations = array(";
    foreach($found as $word=>$files){
        print " ".json_encode($word)." => __(".json_encode($word)."),/*".join(", ",$files)." */\n";
    }
    print "); ?>";
?>
