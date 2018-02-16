describe('translations', function(){
    beforeEach(module('WPCFS'));

    var $httpBackend, i18n, $q;

    beforeEach(inject(function(_$httpBackend_,_i18n_,_$q_){
        $httpBackend =_$httpBackend_;
        i18n = _i18n_;
        $q = _$q_;
    }));

    describe('i18n_service', function(){
        
        it('Check against fixed translations dictionary',function(){

            $httpBackend.when("GET","ajax?action=wpcfs_ng_load_translations").respond(200,{
                "Bye":"Auf Wiedersehen",
                "Hello":"Hallo"
            });

            i18n.dict().then(function(__){
                expect(__("Hello")).toBe("Hallo");
            });

            i18n("Hello").then(function(translation){
                expect(translation).toBe("Hallo");
            });

            $httpBackend.flush();
        });
        
    });

    var $compile, $rootScope;
    beforeEach(inject(function(_$compile_,_$rootScope_){
        $compile = _$compile_;
        $rootScope = _$rootScope_.$new();
    }));

    describe('i18n_directive', function(){
        it('Check correct words replaced',function(){

            spyOn(i18n,"i18n").and.callFake(function(t){ var d = $q.defer(); d.resolve(t.toUpperCase()); return d.promise; });

            var element = $compile(angular.element("<a><i18n>Hello</i18n> World</a>"))($rootScope);
            $rootScope.$digest();
            
            expect(element.text()).toBe("HELLO World");

            element = $compile(angular.element("<a><i18n>Hello</i18n> <i18n>World</i18n></a>"))($rootScope);
            $rootScope.$digest();
            
            expect(element.text()).toBe("HELLO WORLD");
        });
        
    });
});
test_suite_loaded("translations");
