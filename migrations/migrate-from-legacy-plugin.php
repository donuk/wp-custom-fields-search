<?php
    $old_settings = get_option("db_customsearch_widget");
    $new_settings = array(
        "widget"=>array(),
        "preset"=>array()
    );
    foreach($old_settings as $k=>$old_config){
        if($k=="version") continue;

        $new_config = array(
            "name"=>$old_config['name'],
            "inputs"=>array(),
        ); 
        $mappings = array(
            "joiner"=>array(
                "PostDataJoiner"=>"WPCustomFieldsSearch_PostField",
                "PostTypeJoiner"=>"WPCustomFieldsSearch_PostTypeField",
                "CategoryJoiner"=>"WPCustomFieldsSearch_Category",
            ),
            "comparison"=>array(
                "WordsLikeComparison"=>"WPCustomFieldsSearch_TextIn",
                "LikeComparison"=>"WPCustomFieldsSearch_TextIn",
                "EqualComparison" =>"WPCustomFieldsSearch_Equals",
            ),
            "input"=>array(
                "DropDownField"=>"WPCustomFieldsSearch_SelectInput",
                "TextField"=>"WPCustomFieldsSearch_TextBoxInput",
                "HiddenField"=>"WPCustomFieldsSearch_HiddenInput",
            ),
        );
            
        foreach($old_config as $field_index=>$old_input){
            if(is_numeric($field_index)){
                $new_input = array(
                    "label"=>$old_input['label'],
                    "datatype"=>$mappings["joiner"][$old_input['joiner']],
                    "datatype_field"=>$old_input['name'],
                    "comparison"=>$mappings['comparison'][$old_input['comparison']],
                    "input"=>$mappings['input'][$old_input['input']],
                );
                switch($old_input['joiner']){
                    case 'CategoryJoiner':
                        $new_input['datatype_field']='term_id';
                        break;
                }
                switch($old_input['comparison']){
                    case "WordsLikeComparison":
                        $new_input['split_words'] = "True";
                        $new_input['multi_match'] = "All";
                        break;
                    case "HiddenField":
                        $new_input['constant_value'] = $old_input['constant-value'];
                        break;
                }
                switch($old_input['input']){
                    case 'DropDownField':
                        if($old_input['dropdownoptions']){
                            $new_input['source'] = 'Manual';
                            $options = array();
                            $optionString = $old_input['dropdownoptions'];
                            $options=array();
                            $optionPairs = explode(',',$optionString);
                            $prefix="";
                            foreach($optionPairs as $option){
                                if(strrchr($option,"\\")=="\\"){
                                    $prefix .= substr($option,0,-1).",";
                                    continue;
                                }
                                $option = $prefix.$option;
                                list($k,$v) = explode(':',$option);
                                if(!$v) $v=$k;
                                $options[]=array("label"=>$v,"value"=>$k);
                                $prefix = "";
                            }
                            $new_input['options'] = $options;
                        } else {
                            $new_input['source'] = 'Auto';
                        }
                        break;
                }

                $new_config["inputs"][] = $new_input;
            }
        }

        if(strpos($k,"preset-")===0){
            $type="preset";
        } elseif(is_numeric($k)){
            $type="widget";
        } else {
            trigger_error("Unrecognised item in legacy settings");
        }
        
//        $new_config["data"] = json_encode($new_config['data']);
        $new_settings[$type][$k] = $new_config;
    }
    //echo "<pre>";
    //var_dump($new_settings);
    //var_dump($old_settings);
    //$new_example = unserialize('a:2:{i:2;a:1:{s:4:"data";s:958:"{"inputs":[{"datatype":"WPCustomFieldsSearch_PostField","datatype_field":"all","input":"WPCustomFieldsSearch_TextBoxInput","comparison":"WPCustomFieldsSearch_WordsIn","expand":false,"$$hashKey":"object:13","multi_match":"All","label":"Test Field"},{"label":"Custom Field","expand":false,"$$hashKey":"object:73","multi_match":"All","datatype":"WPCustomFieldsSearch_CustomField","datatype_field":"Test Field","comparison":"WPCustomFieldsSearch_TextIn","input":"WPCustomFieldsSearch_SelectInput","any_message":"Any","options":[{"value":1,"label":"One"},{"value":2,"label":"Two"}],"source":"Auto"},{"label":"Untitled Field","expand":true,"$$hashKey":"object:94","multi_match":"All","datatype":"WPCustomFieldsSearch_CustomField","datatype_field":"Test Field","input":"WPCustomFieldsSearch_CheckboxInput","any_message":"Any","options":[{"value":1,"label":"One"},{"value":2,"label":"Two"}],"source":"Auto","comparison":"WPCustomFieldsSearch_Equals"}],"settings":[]}";}s:12:"_multiwidget";i:1;}');
    //var_dump($new_example);
    //var_dump(json_decode($new_example[2]['data'],true));
    update_option("wp-custom-fields-search",array("presets"=>array_values($new_settings['preset'])));
    foreach($new_settings['widget'] as $k=>$v){
        $new_settings['widget'][$k] = array('data'=>json_encode($v));
    }
    update_option("widget_wp_custom_fields_search",$new_settings["widget"]);

    $sidebars = get_option("sidebars_widgets");
    foreach($sidebars as $menu_name=>$widgets){
        foreach($widgets as $k=>$v){
            $sidebars[$menu_name][$k] = str_replace("db_customsearch_widget","wp_custom_fields_search",$v);
        }
    }
    update_option('sidebars_widgets',$sidebars);
