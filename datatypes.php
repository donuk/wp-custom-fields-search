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

		function add_joins($config,$join,$count){
			return $join;
		}
		function get_field_aliases($config,$count){
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
		function get_field_alias($config,$field_name,$count){
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
		function get_field_alias($config,$field_name,$count){
			return $this->get_table_alias($config,$count).".meta_value";
		}
		function add_joins($config,$join,$count){
			$join = parent::add_joins($config,$join,$count);
            for($a = 0 ; $a<$count ; $a++){
    			$alias = $this->get_table_alias($config,$a);
	    		$join = str_replace("AS $alias ON ","AS $alias ON $alias.meta_key='".mysql_escape_string($config['datatype_field'])."' AND ",$join);
            }
            return $join;
		}
	}

    class WPCustomFieldsSearch_Category extends WPCustomFieldsSearch_DataType {
        var $multijoin = true;

        function getFieldMap(){
            return array("term_id"=>"ID","name"=>"Name");
        }

		function add_joins($config,$join,$count){
            global $wpdb;
            for($index = 0 ; $index<$count ; $index++){

                $alias = $this->get_table_alias($config,$index);
                $alias2 = $alias."_2";
                $alias3 = $alias."_3";
                
                $join.=" LEFT JOIN $wpdb->term_relationships AS $alias2 ON $wpdb->posts.ID = $alias2.object_id ";
                $join.=" LEFT JOIN $wpdb->term_taxonomy AS $alias3 ON $alias3.term_taxonomy_id = $alias2.term_taxonomy_id ";
                $join.=" LEFT JOIN $wpdb->terms AS $alias ON $alias3.term_id = $alias.term_id ";
            }

            return $join;
        }

        function get_editor_options(){
            $options = parent::get_editor_options();
            if(!$options['labels']) $options['labels'] = array();
            $options['labels'][] = "is_wp_term";
            return $options;
        }
        function recurse_category($id,$field,$trace=array()){
            $categories = get_categories(array('parent'=>$id));
            $values = array();
            foreach($categories as $category){
                $full_trace[] = array_merge($trace,array($category));
                $values[] = array("value"=>$category->$field,"label"=> $category->name);
                $values = array_merge($values,$this->recurse_category($category->term_id,$field,$full_trace));
            }
            return $values;
        }

        function get_suggested_values($config){
            return $this->recurse_category(0,$config['datatype_field']); #TODO - Have this id selected in the editor UI
        }
    }
