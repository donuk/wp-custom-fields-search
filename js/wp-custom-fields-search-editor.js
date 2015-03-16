(function($){
 	var find_by_id = function(arr,id){
		for(var i = 0 ; i<arr.length ; i++){
			if(arr[i].id==id)
				return arr[i];
		}
		return null;
	};
	var array_keys = function(dict){
		var arr = [];
		for(var key in dict){
			arr.push(key);
		}
		return arr;
	};
	$.wh_plugin("wp_custom_fields_search_editor",{
		"defaults":{

		},
		"init": function(params){
			this.addClass("wp_custom_fields_search_editor");
			params.ui = this;
			params.value_element = $("<input type='hidden' name='"+params.field_name+"' value=''/>").appendTo(this);
			params.display_element = $("<div></div>").appendTo(this);
			this.wp_custom_fields_search_editor("view","field_list");
		},
		"add_row": function(params,args){
		console.log("Adding Row");
			params.form_config.inputs.push($.extend({
				"datatype":params.building_blocks.datatypes[0].id,
				"input":params.building_blocks.inputs[0].id,
				"comparison":params.building_blocks.comparisons[0].id,
			},args));
			console.log(params.form_config);
			this.wp_custom_fields_search_editor('refresh');
		},
		"view":function(params,view){
			params.view = view;
			this.wp_custom_fields_search_editor("view_"+view);
		},
		"refresh": function(params){
			this.wp_custom_fields_search_editor("view_"+params.view);
		},
		"view_field_list": function(params,list_element,args){
			params.display_element.html("");
			var field_list = $('<ul class="field-list"></ul>').appendTo(params.display_element);
			for(var i = 0 ; i<params.form_config.inputs.length ; i++){
				var input = params.form_config.inputs[i];
				var wrapper = $("<li class='form-element'></li>").appendTo(field_list);

				var datatype_wrapper = $("<div class='datatype'></div>").appendTo(wrapper);
				var datatype_selector = $("<select class='datatype'></select>").appendTo(datatype_wrapper);
				for(var j = 0 ; j<params.building_blocks.datatypes.length ; j++){
					var datatype = params.building_blocks.datatypes[j];
					datatype_selector.append("<option value='"+datatype.id+"'>"+datatype.name+"</option>");
				}
				datatype_selector.val(input.datatype);
				this.wp_custom_fields_search_editor("select_datatype",input,datatype_wrapper);
				(function(input,datatype_wrapper){
					datatype_selector.bind('change',function(){
						input.datatype = $(this).val();
						params.ui.wp_custom_fields_search_editor('select_datatype',input,datatype_wrapper);
					});
				})(input,datatype_wrapper);

			}
			$('<a href="#">Add</a>').appendTo(params.display_element).click(function(){
				params.ui.wp_custom_fields_search_editor('add_row');
				return false;
			});
		},
		"select_datatype": function(params,input,wrapper){
			wrapper.find('select.field_name').remove();
			var dropdown = $('<select class="field_name"></select>').appendTo(wrapper);
			var datatype = find_by_id(params.building_blocks.datatypes,input.datatype);
			var found = false;

			for(var field in datatype.options.all_fields){
				var option = $('<option/>').appendTo(dropdown);
				option.attr('value',field);
				option.html(datatype.options.all_fields[field]);
				
				if(field==input["datatype/field"]) found=true;
			}
			if(!found) input["datatype/field"]= array_keys(datatype.options.all_fields)[0];
			dropdown.val(input["datatype/field"]);
			dropdown.change(function(){
				input["datatype/field"] = $(this).val();
			});
		}

	});
})(jQuery);
