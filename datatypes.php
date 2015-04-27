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
		function get_sql_fields($params,$post_data){
			global $wpdb;
			switch($params['datatype_field']){
			case 'all':
				return array(
					$wpdb->posts.".post_title",
					$wpdb->posts.".post_author",
					$wpdb->posts.".post_content",
					$wpdb->posts.".post_id",
				);
			default:
				return array($wpdb->posts.".`".mysql_escape_string($params['datatype_field'])."`");
			}
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
		function _get_alias($params){
			if(!$params['alias']){
				$params['alias'] = "custom".$params['index'];
			}
			return $params['alias'];
		}
		function get_sql_fields($params,$post_data){
			global $wpdb;
			$alias = $this->_get_alias($params);

			return array("$alias.meta_value");
		}
	}
