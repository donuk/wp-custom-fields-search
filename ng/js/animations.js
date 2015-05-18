"use strict";

angular.module('WPCFS')
.animation('.slide', function() {
	var NG_HIDE_CLASS = 'ng-hide';
	return {
		beforeAddClass: function(element, className, done) {
		    if(className === NG_HIDE_CLASS) {
			element.slideUp(done); 
		    }
		},
		removeClass: function(element, className, done) {
		    if(className === NG_HIDE_CLASS) {
			element.hide().slideDown(done);
		    }
		},
		enter: function(element, done) {
			element.hide().slideDown()
			return function(cancelled) {};
	    	},
		leave: function(element, done) { 
			element.slideUp();
		},
	}
});
