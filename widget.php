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
		require_once("engine.php");
		$components = array();
		$data =json_decode($instance['data'],true);
		$index = 0;
		foreach($data['inputs'] as $config){
			$clsname = $config['input'];
			$config['class'] = new $clsname();
			$config['index'] = ++$index;
			array_push($components,$config);
		}
		$template_file = apply_filters("wpcfs_form_template",dirname(__FILE__).'/templates/form.php',$instance);
		$hidden = "<input type='hidden' name='wpcfs' value='".htmlspecialchars($args['widget_id'])."'/>";
		$method = "get";
		$results_page = "/";
		include($template_file);
	}
	function update($new_instance,$old_instance){
		return array(
			"data"=>$new_instance['data'],
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
							"datatype_field"=>"all",
							"input"=>"WPCustomFieldsSearch_TextBoxInput",
							"comparison"=>"WPCustomFieldsSearch_WordsIn",
						)
					)
				)
			)
		);
		$instance=array_merge($defaults,$instance);

		$form_id = $this->get_field_id('edit-form');
		// TODO: Could this be implemented with is_active_sidebar???
		if($this->number=="__i__"){
			echo "
				<div id='$form_id' class='wp-custom-fields-search-form'>
				</div>
				<script>
					jQuery('.wp-custom-fields-search-form:not(.wp_custom_fields_search_editor)').each(function(el){
						var $=jQuery;
						var template_id = '$form_id',
							template_name='".$this->get_field_name('data')."',
							id_parts = template_id.split('__i__'),
							actual_id = $(this).attr('id');

						var index=actual_id.substr(id_parts[0].length,actual_id.length-id_parts[1].length-id_parts[0].length);
						var actual_name = template_name.replace('__i__',index);
						if(index=='__i__') return;

						$(this).wp_custom_fields_search_editor({
							'form_config':".($instance['data']?$instance['data']:"{inputs:[]}").",
							'building_blocks': ".json_encode(WPCustomFieldsSearchPlugin::get_javascript_editor_config()).",
							'field_name':'".$this->get_field_name('data')."'
						});
						
					});
				</script>
			";
		} else {
			echo "
				<div id='$form_id'>
				</div>
				<script>
					jQuery('#$form_id').wp_custom_fields_search_editor({
						'form_config':".($instance['data']?$instance['data']:"{inputs:[]}").",
						'building_blocks': ".json_encode(WPCustomFieldsSearchPlugin::get_javascript_editor_config()).",
						'field_name':'".$this->get_field_name('data')."'

					});
				</script>
			";
		}
	}
}
