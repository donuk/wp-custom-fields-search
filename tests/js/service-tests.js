describe('services', function(){
    beforeEach(module('WPCFS'));

    var replace_all;

    beforeEach(inject(function(_replace_all_){
        replace_all = _replace_all_;
    }));

    describe('replace_all', function(){
        it('Check replace_all with strings',function(){
            expect(replace_all("test",{"cake":"cheese"})).toBe("test")
            expect( replace_all("test",{"TEST":"no-match"})).toBe("test")
            expect(replace_all("test",{"test":"changed"})).toBe("changed")
            expect(replace_all("test",{"test2":"no-match","test":"changed"})).toBe("changed")

            expect(replace_all("test test",{"test":"changed"})).toBe("changed test")

            expect(replace_all("{a} {b}",{"{a}":"A","{b}":"B"})).toBe('A B')
        });

/*
        it('Check replace_all with RegEx',function(){
            expect( replace_all("test",[ [/cake/,"cheese"] ])).toBe("test")
            expect( replace_all("test",[ [/test/,"changed"] ])).toBe("changed")
            expect( replace_all("test",[ [/TEST/,"changed"]])).toBe("test")
            expect( replace_all("test",[[/TEST/i,"changed"]])).toBe("changed")

            expect( replace_all("test test",[[/test/,"changed"]])).toBe("changed test")
            expect( replace_all("test test",[ [/test/g,"changed"]])).toBe("changed")
        });
        */
    });
    
});

test_suite_loaded("services");
