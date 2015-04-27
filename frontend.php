<?
/**
 * FIXME: 
 * Situations to consider...
 * 	posts.all datatype
 * 		with multiple words (e.g. if I type author name and title)
 *	or joins
 *	checkbox inputs
 *	custom fields
 *
 */
class WPCustomFieldsSearchFrontEnd {
	function __construct(){
		/** Front End Search */
		add_filter('posts_join',array($this,'posts_join'));
		add_filter('posts_where',array($this,'posts_where'));
		add_filter('posts_groupby',array($this,'posts_groupby'));
		add_filter('posts_orderby',array($this,'posts_orderby'));

		add_filter('home_template',array($this,'show_search_template_if_posted'));
		add_filter('page_template',array($this,'show_search_template_if_posted'));

		/* Relevanssi compatibility - Over-rides relevanssi changes when the search form has been used*/
		add_filter('posts_request',array(&$this,'store_search'),9);
		add_filter('posts_request',array(&$this,'restore_search'),11);
		add_filter('the_posts',array(&$this,'store_search'),9);
		add_filter('the_posts',array(&$this,'restore_search'),11);
	}

	function _is_search_request(){
		return $this->_get_posted_form();
	}

	function _load_keyed($key){
		$array = apply_filters($key,array());
		$keyed = array();
		foreach($array as $item){
			$keyed[$item->getId()] = $item;
		}
		return $keyed;
	}
	function _get_posted_form(){
		static $posted_form;
		$source_id = $_REQUEST['wpcfs-search-source'];
		if($source_id && !$posted_form){
			list($widget_type,$index)=explode('-',$source_id,2);
			if($widget_type!='wp_custom_fields_search_1_0')
				throw new Exception("Should this ever be different?");//TODO:Get rid of this
			$widgets = get_option('widget_'.$widget_type);
			$posted_form = json_decode($widgets[$index]['data'],true);

			$inputs = $this->_load_keyed("wp_custom_fields_search_inputs");
			$datatypes = $this->_load_keyed("wp_custom_fields_search_datatypes");
			$comparisons = $this->_load_keyed("wp_custom_fields_search_comparisons");

			$index = 0;
			foreach($posted_form['inputs'] as &$field){
				$field['index'] = ++$index;
				$field['input'] = $inputs[$field['input']];
				$field['datatype'] = $datatypes[$field['datatype']];
				$field['comparison'] = $comparisons[$field['comparison']];
			}
		}
		return $posted_form;
	}

	function posts_join($join){
		if($this->_is_search_request()){
			throw new Exception("Unimplemented");
		}
		return $join;
	}
	function posts_where($where){
		if($this->_is_search_request()){
			$form = $this->_get_posted_form();
			$extra_where = array();
			foreach($form['inputs'] as $params){
				if(!$params['input']->is_populated($params,$_REQUEST)){
					continue;
				}
				$field_names=$params['datatype']->get_sql_fields($params,$_REQUEST);
				$value=$params['input']->get_value($params,$_REQUEST);

				$sub_where = array();
				foreach($field_names as $field_name){
					$sub_where[] = $params['comparison']->get_sql_where_clause($params,$field_name,$value);
				}
				$extra_where[] = join(" OR",$sub_where);
			}
			$where = " (".join(" AND ",$extra_where).")";
		}
		return $where;
	}
	function posts_groupby($groupby){
		if($this->_is_search_request()){
			global $wpdb;
			$groupby = $wpdb->posts.".ID";
		}
		return $groupby;
	}
	function posts_orderby($orderby){
		if($this->_is_search_request()){
			throw new Exception("Unimplemented");
		}
		return $orderby;
	}

	function show_search_template_if_posted($template){
		if($this->_is_search_request()){
			$template = get_query_template("search");
		}
		return $template;
	}

        function store_search($sql){
                $this->sql = $sql;
                return $sql;
        }
        function restore_search($sql){
                if($this->_is_search_request()){
                        $sql = $this->sql;
                }
                return $sql;
        }
}
new WPCustomFieldsSearchFrontEnd();
