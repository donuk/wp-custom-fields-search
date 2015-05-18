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
		parent::__construct('wp_custom_fields_search_1_0',
			__("WPCFS Custom Search Form","wp_custom_fields_search" ),
			array(
				"description"=>__("Customisable search form (from WP Custom Fields Search)","wp_custom_fields_search")
			)
		);
	}

	function widget($args,$instance){
		$data = json_decode($instance['data'],true);
		$input_classes = apply_filters("wp_custom_fields_search_inputs",array());
		$keyed_inputs = array();
		foreach($input_classes as $input){
			$keyed_inputs[$input->getId()] = $input;
		}

		$index = 0;
		$wrapped_inputs = array();
		$post_data = $this->_was_posted($args) ? $_REQUEST : array();
		foreach($data['inputs'] as $k=>$input){
			$input['index'] = ++$index;
			$wrapped_inputs[] = new WPCFS_InputWrapper(
				$keyed_inputs[$input['input']],
				$input,
				$post_data
			);
		}
		
		echo $args['before_widget'];
		echo $args['before_title'];
		echo "Search Form";
		echo $args['after_title'];
		$this->_show_template("wpcfs-search-form",array(
			"inputs"=>$wrapped_inputs,
			'form_id'=>$args['widget_id']
		));
		echo $args['after_widget'];
	}
	function _was_posted($args){
		return $_REQUEST['wpcfs-search-source'] == $args['widget_id'];
	}
	function _show_template($_template_name,$vars){
		extract($vars,EXTR_SKIP);
		$template = get_query_template($_template_name);
		if(!$template) $template = dirname(__FILE__).'/templates/wpcfs-search-form.php';

		include($template);
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
							'root':'".plugin_dir_url(__FILE__)."',
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
						'root':'".plugin_dir_url(__FILE__)."',
						'form_config':".($instance['data']?$instance['data']:"{inputs:[]}").",
						'building_blocks': ".json_encode(WPCustomFieldsSearchPlugin::get_javascript_editor_config()).",
						'field_name':'".$this->get_field_name('data')."'

					});
				</script>
			";
		}
	}
}

class WPCFS_InputWrapper {
	functioN __construct($type,$params,$post_data){
		$this->type = $type;
		$this->params = $params;
		$this->params['id'] = rand();
		$this->params['html_name'] = "wpcfs-".$params['index'];
		$this->post_data = $post_data;
	}
	function getCSSClasses(){
		return strtolower($this->params['type'])." ".str_replace(" ","_",strtolower($this->params['label']));
	}
	function getHTMLId(){
		return $this->params['id'];
	}

	function getLabel(){
		return $this->params['label'];
	}
	function render(){
		return $this->type->render($this->params,$this->post_data);
	}
}
