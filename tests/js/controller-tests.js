describe('controllers', function(){
    beforeEach(module('WPCFS'));

    var $scope, createController;

    beforeEach(inject(function($rootScope,$controller){
        $scope = $rootScope.$new();
        $scope.config = {
            building_blocks: {
                datatypes:[],
                inputs:[],
                comparisons:[],
            },
            form_config: {
                inputs: [],
                settings: {},
            } 
        };

        createController = function(name,injections){
            console.log("CONTROLLER",$controller,name,injections);
            return $controller(name,injections);
        };
    }));

    describe('WPCFSForm', function(){
        var mock_i18n = {
            dict: function(){
               return { 
                    then: function(fn){
                       fn(function(s){ return s; }); 
                    }
               }; 
            }
        };
        it('test_edit_field',function(){
            createController("WPCFSForm",{"$scope": $scope, "i18n": mock_i18n});

            $scope.edit_field("test");
            expect($scope.popped_up_field).toBe("test");
        });

        it('test_close_edit_form',function(){
            createController("WPCFSForm",{"$scope": $scope, "i18n": mock_i18n});

            $scope.popped_up_field="test";
            $scope.close_edit_form();
            expect($scope.popped_up_field).toBe(null);
        });
    });
    
});

test_suite_loaded("controllers");
