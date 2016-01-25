<?php
	class WPCustomFieldsSearch_TextBoxInput extends WPCustomFieldsSearch_Input {
		function render($config){
			echo "<input type='text'/>";
		}
	}

	class WPCustomFieldsSearch_SelectInput extends WPCustomFieldsSearch_Input {
		function getEditorOptions(){
			$options = parent::getEditorOptions();
			$options['extra_config_form'] = plugin_dir_url(__FILE__).'/ng/partials/inputs/select.html';
			$options['any_label'] = 'Any';
			return $options;
		}
	}
