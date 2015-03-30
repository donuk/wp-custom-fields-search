<?
	class WPCustomFieldsSearch_Input {
		function getId(){
			return get_class($this);
		}
		function getName(){
			return str_replace("WPCustomFieldsSearch_","",get_class($this));
		}
		function getEditorOptions(){
			return array();
		}
	}

	class WPCustomFieldsSearch_DataType{
		function getId(){
			return get_class($this);
		}
		function getName(){
			return str_replace("WPCustomFieldsSearch_","",get_class($this));
		}
		function getEditorOptions(){
			return array(
				"all_fields"=>$this->getFieldMap(),
			);
		}
	}

	class WPCustomFieldsSearch_Comparison {
		function getId(){
			return get_class($this);
		}
		function getName(){
			return str_replace("WPCustomFieldsSearch_","",get_class($this));
		}
		function getEditorOptions(){
			return array();
		}
	}

	require_once(dirname(__FILE__).'/inputs.php');
	require_once(dirname(__FILE__).'/datatypes.php');
	require_once(dirname(__FILE__).'/comparisons.php');
?>
