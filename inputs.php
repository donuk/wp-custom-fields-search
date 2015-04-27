<?php
	class WPCustomFieldsSearch_TextBoxInput extends WPCustomFieldsSearch_Input {
	}

	class WPCustomFieldsSearch_SelectInput extends WPCustomFieldsSearch_Input {
		function getEditorOptions(){
			$options = parent::getEditorOptions();
			$options['extra_config_form'] = plugin_dir_url(__FILE__).'/ng/partials/inputs/select.html';
			$options['any_label'] = 'Any';
			$options['handler'] = "wpcfs_select_handler";
			return $options;
		}
		function render($params,$posted_data){
			echo "<select name='".$params['html_name']."' id='".$params['id']."'>";
			$selected = $posted_data[$params['html_name']];
			foreach($this->getOptions($params) as $k=>$v){
				$attrs = ($k==$selected) ? " selected='selected'":"";
				echo '<option value="'.htmlspecialchars($k).'"'.$attrs.'>'.htmlspecialchars($v).'</option>';
			}
			echo "</select>";
		}

		function getOptions($params){
			$options = array();
			switch($params['source']){
			case 'Manual':
				foreach($params['options'] as $option){
					$options[$option['value']] = $option['label'];
				}
				break;
			case 'Auto':
				throw new Exception("Auto-populating selects Unimplemented"); //TODO: Implement this
			default:
				trigger_error("Bad Select Source");
			}
			return $options;
		}
	}
