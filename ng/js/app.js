angular.module('WPCFS')
.controller('WPCFSForm', ['$scope','i18n',function ($scope,i18n) {
    $scope.min_height = 0;
    $scope.heights = {};
    $scope.set_min_height = function (height,name){
        $scope.heights[name] = height;
        var min_height = 0;
        angular.forEach($scope.heights,function(v,k){
            if(v>min_height) min_height=v;
        });
        $scope.min_height = min_height + 100;
    };
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

    i18n.dict.then(function(__){
    	$scope.add_field = function(){
	    	var new_field = {};
	    	$scope.form_fields.push(new_field);
            $scope.edit_field(new_field);
    	};
    });

    $scope.edit_field = function(field){
        $scope.popped_up_field = field;
    }
    $scope.remove_field = function(field) {
        $scope.form_fields.splice($scope.form_fields.indexOf(field),1);
    }
    $scope.close_edit_form = function(field){
        if(!field.label)
            $scope.remove_field(field);

        $scope.popped_up_field = null;
        $scope.set_min_height(0,"field");
    }

    $scope.show_settings_popup = function(){ $scope.settings_visible = true; }
    $scope.close_settings_popup = function(){ 
        $scope.settings_visible = false; 
        $scope.set_min_height(0,"field");
    }
}]).controller('WPCFSField', ['$scope', 'replace_all', 'i18n', function($scope, replace_all, i18n) {
    $scope.field = $scope.popped_up_field;
	if(!$scope.field.multi_match) $scope.field.multi_match="All";

    $scope.show_config_form = function(form,field){
        $scope.config_popup = {"form": form, "field": field};
    };
    $scope.close_config_popup = function(){
        $scope.config_popup = null;
        $scope.set_min_height(0,"sub_config");
    };
    i18n.dict.then(function(__){
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
        [ "input" , "datatype", "comparison" ].forEach(function(type){
            $scope.$watch("field."+type,function(new_option){
                try {
                    var config = $scope[type+"s"][new_option]['options'];
                } catch(err){ 
                    return false; 
                }

                if(config.defaults)
                    angular.forEach(config.defaults,function(v,k){
                        $scope.field[k] = angular.copy(v);
                    });
            });
        });
        $scope.$watch("field.datatype",function(){
            $scope.valid_comparisons = $scope.get_valid_comparisons();
        });
    });

}]).controller('WPCFSSettings', ['$scope', function($scope) {
    $scope.expand = function(page){
        $scope.expanded = page;
    };
    $scope.is_expanded = function(page){
        return $scope.expanded == page;
    };
    $scope.expanded = $scope.config.settings_pages[0];
}]).controller('ConfigPopup', ['$scope', function($scope) {
    $scope.include_file = $scope.config_popup.form;
    $scope.field = $scope.config_popup.field;
}]).controller('SelectController', ['$scope','i18n', function($scope,i18n) {
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

    i18n.dict.then(function(__){
       $scope.get_preset_title = function(preset){
            return preset.settings.form_title || preset.name || __("Untitled Preset");
       };
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
        $scope.is_selected_preset = function(preset){
            return preset == $scope.preset;
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
}]);
