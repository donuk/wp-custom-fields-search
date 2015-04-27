<?php 

class WPCustomFieldsSearch_Equals extends WPCustomFieldsSearch_Comparison {
	function get_sql_where_clause($params,$field_name,$value){
		return "$field_name = '".mysql_escape_string($value)."'";
	}
}
class WPCustomFieldsSearch_WordsIn extends WPCustomFieldsSearch_Comparison {
	function get_sql_where_clause($params,$field_name,$value){
		throw new Exception("Unimplemented");
	}
}
class WPCustomFieldsSearch_PhraseIn extends WPCustomFieldsSearch_Comparison {
	function get_sql_where_clause($params,$field_name,$value){
		throw new Exception("Unimplemented");
	}
}

abstract class WPCustomFieldsSearch_OrderedComparison extends WPCustomFieldsSearch_Comparison {
	function prepare_field_name($params,$name){
		if($params['is_numeric']){
			$name = $name."*1";
		}
		return $name;
	}
	function prepare_value($params,$value){
		$value = mysql_escape_string($value);
		if(!$params['is_numeric']){
			$value="'$value'";
		}
		return $value;
	}
	function getEditorOptions(){
		$options = parent::getEditorOptions();
		$options['extra_config_form'] = plugin_dir_url(__FILE__).'/ng/partials/comparisons/ordered.html';
		return $options;
	}
}

class WPCustomFieldsSearch_GreaterThan extends WPCustomFieldsSearch_OrderedComparison {
	function get_sql_where_clause($params,$field_name,$value){
		$field_name = $this->prepare_field_name($params,$field_name);
		$value = $this->prepare_value($params,$value);
		return "$field_name > $value";
	}
}
class WPCustomFieldsSearch_LessThan extends WPCustomFieldsSearch_OrderedComparison {
	function get_sql_where_clause($params,$field_name,$value){
		$field_name = $this->prepare_field_name($params,$field_name);
		$value = $this->prepare_value($params,$value);
		return "$field_name < $value";
	}
}
class WPCustomFieldsSearch_Range extends WPCustomFieldsSearch_OrderedComparison {
	function get_sql_where_clause($params,$field_name,$value){
		$field_name = $this->prepare_field_name($params,$field_name);
		list($value1,$value2) = explode(":",$value);
		$clauses = array();
		foreach(array(array($value1,">"),array($value2,"<")) as $clause_config){
			list($value,$comparison) = $clause_config;
			if(strlen($value)>0){
				$value = $this->prepare_value($params,$value);
				$clauses[] = "$field_name $comparison $value";
			}
		}
		return join(" AND ",$clauses);
	}
}
