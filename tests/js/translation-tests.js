describe('translations', function(){
    beforeEach(module('WPCFS'));

    var $service, $httpBackend,i18n;

    beforeEach(inject(function(_$httpBackend_,_i18n_){
        $httpBackend =_$httpBackend_;
        i18n = _i18n_;
    }));

    describe('i18n_service', function(){
        it('Check against fixed translations dictionary',function(){
            console.log("Start test");

            $httpBackend.when("GET","ajax?action=wpcfs_ng_load_translations").respond(200,{
                "Bye":"Auf Wiedersehen",
                "Hello":"Hallo"
            });

            i18n.dict.then(function(__){
                expect(__("Hello")).toBe("Hallo");
            });

            i18n("Hello").then(function(translation){
                expect(translation).toBe("Hallo");
            });

            $httpBackend.flush();
        });
        
    });
    
});
