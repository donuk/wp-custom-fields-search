<?php
    class WPCFSSearchForm {
        static function get_query_if_submitted($instance){
            if($_GET['wpcfs']==$instance['widget_id']){
                return $_GET;
            }
        }   
        static function show_form($data,$submit_id){
            require_once("engine.php");
            $components = array();
            $index = 0;
            foreach($data['inputs'] as $config){
                $clsname = $config['input'];
                $config['class'] = new $clsname();
                $config['index'] = ++$index;
                array_push($components,$config);
            }
            $template_file = apply_filters("wpcfs_form_template",dirname(__FILE__).'/templates/form.php',$instance);
            $hidden = "<input type='hidden' name='wpcfs' value='".htmlspecialchars($submit_id)."'/>";
            $method = "get";
            $results_page = apply_filters("wpcfs_results_page","/",$data);
            $query = self::get_query_if_submitted($args);
            include($template_file);
        }
    }
