<?php
	class WPCustomFieldsSearch_TextBoxInput extends WPCustomFieldsSearch_Input {
		var $template = "text";
	}

	class WPCustomFieldsSearch_SelectInput extends WPCustomFieldsSearch_Input {
		var $template = "select";
		function get_editor_options(){
			$options = parent::get_editor_options();
			$options['extra_config_form'] = plugin_dir_url(__FILE__).'/ng/partials/inputs/select.html';
			$options['any_label'] = 'Any';
			return $options;
		}

		function render($config){
			if($config['source']=='Auto'){
				throw new Exception("Not Implemented Yet");
			}
			return parent::render($config);
		}
	}
