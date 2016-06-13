<?php 

class WPCustomFieldsSearch_Equals extends WPCustomFieldsSearch_Comparison {
}
class WPCustomFieldsSearch_WordsIn extends WPCustomFieldsSearch_Comparison {
	function get_where($config,$value,$field_alias){
		#FIXME - This needs serious thinking about
		return WPCustomFieldsSearch_PhraseIn::get_where($config,$value,$field_alias);
	}
}
class WPCustomFieldsSearch_PhraseIn extends WPCustomFieldsSearch_Comparison {
	function get_where($config,$value,$field_alias){
		return $field_alias." LIKE '%".mysql_escape_string($value)."%'";
	}
}
class WPCustomFieldsSearch_GreaterThan extends WPCustomFieldsSearch_Comparison {
	function get_where($config,$value,$field_alias){
		$value = mysql_escape_string($value);
		switch($config['numeric']){
		case 'Numeric':
			$field_alias="1*$field_alias";
			break;
		case 'Alphabetical': default:
			$value = "'$value'";
			break;
		}
		return "$field_alias>$value";
	}
	function get_editor_options(){
		$options = parent::get_editor_options();
		$options['extra_config_form'] = plugin_dir_url(__FILE__).'/ng/partials/comparisons/numeric.html';
		$options['numeric'] = "Alphabetical";
		return $options;
	}
}
class WPCustomFieldsSearch_LessThan extends WPCustomFieldsSearch_Comparison {
}
class WPCustomFieldsSearch_Range extends WPCustomFieldsSearch_Comparison {
}

class WPCustomFieldsSearch_SubCategoryOf extends WPCustomFieldsSearch_Comparison {
    function get_editor_options(){
        return array_merge(parent::get_editor_options(),array(
            "valid_for"=>array(
                "datatype"=>"is_wp_term"
            )
        ));
    }
    function collect_ids($field,$category_list){
        $to_return = array();
        foreach($category_list as $category){
            $to_return[] = $category->$field;
            $to_return = array_unique(array_merge($to_return,$this->collect_ids($field,get_categories(array("child_of"=>$category->term_id)))));
        }
        return $to_return;
    }
    function get_where($config,$value,$field_alias){
        global $wpdb;
        $field = $config['datatype_field'];
        if($field == "term_id"){
            $dummy_category->term_id = $value;
            $parent_categories = array($dummy_category);
        } else {
            $parent_categories = get_categories(array("name"=>$value));
        }
        $child_categories = $this->collect_ids($field,$parent_categories);
        
        return $field_alias." IN ('".join("','",$child_categories)."')";
    }
}
