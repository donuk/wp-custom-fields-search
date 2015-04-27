<?php 

class WPCustomFieldsSearch_Equals extends WPCustomFieldsSearch_Comparison {
}
class WPCustomFieldsSearch_WordsIn extends WPCustomFieldsSearch_Comparison {
}
class WPCustomFieldsSearch_PhraseIn extends WPCustomFieldsSearch_Comparison {
}

class WPCustomFieldsSearch_OrderedComparison extends WPCustomFieldsSearch_Comparison {
	function getEditorOptions(){
		$options = parent::getEditorOptions();
		$options['extra_config_form'] = plugin_dir_url(__FILE__).'/ng/partials/comparisons/ordered.html';
		return $options;
	}
}

class WPCustomFieldsSearch_GreaterThan extends WPCustomFieldsSearch_OrderedComparison {
}
class WPCustomFieldsSearch_LessThan extends WPCustomFieldsSearch_OrderedComparison {
}
class WPCustomFieldsSearch_Range extends WPCustomFieldsSearch_OrderedComparison {
}
