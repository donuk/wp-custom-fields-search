(function($){
	$.wp_custom_fields_search_add_handler("input","select_handler",function(main_ui,instance_config,type_config,save){
		instance_config = $.extend({ 
				"source": "Auto",
				"allow_blank":true,
				"any_label": type_config.any_label,
			},instance_config);

		(function(select,options_ui){
			select.append("<option value='Auto'>"+objectL10n.source_auto+"</option>");
			select.append("<option value='Manual'>"+objectL10n.source_manual+"</option>");
			select.val(instance_config.source);

			var show_options = function(){
				options_ui.html("");
				if(instance_config.source=="Auto"){
					var rand_id = Math.random();
					(function(auto_ui){

					 })($(
					"<div>"+
						"<input type='checkbox' id='"+rand_id+"'/>"+
						"<label for='"+rand_id+"'>"+objectL10n.allow_blank+" Blank</label>"+
						"<input class='any-label'/>"+
					"</div>"
					).appendTo(options_ui));



				} else {

				}
			};
			select.change(function(){
				instance_config.source = $(this).val();
				save(instance_config);
				show_options();
			});
			show_options();
		})(
			$('<select/>').appendTo(main_ui),
			$('<div/>').appendTo(main_ui)
		);

	});
 })(jQuery);
