(function($){
	var handler_list = {
		"input":{},
		"comparison":{},
		"datatype":{},
	}
	var array2dict = function(arr,key_fun){
		if(!key_fun) key_fun = function(el){ return el.id };
		var output = {};
		arr.forEach(function(item){
			output[key_fun(item)] = item;
		});
		return output;
	};
	$.widget("wpcfs.wp_custom_fields_search_editor",{
		"options":{

		},
		"save": function(){
			this.options.value_element.val(JSON.stringify(this.options.form_config));
		},
		"_create": function(){
			//Instantiate Angular App and pass config into the angular environment

			this.element.addClass("wp_custom_fields_search_editor");
			this.options.value_element = $("<input type='hidden' name='"+this.options.field_name+"' value=''/>").appendTo(this.element);

			var angular_root = $("<div ng-controller='RootController' ng-include='partials+\"/form.html\"'></div>").appendTo(this.element);

			(function(widget){
				widget.save();
				$('.widget-control-actions input')
				.live('mouseenter',function(){ widget.save(); })
				.live('click',function(){ widget.save(); });
				angular.module('WPCFS')
				.controller('RootController', ['$scope', function ($scope) {
					$scope.root = widget.options.root+"ng/"; 
					$scope.partials = $scope.root+"partials/";
					$scope.config = widget.options;
					$scope.handlers = widget.handlers;

					$scope.datatypes  = array2dict($scope.config.building_blocks.datatypes); 
					$scope.inputs  = array2dict($scope.config.building_blocks.inputs);
					$scope.comparisons  = array2dict($scope.config.building_blocks.comparisons); 


					$scope.form_fields = $scope.config.form_config.inputs;
					if(!$scope.config.form_config.options){
						$scope.config.form_config.options = {};
					}
					$scope.options = $scope.config.form_config.options;
					if(!('include_frontend_css' in $scope.options)){
						$scope.options['include_frontend_css'] = true;
					}

					$scope.save = function(){
						widget.save();
					}
				}]);
			})(this);

			/* Render */
			angular.bootstrap(angular_root[0], ['WPCFS']);
		},
		"handlers": handler_list
	});
	$.wp_custom_fields_search_add_handler = function(type,name,handler){
		handler_list[type][name] = handler;
	};
})(jQuery);
