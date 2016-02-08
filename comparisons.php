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
}
class WPCustomFieldsSearch_LessThan extends WPCustomFieldsSearch_Comparison {
}
class WPCustomFieldsSearch_Range extends WPCustomFieldsSearch_Comparison {
}
