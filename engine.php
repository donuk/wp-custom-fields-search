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
		function html_name($params){
			return "wpcfs-".$params['index'];
		}
		function render($params,$posted_data){
			echo "<input type='text' name='".$this->html_name($params)."' id='".$params['id']."' value=\"".htmlspecialchars($posted_data[$params['html_name']])."\">";
		}

		function is_populated($params,$posted_data){
			return $this->get_value($params,$posted_data);
		}
		function get_value($params,$posted_data){
			return $posted_data[$this->html_name($params)];
		}
	}

	class WPCustomFieldsSearch_DataType extends WPCustomFieldsSearch_Base {
		function getEditorOptions(){
			return array(
				"all_fields"=>$this->getFieldMap(),
			);
		}
	}

	abstract class WPCustomFieldsSearch_Comparison extends WPCustomFieldsSearch_Base {
		abstract function get_sql_where_clause($params,$field_name,$value);
	}

	require_once(dirname(__FILE__).'/inputs.php');
	require_once(dirname(__FILE__).'/datatypes.php');
	require_once(dirname(__FILE__).'/comparisons.php');
?>
