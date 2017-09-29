<?php
	class WPCustomFieldsSearch_TextBoxInput extends WPCustomFieldsSearch_Input {
		var $template = "text";
		function get_editor_options(){
			$options = parent::get_editor_options();
			$options['extra_config_form'] = plugin_dir_url(__FILE__).'/ng/partials/inputs/textbox.html';
			$options['split_words'] = '';
			return $options;
		}
		function get_submitted_value($options,$data){
			$html_name="f".$options['index'];
			$value = $data[$html_name];
            if($options['split_words']){
                return array_filter(explode(" ",$value),strlen);
            } else {
                return array($value);
            }
		}
        function get_name(){ return __("Text Input"); }
	}

	class WPCustomFieldsSearch_SelectInput extends WPCustomFieldsSearch_Input {
        function get_name(){ return __("Drop Down"); }
		var $template = "select";
		function get_editor_options(){
			$options = parent::get_editor_options();
			$options['extra_config_form'] = plugin_dir_url(__FILE__).'/ng/partials/inputs/select.html';
			$options['clear_extra_config_form'] = true;
			$options['any_label'] = 'Any';
			return $options;
		}

		function render($config,$query){
			if($config['source']=='Auto'){
                $datatype = new $config['datatype']();
                $config['options'] = array_merge(array(array("value"=>"","label"=>$config['any_message'])),$datatype->get_suggested_values($config));
			}
			return parent::render($config,$query);
		}
	}
	class WPCustomFieldsSearch_RadioButtons extends WPCustomFieldsSearch_SelectInput {
        function get_name(){ return __("Radio Buttons"); }
		var $template = "radio-buttons";
    }
	class WPCustomFieldsSearch_CheckboxInput extends WPCustomFieldsSearch_Input {
        function get_name(){ return __("Checkboxes"); }
		var $template = "checkbox";
		function get_editor_options(){
			$options = parent::get_editor_options();
			$options['extra_config_form'] = plugin_dir_url(__FILE__).'/ng/partials/inputs/checkbox.html';
			$options['clear_extra_config_form'] = true;
			return $options;
		}

		function render($config,$query){
			if($config['source']=='Auto'){
                $datatype = new $config['datatype']();
                $config['options'] = $datatype->get_suggested_values($config);
			}
			return parent::render($config,$query);
		}
	}
	class WPCustomFieldsSearch_HiddenInput extends WPCustomFieldsSearch_Input {
        var $show_in_form = false;

		function get_editor_options(){
			$options = parent::get_editor_options();
			$options['extra_config_form'] = plugin_dir_url(__FILE__).'/ng/partials/inputs/hidden.html';
            $options['constant_value'] = '';
			return $options;
		}
		function get_submitted_value($options,$data){
            return $options['constant_value'];
		}

        function get_name(){ return __("Hidden Constant"); }
		function is_submitted($options,$data){
            return true;
        }
	}
