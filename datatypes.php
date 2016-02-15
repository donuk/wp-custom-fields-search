<?php
	class WPCustomFieldsSearch_PostField extends WPCustomFieldsSearch_DataType {
		function getFieldMap(){
			global $wpdb;
			return array(
				"post_title"	=>	"Title",
				"post_author"	=>	"Author",
				"post_date"	=>	"Date",
				"post_content"	=>	"Content",
				"post_excerpt"	=>	"Excerpt",
				"all"		=>	"All",
				"post_id"	=>	"ID",
			);
		}
		function getAvailableFields(){
			return array_values($this->getFieldMap());
		}

		function add_join($config,$join){
			return $join;
		}
		function get_field_aliases($config){
			if($config['datatype_field']=='all'){
				$aliases = array();
				foreach(array('post_title','post_author','post_content') as $field){
					$aliases[] = $this->get_field_alias($config,$field);
				}
				return $aliases;
			} else {
				return parent::get_field_aliases($config);
			}
		}
		function get_field_alias($config,$field_name){
			global $wpdb;
			return $wpdb->posts.".".$field_name;
		}
	}
	class WPCustomFieldsSearch_CustomField extends WPCustomFieldsSearch_DataType {
		function getFieldMap(){
			global $wpdb;
			$results = $wpdb->get_results("SELECT DISTINCT(meta_key) FROM $wpdb->postmeta ORDER BY meta_key");
			$fields = array();
			foreach($results as $result){
				$fields[$result->meta_key] = $result->meta_key;
			}
			return $fields;
		}
		function getAvailableFields(){
			return array_values($this->getFieldMap());
		}

		function get_table_name(){
			global $wpdb;
			return $wpdb->postmeta;
		}
		function get_field_alias($config,$field_name){
			return $this->get_table_alias($config).".meta_value";
		}
		function add_join($config,$join){
			$join = parent::add_join($config,$join);
			$alias = $this->get_table_alias($config);
			return $join." AND $alias.meta_key='".mysql_escape_string($config['datatype_field'])."' ";
		}
	}
