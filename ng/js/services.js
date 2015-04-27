"use strict";

angular.module('WPCFS')
.factory('wpcsf_handler_lookup',[ '$injector', 'wpcfs_type_handler', function($injector, wpcfs_type_handler){
	return function($scope,type_type,type_name){
		var type_def = $scope[type_type+"s"][type_name];
		var type_class = null;
		if(type_def && type_def.options.handler){
			type_class = $injector.get(type_def.options.handler);
		} else {
			type_class = wpcfs_type_handler;
		}
		console.log("handler_lookup",type_type,type_def,type_class);
		return new type_class($scope,type_type,type_name);
	};
}])
.factory('wpcfs_type_handler', [ function(){
	var TypeHandler = function TypeHandler($scope,type,name){
		this.field = $scope.field;
		this.type = type;
		this.name = name;
	};
	var do_nothing = function do_nothing(){};
	TypeHandler.prototype = {
		"do_nothing": do_nothing,
		"selected": do_nothing,
		"deselected": do_nothing,
	};
	return TypeHandler;
}])
.factory('wpcfs_select_handler', [ 'wpcfs_type_handler', function(wpcfs_type_handler){
	//TODO: Should this behaviour be in the super class?? e.g. a default_values field??
	var SelectHandler = function SelectHandler(){
		wpcfs_type_handler.apply(this,arguments);
	};
	SelectHandler.prototype = jQuery.extend({},wpcfs_type_handler.prototype,{
		"selected": function(){
			if(!('source' in this.field)){
				this.field.source="Auto";
			}
		},
	});
	return SelectHandler;
}])
;


