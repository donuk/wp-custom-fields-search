angular.module('WPCFS', ['ui.sortable'])
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
		$scope.form_fields.push({"label": "Untitled Field", "expand":true});
	};

    $scope.remove_field = function(field) {
        $scope.form_fields.splice($scope.form_fields.indexOf(field),1);
    }

    angular.forEach($scope.form_fields,function(field){
        field.expand = false;
    });

}]).controller('WPCFSField', ['$scope', function($scope) {
	if(!$scope.field.multi_match) $scope.field.multi_match="All";
	$scope.$watch("field.datatype",function(){
		var datatype_options = $scope.datatypes[$scope.field.datatype];
		$scope.fields = datatype_options ? datatype_options.options.all_fields : [];
	});

    $scope.get_valid_comparisons = function(){
        var comparisons = [];
        angular.forEach($scope.config.building_blocks.comparisons,
            function(comparison){
                var valid = true;
                if(!comparison['options']){
                    valid = false;
                } else if(comparison['options']['valid_for']){
                    console.log(comparison);
                    angular.forEach(comparison['options']['valid_for'],function(restrictions,type){
                        angular.forEach(restrictions,function(value){
                            switch(type){
                                case 'datatype':
                                    var datatype = $scope.config.building_blocks.datatypes.find(function(element){ return element.id==$scope.field.datatype});
                                    if(datatype && datatype.options.labels){
                                        console.log(datatype.options.labels,value);
                                        valid = valid && (datatype.options.labels.indexOf(value)>-1);
                                    }
                                    else valid=false;
                                    break;
                                default:
                                    throw "Cannot restrict by type '"+type+"' in '"+comparison.name+"'";
                            }
                        });
                    });
                }

                if(valid)
                    comparisons.push(comparison);
            }
       );
        return comparisons;
    };
    $scope.$watch("field.datatype",function(){
        $scope.valid_comparisons = $scope.get_valid_comparisons();
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
