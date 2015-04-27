<?php
	class WPCustomFieldsSearch_TextBoxInput extends WPCustomFieldsSearch_Input {
	}

	class WPCustomFieldsSearch_SelectInput extends WPCustomFieldsSearch_Input {
		function getEditorOptions(){
			$options = parent::getEditorOptions();
			$options['extra_config_form'] = plugin_dir_url(__FILE__).'/ng/partials/inputs/select.html';
			$options['any_label'] = 'Any';
#			$options['default_settigs'] = array("source"=>"Auto");
			$options['handler'] = "wpcfs_select_handler";
			return $options;
		}
	}
