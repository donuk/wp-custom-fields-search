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
		/** Admin Hooks */
		add_action('widgets_init',array($this,"widgets_init"));
		add_action('admin_enqueue_scripts',array($this,"admin_enqueue_scripts"));

		/** Base Types*/
		add_filter("wp_custom_fields_search_inputs",array($this,"wp_custom_fields_search_inputs"));
		add_filter("wp_custom_fields_search_datatypes",array($this,"wp_custom_fields_search_datatypes"));
		add_filter("wp_custom_fields_search_comparisons",array($this,"wp_custom_fields_search_comparisons"));
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
			"wpcfs-angular-services",
			plugin_dir_url(__FILE__).'/ng/js/services.js',
			array('wp-custom-fields-search-editor')
		);
		wp_enqueue_script(
			"wp-handlers",
			plugin_dir_url(__FILE__).'/js/wp-handlers.js',
			array('wp-custom-fields-search-editor')
		);

		wp_enqueue_style(
			"wpcfs-angular-css",
			plugin_dir_url(__FILE__).'/ng/css/wp-custom-fields-search-editor.css'
		);
	}

	static function get_javascript_editor_config(){
		error_reporting(E_ALL);
		$inputs = apply_filters("wp_custom_fields_search_inputs",array());
		$datatypes = apply_filters("wp_custom_fields_search_datatypes",array());
		$comparisons = apply_filters("wp_custom_fields_search_comparisons",array());

		foreach($inputs as $k=>$input){
			$inputs[$k] = array(
				"id"=>$input->getId(),
				"name"=>$input->getName(),
				"options"=>$input->getEditorOptions(),
			);
		}
		foreach($datatypes as $k=>$datatype){
			$datatypes[$k] = array(
				"id"=>$datatype->getId(),
				"name"=>$datatype->getName(),
				"options"=>$datatype->getEditorOptions(),
			);
		}
		foreach($comparisons as $k=>$comparison){
			$comparisons[$k] = array(
				"id"=>$comparison->getId(),
				"name"=>$comparison->getName(),
				"options"=>$comparison->getEditorOptions(),
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
		$inputs = $inputs+array(
			new WPCustomFieldsSearch_TextBoxInput(),
			new WPCustomFieldsSearch_SelectInput(),
		);
		return $inputs;
	}
	function wp_custom_fields_search_datatypes($datatypes){
		require_once(dirname(__FILE__).'/engine.php');
		$datatypes = $datatypes+array(
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
