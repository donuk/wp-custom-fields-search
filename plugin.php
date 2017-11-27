<?php
/*
Plugin Name: WP Custom Fields Search
Plugin URI: http://www.webhammer.co.uk/wp-custom-fields-search
Description: Adds powerful search forms to your wordpress site
Version: 1.1.8
Author: Don Benjamin
Author URI: http://www.webhammer.co.uk/
Text Domain: wp_custom_fields_search
*/
define('WPCFS_PLUGIN_VERSION',"1.1.8");
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


require_once(dirname(__FILE__).'/functions.php');

class WPCustomFieldsSearchValidationException extends Exception {}
class WPCustomFieldsSearchPlugin {
	function __construct(){
		add_action('widgets_init',array($this,"widgets_init"));
		add_action('admin_enqueue_scripts',array($this,"admin_enqueue_scripts"));
        add_action('admin_menu', array($this,'admin_menu'));
        add_action('admin_init', array($this,'admin_init'));

        add_action('wp_ajax_wpcfs_save_preset',array($this,'save_preset'));
        add_action('wp_ajax_wpcfs_export_settings',array($this,'export_settings'));

        add_action('wp_ajax_wpcfs_ng_load_translations',array($this,'ng_load_translations'));

		add_filter("wp_custom_fields_search_inputs",array($this,"wp_custom_fields_search_inputs"));
		add_filter("wp_custom_fields_search_datatypes",array($this,"wp_custom_fields_search_datatypes"));
		add_filter("wp_custom_fields_search_comparisons",array($this,"wp_custom_fields_search_comparisons"));

        add_shortcode("wp-custom-fields-search",array($this,"shortcode"));
        add_shortcode("wpcfs-preset",array($this,"preset_shortcode"));
        add_action("parse_query",array($this,"parse_query"));
        add_action('plugins_loaded',array($this,'plugins_loaded'));

		if($this->is_search_submitted()){
			add_filter('template_include',array($this,'show_search_results_template'),99);
			add_filter('posts_orderby',array($this,'posts_orderby'));
			add_filter('posts_join',array($this,'posts_join'));
			add_filter('posts_where',array($this,'posts_where'));
			add_filter('posts_groupby',array($this,'posts_groupby'));
			add_filter('get_search_query',array($this,'get_search_query'));
		}
	}

	function is_search_submitted(){
		return $_REQUEST['wpcfs'];
	}

	function get_submitted_form(){
		static $submitted;
		if(!isset($submitted)){
			$wpcfs = $_REQUEST['wpcfs'];
			if(substr($wpcfs,0,23)=="wp-custom-fields-search"){
				$options = get_option("widget_wp-custom-fields-search");
				$submitted = json_decode($options[substr($wpcfs,24)]['data'],true);
			} elseif(substr($wpcfs,0,7)=="preset-") {
				$config = get_option("wp-custom-fields-search");
                $submitted = $config['presets'][substr($wpcfs,7)];
            } else {
				$submitted = false;
			}
			if($submitted){
				require_once(dirname(__FILE__).'/engine.php');
				$index = 0;
				foreach($submitted['inputs'] as $k=>&$input){
                    try {
                        $input['datatype'] = wpcfs_instantiate_class($input['datatype']);
                        $input['input'] = wpcfs_instantiate_class($input['input']);
                        $input['comparison'] = wpcfs_instantiate_class($input['comparison']);
                    } catch(WPCustomFieldsSearchClassException $e){
                        error_log("WP Custom Fields Search - get_submitted_form() ".$e->getMessage());
                        unset($inputs[$k]);
                        continue;
                    }
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
		foreach($this->get_submitted_inputs() as $input){
            $join = $input['datatype']->add_joins($input,$join,count($input['input']->get_submitted_value($input,$_REQUEST)));
		}
		return $join;
	}
	function posts_where($where){
        $request = stripslashes_deep($_REQUEST);
		foreach($this->get_submitted_inputs() as $input){
			$submitted = $input['input']->get_submitted_values($input,$request);
			$wheres = array();
            $join = ($input['multi_match'] == "Any") ? "OR" : "AND";
            $submitted_index = 0;
            foreach($submitted as $value){
                $sub_wheres = array();
                foreach($input['datatype']->get_field_aliases($input,$submitted_index) as $alias){
	    			$sub_wheres[]= $input['comparison']->get_where($input,$value,$alias);
		    	}
                
                $wheres[] = "(".join(" OR ",$sub_wheres).")";
                
                $submitted_index++;
            }
			$where.=" AND ( ".join(" $join ",$wheres)." )"; #TODO: Make the AND/OR configurable
		}
		return $where;
	}

    function get_submitted_inputs(){
        $form = $this->get_submitted_form();
        $inputs = array();
        foreach($form['inputs'] as $input){
			if($input['input']->is_submitted($input,$_REQUEST)) 
                $inputs[] = $input;
        }
        return $inputs;
    }

    function get_search_query($query){
        $description = array();
        foreach($this->get_submitted_inputs() as $input){
            $description[] = $this->describe_search($input);
        }
        return join(__(" &amp; "),$description);
    }
    function describe_search($input){
        $label = $input['label'];
        $found = array();
        foreach($input['input']->get_submitted_values($input,$_REQUEST) as $value){
            $found[] = $input['comparison']->describe($label,$value);
        }
        $join = ($input['multi_match'] == "Any") ? __(" or ") : __(" &amp; ");
        return join($found," $join ");
    }
	function widgets_init(){
		require_once(dirname(__FILE__).'/widget.php');
		register_widget("WPCustomFieldsSearchWidget");
        wp_enqueue_style("wpcfs-form",plugin_dir_url(__FILE__).'templates/form.css');
	}

	function admin_enqueue_scripts(){
		wp_enqueue_script(
			"angularjs",
			plugin_dir_url(__FILE__)."/js/angular.min.js",
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

        wp_register_style( 'wpcfs_css', plugins_url("wp-custom-fields-search") . '/ng/css/editor.css', false, '1.0.0' );
        wp_enqueue_style( 'wpcfs_css' );
	}

    function admin_menu(){
        add_menu_page('WP Custom Fields Search Presets','WP Custom Fields Search', 'manage_options','wp-custom-fields-search',array($this,'presets_page'));
    }
    function admin_init(){
        $previous_version = get_option("wp-custom-fields-search-version");
        $current_version = WP_CUSTOM_FIELDS_SEARCH_VERSION;
        if($previous_version != $current_version){
            $this->upgrade_plugin($previous_version,$current_version);
            update_option("wp-custom-fields-search-version",$current_version);
        }
    }
    function plugins_loaded(){
        load_plugin_textdomain('wp-custom-fields-search',false,dirname( plugin_basename(__FILE__)).'/languages/');
    }

    function upgrade_plugin($old_version,$latest_version){
	if(!current_user_can('manage_options')) return;
        if(!$old_version){
            require_once(dirname(__FILE__).'/migrations/migrate-from-legacy-plugin.php');
	    wpcfs_upgrade_3_x_to_1_0();
        }
    }

    function presets_page(){
        if($_POST){
            // Save something...
            // Return
        }
        $config = get_option("wp-custom-fields-search");
        $presets = $config['presets'];
        include(dirname(__FILE__).'/templates/presets-page.php');

    }
    function save_preset($data){
        if(!(check_ajax_referer("wpcfs_save_preset","nonce",false) && current_user_can('manage_options'))) {
            header("HTTP/1.1 403 Forbidden");
            throw new Exception("403 Forbidden");
        }

        try {
            $data = json_decode(wpcfs_strip_hash_keys(stripslashes($_POST['data'])),true);
            $id = $data['id'];
            if($data===null) throw new WPCustomFieldsSearchValidationException("data is required");
            $config = get_option("wp-custom-fields-search");

            if(!$config['presets']) $config['presets'] = array();
            $config['presets'][$id] = $data;

            update_option("wp-custom-fields-search",$config);
            echo "OK";
        } catch(WPCustomFieldsSearchValidationException $e){
            header("HTTP/1.1 400 Invalid Data");
            echo "Error {$e->getMessage()}";
            throw $e;
        } catch(Exception $e){
            header("HTTP/1.1 500 Internal Error");
            echo "Error {$e->getMessage()}";
            throw $e;
        }
    }

    function parse_query($wpquery){
        if($this->is_search_submitted()){
            $wpquery->is_search = true;
            $wpquery->is_home = false;
        }
    }
	function show_search_template_for_searches($template){
		if($_REQUEST['wpcfs']){
			$template = "search";
		}
		return $template;
	}
	static function get_javascript_editor_config(){
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
			new WPCustomFieldsSearch_CheckboxInput(),
			new WPCustomFieldsSearch_RadioButtons(),
			new WPCustomFieldsSearch_HiddenInput(),
		);
		return $inputs;
	}
	function wp_custom_fields_search_datatypes($datatypes){
		require_once(dirname(__FILE__).'/engine.php');
		$datatypes = $datatypes + array(
			new WPCustomFieldsSearch_PostField(),
			new WPCustomFieldsSearch_CustomField(),
			new WPCustomFieldsSearch_Category(),
			new WPCustomFieldsSearch_Tag(),
		);
		return $datatypes;
	}
	function wp_custom_fields_search_comparisons($comparisons){
		require_once(dirname(__FILE__).'/engine.php');
		$comparisons = $comparisons+array(
			new WPCustomFieldsSearch_Equals(),
			new WPCustomFieldsSearch_TextIn(),
			new WPCustomFieldsSearch_GreaterThan(),
			new WPCustomFieldsSearch_LessThan(),
			new WPCustomFieldsSearch_Range(),
			new WPCustomFieldsSearch_SubCategoryOf(),
		);
		return $comparisons;
	}

    function preset_shortcode($atts){
        $atts = shortcode_atts(array(
            "id"=>"0"
        ),$atts);
        $this->show_preset($atts['id']);
    }
    function shortcode($atts){
        $atts = shortcode_atts(array(
            "preset"=>"0"
        ),$atts);
        $this->show_preset($atts['preset']);
    }
    static function show_preset($id){
        if($id=="default") $id=0;
        require_once("search_form.php");
        $config = get_option("wp-custom-fields-search");
        $preset = $config['presets'][$id];
        include(dirname(__FILE__).'/templates/preset-display.php');
    }

    function ng_load_translations(){
        $files = apply_filters("wpcfs_ng_translation_files",array(dirname(__FILE__).'/ng/translations.php'));
        $all_translations = array();
        foreach($files as $file){
            require($file);
            $all_translations = array_merge($all_translations,$translations);
        }
        header("Content-type: application/json");
        echo json_encode($all_translations);
        exit(0);
    }
    function export_settings(){
        $export = array(
            "doc_type"=>"wp-custom-fields-search full export",
            "plugin_version"=>WPCFS_PLUGIN_VERSION,
            "format_version"=>"1",
            "presets"=>get_option("wp-custom-fields-search"),
            "widget"=>get_option("widget_wp-custom-fields-search"),
            "sidebars"=>get_option("sidebars_widgets"),
        );
        header("Content-type:text/json");
        echo json_encode($export);
        exit();
    }
}
new WPCustomFieldsSearchPlugin();
function wpcfs_show_preset($id){
    WPCustomFieldsSearchPlugin::show_preset($id);
}
if(!function_exists('wp_custom_fields_search')){
    function wp_custom_fields_search($id="default"){
        return wpcfs_show_preset($id);
    }
}
