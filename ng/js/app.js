angular.module('WPCFS', ['ui.sortable'])
.factory('i18n',['$http', function($http){
    var translations = $http.get(ajaxurl+"?action=wpcfs_ng_load_translations");
    return function(phrase){
        return translations.then(function(response){
            if(response.data[phrase])
                return response.data[phrase];
            else
                return phrase;
        });
    };
}])
.directive('i18n',[ 'i18n', function(i18n){
   return {
        "link": function(scope,element,attrs,controller,transcludeFn){
            i18n(element.html()).then(function(translation){
                element.replaceWith(translation);
            });
        }
    }
}])
.controller('WPCFSForm', ['$scope',function ($scope) {
    $scope.datatypes  = array2dict($scope.config.building_blocks.datatypes); 
    $scope.inputs  = array2dict($scope.config.building_blocks.inputs);
    $scope.comparisons  = array2dict($scope.config.building_blocks.comparisons); 

    var pull_config = function(){
        $scope.form_fields = $scope.config.form_config.inputs;
        if(!$scope.config.form_config.settings) $scope.config.form_config.settings = {};
        $scope.settings = $scope.config.form_config.settings;
    };
    pull_config();
    $scope.$watch('config.form_config.id',pull_config);



	$scope.sortableOptions = {
		"containment": "#field-list"
	};
    $scope.tab = "fields";
    $scope.tabs = [ "fields", "settings" ];
    $scope.set_tab = function(tab){ $scope.tab = tab; };
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
		$scope.form_fields.push({"label": objectL10n.untitled_field, "expand":true});
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
                    angular.forEach(comparison['options']['valid_for'],function(restrictions,type){
                        angular.forEach(restrictions,function(value){
                            switch(type){
                                case 'datatype':
                                    var datatype = $scope.config.building_blocks.datatypes.find(function(element){ return element.id==$scope.field.datatype});
                                    if(datatype && datatype.options.labels){
                                        valid = valid && (datatype.options.labels.indexOf(value)>-1);
                                    }
                                    else valid=false;
                                    break;
                                default:
                                    throw objectL10n.replace('{type}',type).replace('{comparison}',comparison.name);
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
	if(!$scope.field.any_message) $scope.field.any_message=objectL10n.any_message;
	if(!$scope.field.options) $scope.field.options=[{"value":1,"label":objectL10n.one},{"value":2,"label":objectL10n.two}];
	$scope.remove_option = function(option){
		var index = $scope.field.options.indexOf(option);
		$scope.field.options.splice(index,1);
	};
	$scope.add_option = function(){
		$scope.field.options.push({});
	};
}]).controller('PresetsController', [ '$scope', '$filter', '$http', function ($scope,$filter,$http) {
   $scope.form_config = $scope.config.form_config;
   if(!$scope.form_config) $scope.form_config = [];
   $scope.presets = $scope.form_config;
    $scope.preset = null;

   $scope.add_preset = function(){
        var preset = {
            "name": objectL10n.untitled_preset,
            "unsaved": true,
            "id": 1,
            "inputs": [],
            "modified": false,
            "state": "New",
        };
        while($filter('filter')($scope.presets,function(other){ return preset.id==other.id; }).length>0)
            preset.id+=1;
        $scope.presets.push(preset);
        $scope.edit_preset(preset);
   };

   $scope.edit_preset = function(preset){
        $scope.preset = preset;
   };

    $scope.save_preset = function(preset){
        preset.state = "Saving";
        data = angular.copy(preset);
        data.action = $scope.config.save_callback;

        $http({
            "method":"POST",
            "url":ajaxurl,
            "data": "action="+data.action+"&data="+$filter('json')(data)+"&nonce="+$scope.config.save_nonce,
            "headers": {'Content-Type': 'application/x-www-form-urlencoded'}
        }).then(function(){
            preset.state="Saved";
            preset.modified=false;
        },function(){
            preset.state="Error";
        });
    };

    if($scope.presets.length==0){
        $scope.add_preset();
    } else if(!$scope.preset){
        $scope.preset = $scope.presets[0];
    }
}]).controller('PresetController', [ '$scope', function ($scope) {

    var update_child_config = function(){
        $scope.config = {
            "form_config": $scope.preset,
            "building_blocks": $scope.config.building_blocks,
        };
    };
    $scope.$watch("preset",update_child_config);
    update_child_config();
}]).controller('PresetModifiedController', [ '$scope', function($scope){
    $scope.$watch("preset",function(){
        $scope.preset.modified=true;
    });
}]);
