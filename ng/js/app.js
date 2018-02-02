angular.module('WPCFS', ['ui.sortable'])
.factory('i18n',['$q','$http', function($q,$http){
    if(typeof __ != 'undefined') {
        var i18n = function(phrase){
            var d = $q.defer();
            d.resolve(__(phrase));
            return d.promise;
        };

        var d2 = $q.defer();
        d2.resolve(__);
        i18n.dict = function() { return d2.promise };
        return i18n;
    }

    var translations;
    var get_translations = function(){
        if(!translations) translations = $http.get(ajaxurl+"?action=wpcfs_ng_load_translations");
        return translations;
    };

    var service = function(phrase){
        return service.i18n(phrase);
    }

    service.i18n = function(phrase){
        return get_translations().then(function(response){
            if(response.data[phrase])
                return response.data[phrase];
            else
                return phrase;
        });
    };
    service.dict = function(){
        return get_translations().then(function(response){
            return function(k){
                return response.data[k];
            };
        });
    };

    return service;
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
.factory('replace_all', function(){
    return function(string,replacements){
        angular.forEach(replacements,function(v,k){
            string = string.replace(k,v);
        });
        return string;
    };
})
.controller('WPCFSForm', ['$scope','i18n',function ($scope,i18n) {
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

    i18n.dict().then(function(__){
    	$scope.add_field = function(){
	    	$scope.form_fields.push({"label": __("Untitled Field"), "expand":true});
    	};
    });

    $scope.remove_field = function(field) {
        $scope.form_fields.splice($scope.form_fields.indexOf(field),1);
    }

    angular.forEach($scope.form_fields,function(field){
        field.expand = false;
    });

}]).controller('WPCFSField', ['$scope', 'replace_all', 'i18n', function($scope, replace_all, i18n) {
	if(!$scope.field.multi_match) $scope.field.multi_match="All";
    i18n.dict().then(function(__){
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
                                        throw replace_all(__("Cannot restrict by type {type} in {comparison}"),
                                            { '{type}':type, '{comparison}':comparison.name});
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
    });

}]).controller('SelectController', ['$scope','i18n', function($scope,i18n) {
    i18n.dict().then(function(__){
    	if(!$scope.field.any_message) $scope.field.any_message=__("Any");
	    if(!$scope.field.options) $scope.field.options=[{"value":1,"label":__("One")},{"value":2,"label":__("Two")}];
    });
	$scope.remove_option = function(option){
		var index = $scope.field.options.indexOf(option);
		$scope.field.options.splice(index,1);
	};
	$scope.add_option = function(){
		$scope.field.options.push({});
	};
}]).controller('PresetsController', [ '$scope', '$filter', '$http', 'i18n', function ($scope,$filter,$http,i18n) {
   $scope.form_config = [];
   angular.forEach($scope.config.form_config,function(preset){
        $scope.form_config.push(preset);
   });
   $scope.presets = $scope.form_config;
    $scope.preset = null;

    i18n.dict().then(function(__){
       $scope.add_preset = function(){
            var preset = {
                "name": __("Untitled Preset"),
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

        $scope.export_settings_href = ajaxurl+"?action="+$scope.config.export_callback;
        $scope.warn_no_import = function(){
            alert(__("There is currently no import functionality, the settings export is for debug use only"));
        };
    });

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
        //$scope.preset.modified=true;
    });
}]);
