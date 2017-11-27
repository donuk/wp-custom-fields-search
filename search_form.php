<?php
    class WPCFSSearchForm {
        static function get_query_if_submitted($submit_id){
            if($_GET['wpcfs']==$submit_id){
                return stripslashes_deep($_GET);
            }
        }

        static function show_form($data,$submit_id,$args=null){
            require_once("engine.php");
            if(!$args){
                // If called as a preset we should set some sensible defaults
                $args = array(
                    "before_title"=>"<h4>",
                    "after_title"=>"</h4>",
                    "before_widget"=>"<div class='wpcfs-preset'>",
                    "after_widget"=>"<div>",
                );
            }
            $components = array();
            $index = 0;
            static $counter;
            $counter++;
            $form_id = $submit_id."/$counter";
            foreach($data['inputs'] as $config){
                $clsname = $config['input'];
                try {
                    $config['class'] = wpcfs_instantiate_class($clsname);
                } catch(WPCustomFieldsSearchClassException $e){
                    error_log("WP Custom Fields Search - search_form.php ".$e->getMessage());
                    continue;
                }
                $config['index'] = ++$index;
                $config['html_name'] = "f$index";
                $config['html_id'] = "$form_id/$config[html_name]";
                if($config['class']->show_in_form){
                    array_push($components,$config);
                }
            }
            $template_file = apply_filters("wpcfs_form_template",dirname(__FILE__).'/templates/form.php',$data);
            $hidden = "<input type='hidden' name='wpcfs' value='".htmlspecialchars($submit_id)."'/>";
            $method = "get";
            $results_page = apply_filters("wpcfs_results_page","/",$data);
            $query = self::get_query_if_submitted($submit_id);

            $hidden = apply_filters("wpcfs_hidden_elements",$hidden,array("data"=>$data,"form_id"=>$form_id));
            $settings =$data['settings'];
            include($template_file);
        }
    }
