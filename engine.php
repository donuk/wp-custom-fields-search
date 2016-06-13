<?php
	class WPCustomFieldsSearch_Input {
		var $template = "text";
		function render($options){
			$template_file = apply_filters("wpcfs_form_input",
				dirname(__FILE__).'/templates/input-'.$this->template.'.php',
				$this->template,$options);
			$html_name = "f".$options['index'];
			include($template_file);
		}

		function get_id(){
			return get_class($this);
		}
		function get_name(){
			return str_replace("WPCustomFieldsSearch_","",get_class($this));
		}
		function get_editor_options(){
			return array();
		}
		function is_submitted($options,$data){
			$html_name="f".$options['index'];
            return array_key_exists($html_name,$data) && $data[$html_name]!=="";
		}
		function get_submitted_value($options,$data){
			$html_name="f".$options['index'];
			return $data[$html_name];
		}
	}

	class WPCustomFieldsSearch_DataType{
		function get_id(){
			return get_class($this);
		}
		function get_name(){
			return str_replace("WPCustomFieldsSearch_","",get_class($this));
		}
		function get_editor_options(){
			return array(
				"all_fields"=>$this->getFieldMap(),
			);
		}

		function add_join($config,$join){
			global $wpdb;
			$alias = $this->get_table_alias($config);
			$posts_table = $wpdb->posts;
			$join.=" LEFT JOIN ".$this->get_table_name($config)." AS $alias ON $alias.post_id = $posts_table.id";
			return $join;
		}

		function get_field_aliases($config){
			return array( $this->get_field_alias($config,$config['datatype_field']));
		}

		function get_field_alias($config,$field_name){
			return $this->get_table_alias($config).".".$field_name;
		}

		function get_table_alias($config){
			return "wpcfs".$config['index'];
		}
	}

	class WPCustomFieldsSearch_Comparison {
		function get_id(){
			return get_class($this);
		}
		function get_name(){
			return str_replace("WPCustomFieldsSearch_","",get_class($this));
		}
		function get_editor_options(){
			return array();
		}

		function get_where($config,$value,$field_alias){
			return $field_alias."='".mysql_escape_string($value)."'";
		}
	}

	require_once(dirname(__FILE__).'/inputs.php');
	require_once(dirname(__FILE__).'/datatypes.php');
	require_once(dirname(__FILE__).'/comparisons.php');
?>
