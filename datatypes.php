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
	}
