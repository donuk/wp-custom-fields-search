<?php
/*
 * Copyright 2015 Web Hammer UK Ltd.
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 *
 * 	http://www.apache.org/licenses/LICENSE-2.0 
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License.
 */
class WPCustomFieldsSearchWidget extends WP_Widget {
	function __construct(){
		parent::__construct('wp_custom_fields_search',
			__("WPCFS Custom Search Form","wp_custom_fields_search" ),
			array(
				"description"=>__("Customisable search form (from WP Custom Fields Search)","wp_custom_fields_search")
			)
		);
	}

	function widget($args,$instance){
		echo "<h1>".htmlspecialchars($instance["title"])."</h1>";
	}
	function update($new_instance,$old_instance){
		return array(
			"title"=>$new_instance['title'],
		);
	}

	function form($instance){
		$defaults = array(
			"title" => __("New Form","wp_custom_fields_search"),
			"data" => json_encode(
				array("inputs"=>
					array(
						array(
							"datatype"=>"WPCustomFieldsSearch_PostField",
							"datatype/field"=>"all",
							"input"=>"WPCustomFieldsSearch_TextBoxInput",
							"comparison"=>"WPCustomFieldsSearch_WordsIn",
						)
					)
				)
			)
		);
		$instance=array_merge($defaults,$instance);

		$form_id = $this->get_field_id('edit-form');
		echo "<div id='$form_id'>
			</div>
			<script>
				jQuery('#$form_id').wp_custom_fields_search_editor({
					'form_config':".$instance['data'].",
					'building_blocks': ".json_encode(WPCustomFieldsSearchPlugin::get_javascript_editor_config()).",
					'field_name':'".$this->get_field_id('data')."'

				});
			</script>
		";
	}
}
