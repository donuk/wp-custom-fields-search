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
	var handler_list = {
		"input":{},
		"comparison":{},
		"datatype":{},
	}
	$.widget("wpcfs.wp_custom_fields_search_editor",{
		"options":{

		},
		"save": function(){
			this.options.value_element.val(JSON.stringify(this.options.form_config));
		},
		"_create": function(){
			this.element.addClass("wp_custom_fields_search_editor");
			this.options.ui = this;
			this.options.value_element = $("<input type='hidden' name='"+this.options.field_name+"' value=''/>").appendTo(this.element);
			this.options.display_element = $("<div></div>").appendTo(this.element);
			this.save();
			this.view("field_list");
		},
		"add_row": function(args){
			this.options.form_config.inputs.push($.extend({
				"datatype":this.options.building_blocks.datatypes[0].id,
				"input":this.options.building_blocks.inputs[0].id,
				"comparison":this.options.building_blocks.comparisons[0].id,
			},args));
			this.save();
			this.refresh();
		},
		"view":function(view){
			this.options.view = view;
			this["view_"+view]();
		},
		"refresh": function(){
			this["view_"+this.options.view]();
		},



		"view_field_list": function(list_element,args){
			this.options.display_element.html("");
			var field_list = $('<ul class="field-list"></ul>').appendTo(this.options.display_element);
			for(var i = 0 ; i<this.options.form_config.inputs.length ; i++){
				var input = this.options.form_config.inputs[i];
				var wrapper = $("<li class='form-element'></li>").appendTo(field_list);
				wrapper.data("index",i);

				var datatype_wrapper = $("<div class='datatype'></div>").appendTo(wrapper);

				var datatype_selector = $("<select class='datatype'></select>").appendTo(datatype_wrapper);
				for(var j = 0 ; j<this.options.building_blocks.datatypes.length ; j++){
					var datatype = this.options.building_blocks.datatypes[j];
					datatype_selector.append("<option value='"+datatype.id+"'>"+datatype.name+"</option>");
				}
				datatype_selector.val(input.datatype);
			
				var dropdown = $('<select class="field_name"></select>').appendTo(datatype_wrapper);
				var datatype = find_by_id(this.options.building_blocks.datatypes,input.datatype);
				var found = false;
				for(var field in datatype.options.all_fields){
					var option = $('<option/>').appendTo(dropdown);
					option.attr('value',field);
					option.html(datatype.options.all_fields[field]);
					
					if(field==input["datatype/field"]) found=true;
				}
				if(!found) input["datatype/field"]= array_keys(datatype.options.all_fields)[0];
				dropdown.val(input["datatype/field"]);
				(function(widget,input){
					dropdown.change(function(){
						input["datatype/field"] = $(this).val();
						widget.save();
					});
				})(this,input);

				(function(widget,input){
				 	var input_config = datatype.options.all_fields[input["datatype/field"]];
					var input_wrapper = $("<div class='input'></div>").appendTo(wrapper);
					var input_selector = $("<select class='input'></select>").appendTo(input_wrapper);
					var selected_type;
					widget.options.building_blocks.inputs.forEach(function(input_type,index){
						var option = $('<option/>').appendTo(input_selector);
						option.html(input_type.name);
						option.attr("value",input_type.id);
						if(input_type.id==input.input)
							selected_type = input_type;
					});
					input_selector.val(input.input);
					input_selector.change(function(){
						input['input'] = $(this).val();
						widget.save();
						widget.refresh();
					});
					if(selected_type){
						widget.input_handler(input_wrapper, input, selected_type);
					}
				})(this,input);

				var delete_wrapper=$("<a href='#' class='delete-link'>X</a>").appendTo(wrapper);
				(function(input,datatype_wrapper,options,widget,i){
					datatype_selector.bind('change',function(){
						input.datatype = $(this).val();
						widget.save();
						widget.refresh();
					});
					delete_wrapper.bind('click',function(){
						widget.options.form_config.inputs.splice(i,1);
						widget.save();
						widget.refresh();
						return false;
					});
				})(input,datatype_wrapper,this.options,this,i);


			}
			(function(widget){
				$('<a href="#">Add</a>').appendTo(widget.options.display_element).click(function(){
					widget.add_row();
					return false;
				});
				field_list.sortable({
					stop: function(event,ui){
						var inputs = widget.options.form_config.inputs,
							originalPosition = ui.item.data('index'),
							newPosition = ui.item.index(),

							input = inputs[originalPosition];

						inputs.splice(originalPosition,1);
						inputs.splice(newPosition,0,input);
						$(".form-element",field_list).each(function(index,el){
							$(el).data('index',index);
						});
						widget.save();
					}
				});
			})(this);
		},
		"input_handler": function(element,input_data,type_config){
			this.field_handler("input",element,input_data,type_config);
		},
		"field_handler": function(type,element,data,type_config){
			if(!('handler' in type_config.options)) return;

			if(data['handler'] != type_config.options.handler){
				// Clear options when switching handlers
				data['handler'] = type_config.options.handler;
				data['options']={};
			}

			//TODO: Should this be more OO?
			return handler_list[type][type_config.options.handler](element,data['options'],type_config,function(options){
				data['options'] = options;
				this.save()
			});
		},
		"handlers": handler_list
	});
	$.wp_custom_fields_search_add_handler = function(type,name,handler){
		handler_list[type][name] = handler;
	};
})(jQuery);
