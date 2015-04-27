<?
	function humanize($string){
		return trim(join(" ",preg_split("/(_)|(?=[A-Z])/",$string)));
	}
	class WPCustomFieldsSearch_Base {
		function getId(){
			return get_class($this);
		}
		function getName(){
			return humanize(str_replace("WPCustomFieldsSearch_","",$this->getId()));
		}
		function getEditorOptions(){
			return array();
		}
	}
	class WPCustomFieldsSearch_Input extends WPCustomFieldsSearch_Base{
	}

	class WPCustomFieldsSearch_DataType extends WPCustomFieldsSearch_Base {
		function getEditorOptions(){
			return array(
				"all_fields"=>$this->getFieldMap(),
			);
		}
	}

	class WPCustomFieldsSearch_Comparison extends WPCustomFieldsSearch_Base {
	}

	require_once(dirname(__FILE__).'/inputs.php');
	require_once(dirname(__FILE__).'/datatypes.php');
	require_once(dirname(__FILE__).'/comparisons.php');
?>
