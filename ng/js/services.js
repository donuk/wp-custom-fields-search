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
        i18n.dict = d2.promise;
        return i18n;
    }

    var translations = $http.get(ajaxurl+"?action=wpcfs_ng_load_translations");

    var i18n = function(phrase){
        return translations.then(function(response){
            if(response.data[phrase])
                return response.data[phrase];
            else
                return phrase;
        });
    };
    i18n.dict = translations.then(function(response){
        return function(k){
            return response.data[k];
        };
    });
    return i18n;
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
        angular.forEach(replacements,function(k,v){
            string = string.replace(k,v);
        });
        return string;
    };
})
.directive("wpcfsHeightSource", function(){
    return {
        "restrict": "A",
        "link": function(scope,elem,attrs) {
            var source_field = attrs.wpcfsHeightSource;
            scope.$watch(function(){
                scope.set_min_height(elem.height(), source_field);
            });
        }
    }
})
.directive("focusMe", [ '$timeout', function($timeout){
    return {
        "restrict": "A",
        "link": function(scope,elem,attrs) {
            $timeout(function(){
                elem[0].focus();
            });
        }
    }
}]);
