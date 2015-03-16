/*
 * Copyright 2012 Don Benjamin
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 *
 * 	http://www.apache.org/licenses/LICENSE-2.0 
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License.
 */
(function($){
	 $.wh_plugin = function(name,methods,defaults){
		methods = $.extend({init: function(options){}},methods);
		defaults = $.extend({},defaults,methods.defaults);
		defaults.methods = methods;
		$.fn[name] = function( method ) {
			if(this.length==0) return;
			var methods = $.fn[name].methods;
			if ( methods[method] ) {
				// Method calls e.g. $('div').my_plugin('recalculate',...)
				my_args = Array.prototype.slice.call(arguments,0);
				my_args[0] = this.data(name);
                                return methods[method].apply( this, my_args );
                        } else if ( typeof method === 'object' || ! method ) {
				// Constructor e.g. $('div').my_plugin({...params...})
                                var options = ( typeof method === 'object' ) ? method : {};
                                options = $.extend({},$.fn[name].defaults,options);
                                this.data(name,options);
                                var return_value =  methods.init.apply( this, [options] );
				if (typeof return_value == 'undefined')
					return_value = this;
				return return_value;
                        } else {
                                $.error( 'Method ' +  method + ' does not exist on jQuery.'+name );
                        }
                };
                $.fn[name].methods = methods;
                $.fn[name].defaults = defaults;
        };
})(jQuery);
