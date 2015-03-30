<?php
	class WPCustomFieldsSearch_TextBoxInput extends WPCustomFieldsSearch_Input {
	}

	class WPCustomFieldsSearch_SelectInput extends WPCustomFieldsSearch_Input {
		function getEditorOptions(){
			$options = parent::getEditorOptions();
			$options['handler'] = 'select_handler';
			$options['any_label'] = 'Any';
			return $options;
		}
	}
