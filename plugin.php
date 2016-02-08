<?php
/*
Plugin Name: WP Custom Fields Search
Plugin URI: http://www.webhammer.co.uk/wp-custom-fields-search
Description: Adds powerful search forms to your wordpress site
Version: 0.9.9
Author: Don Benjamin
Author URI: http://www.webhammer.co.uk/
Text Domain: wp_custom_fields_search
*/
/*
 * Copyright 2015 Webhammer UK Ltd.
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


class WPCustomFieldsSearchPlugin {
	function __construct(){
		add_action('widgets_init',array($this,"widgets_init"));
		add_action('admin_enqueue_scripts',array($this,"admin_enqueue_scripts"));

		add_filter("wp_custom_fields_search_inputs",array($this,"wp_custom_fields_search_inputs"));
		add_filter("wp_custom_fields_search_datatypes",array($this,"wp_custom_fields_search_datatypes"));
		add_filter("wp_custom_fields_search_comparisons",array($this,"wp_custom_fields_search_comparisons"));

		if($this->is_search_submitted()){
			add_filter('template_include',array($this,'show_search_results_template'),99);
			add_filter('posts_orderby',array($this,'posts_orderby'));
			add_filter('posts_join',array($this,'posts_join'));
			add_filter('posts_where',array($this,'posts_where'));
			add_filter('posts_groupby',array($this,'posts_groupby'));
		}
	}

	function is_search_submitted(){
		return $_REQUEST['wpcfs'];
	}
	function get_submitted_form(){
		static $submitted;
		if(!isset($submitted)){
			$wpcfs = $_REQUEST['wpcfs'];
			if(substr($wpcfs,0,23)=="wp_custom_fields_search"){
				$submitted = json_decode(get_option("widget_wp_custom_fields_search")[substr($wpcfs,24)]['data'],true);
			} else {
				$submitted = false;
			}
			if($submitted){
				require_once(dirname(__FILE__).'/engine.php');
				$index = 0;
				foreach($submitted['inputs'] as &$input){
					$input['datatype'] = new $input['datatype'];
					$input['input'] = new $input['input'];
					$input['comparison'] = new $input['comparison'];
					$input['index'] = ++$index;
				}
			}
		}
		return $submitted;
	}
	function show_search_results_template($template){
		$new_template = locate_template(array('wpcfs-search.php','search.php','index.php'));
		if($new_template) return $new_template;
		else return $template;
	}


	function posts_orderby($orderby){
		return $orderby;
	}
	function posts_groupby($groupby){
		return $groupby;
	}
	function posts_join($join){
		$form = $this->get_submitted_form();
		foreach($form['inputs'] as $index=>$input){
			$submitted = $input['input']->is_submitted($input,$_REQUEST);
			if($submitted){
				$join = $input['datatype']->add_join($input,$join);
			}
		}
		return $join;
	}
	function posts_where($where){
		$form = $this->get_submitted_form();
		foreach($form['inputs'] as $index=>$input){
			$submitted = $input['input']->get_submitted_value($input,$_REQUEST);
			$wheres = array();
			foreach($input['datatype']->get_field_aliases($input) as $alias){
				$wheres[]= $input['comparison']->get_where($input,$submitted,$alias);
			}
			$where.=" AND ( ".join(" OR ",$wheres)." )";
		}
		return $where;
	}

	function widgets_init(){
		require_once(dirname(__FILE__).'/widget.php');
		register_widget("WPCustomFieldsSearchWidget");
	}

	function admin_enqueue_scripts(){
		wp_enqueue_script(
			"angularjs",
			"https://ajax.googleapis.com/ajax/libs/angularjs/1.3.15/angular.min.js",
			array('jquery')
		);
		wp_enqueue_script(
			"ng-sortable",
			plugin_dir_url(__FILE__)."/ng/lib/ui-sortable.js",
			array('angularjs')
		);
		wp_enqueue_script(
			"wp-custom-fields-search-editor",
			plugin_dir_url(__FILE__).'/js/wp-custom-fields-search-editor.js',
			array('jquery','jquery-ui-core','jquery-ui-widget','jquery-ui-sortable','angularjs','ng-sortable')
		);
		wp_enqueue_script(
			"wpcfs-angular-app",
			plugin_dir_url(__FILE__).'/ng/js/app.js',
			array('wp-custom-fields-search-editor')
		);
		wp_enqueue_script(
			"wp-handlers",
			plugin_dir_url(__FILE__).'/js/wp-handlers.js',
			array('wp-custom-fields-search-editor')
		);
	}

	function parse_request(&$wp){
		if(array_key_exists("wpcfs",$_REQUEST)){
			//exit();
		}
	}
	function show_search_template_for_searches($template){
		if($_REQUEST['wpcfs']){
			$template = "search";
		}
		return $template;
	}
	static function get_javascript_editor_config(){
		error_reporting(E_ALL);
		$inputs = apply_filters("wp_custom_fields_search_inputs",array());
		$datatypes = apply_filters("wp_custom_fields_search_datatypes",array());
		$comparisons = apply_filters("wp_custom_fields_search_comparisons",array());

		foreach($inputs as $k=>$input){
			$inputs[$k] = array(
				"id"=>$input->get_id(),
				"name"=>$input->get_name(),
				"options"=>$input->get_editor_options(),
			);
		}
		foreach($datatypes as $k=>$datatype){
			$datatypes[$k] = array(
				"id"=>$datatype->get_id(),
				"name"=>$datatype->get_name(),
				"options"=>$datatype->get_editor_options(),
			);
		}
		foreach($comparisons as $k=>$comparison){
			$comparisons[$k] = array(
				"id"=>$comparison->get_id(),
				"name"=>$comparison->get_name(),
				"options"=>$comparison->get_editor_options(),
			);
		}

		return array(
			"inputs"=>$inputs,
			"datatypes"=>$datatypes,
			"comparisons"=>$comparisons,

		);
	}
	function wp_custom_fields_search_inputs($inputs){
		require_once(dirname(__FILE__).'/engine.php');
		$inputs = $inputs + array(
			new WPCustomFieldsSearch_TextBoxInput(),
			new WPCustomFieldsSearch_SelectInput(),
		);
		return $inputs;
	}
	function wp_custom_fields_search_datatypes($datatypes){
		require_once(dirname(__FILE__).'/engine.php');
		$datatypes = $datatypes + array(
			new WPCustomFieldsSearch_PostField(),
			new WPCustomFieldsSearch_CustomField(),
		);
		return $datatypes;
	}
	function wp_custom_fields_search_comparisons($comparisons){
		require_once(dirname(__FILE__).'/engine.php');
		$comparisons = $comparisons+array(
			new WPCustomFieldsSearch_Equals(),
			new WPCustomFieldsSearch_WordsIn(),
			new WPCustomFieldsSearch_PhraseIn(),
			new WPCustomFieldsSearch_GreaterThan(),
			new WPCustomFieldsSearch_LessThan(),
			new WPCustomFieldsSearch_Range(),
		);
		return $comparisons;
	}
}
new WPCustomFieldsSearchPlugin();
