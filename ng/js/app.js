angular.module('WPCFS', ['ui.sortable','ngAnimate'])
.controller('WPCFSForm', ['$scope', function ($scope) {
	$scope.sortableOptions = {
		"containment": "#field-list"
	};
	var array_values = function(dict){
		var result = [];
		for(var i in dict){
			result.push(dict[i]);
		};
		return result;
	};
	var array_keys = function(dict){
		var result = [];
		for(var i in dict){
			result.push(i);
		};
		return result;
	};
	$scope.add_field = function(){
		$scope.form_fields.push({
			"datatype":array_values($scope.datatypes)[0].id,
			"datatype_field":array_keys(array_values($scope.datatypes)[0].options.all_fields)[0],
			"input":array_values($scope.inputs)[0].id,
			"comparison":array_values($scope.comparisons)[0].id,
			"label":"Field "+($scope.form_fields.length+2)
		});
	};

}]).controller('WPCFSField', ['$scope', 'wpcsf_handler_lookup', function($scope, wpcsf_handler_lookup) {
	$scope.$watch("field.datatype",function(old_type,new_type){
		var datatype_options = $scope.datatypes[$scope.field.datatype];
		$scope.fields = datatype_options.options.all_fields;
		wpcsf_handler_lookup($scope,"datatype",old_type).deselected();
		wpcsf_handler_lookup($scope,"datatype",new_type).selected();
	});

	$scope.$watch("field.input",function(new_type,old_type){
		wpcsf_handler_lookup($scope,"input",old_type).deselected();
		wpcsf_handler_lookup($scope,"input",new_type).selected();
	});

	$scope.$watch("field.comparison",function(new_type,old_type){
		wpcsf_handler_lookup($scope,"comparison",old_type).deselected();
		wpcsf_handler_lookup($scope,"comparison",new_type).selected();
	});

}]).controller('SelectController', ['$scope', function($scope) {
	if(!$scope.field.any_message) $scope.field.any_message="Any";
	if(!$scope.field.options) $scope.field.options=[{"value":1,"label":"One"},{"value":2,"label":"Two"}];
	$scope.remove_option = function(option){
		var index = $scope.field.options.indexOf(option);
		$scope.field.options.splice(index,1);
	};
	$scope.add_option = function(){
		$scope.field.options.push({});
	};
}]);
