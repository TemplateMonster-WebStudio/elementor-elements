"use strict";

!(function($, frontend, elementor){

	/**
	 * Sample Js - module
	 */
	function RequiredJsModule(){}

	RequiredJsModule.prototype = {
		initialize: function(settings){
			console.log(settings);
		}
	}
	
	/**
	 * Plugin Frontend Javascript Module
	 */
	function ElementorElements(){
		$(window).on('elementor/frontend/init', this.onInit.bind(this));
	};

	ElementorElements.prototype = {
		widgets: {

			/**
			 * Demo Widget nodule
			 * demo-widget - is return value of get_name() method of widget class
			 * default - is skin name
			 */
			'demo-widget.default': function($scope){

				if('function' !== typeof RequiredJsModule){
					return;
				}

				var el = $scope.find('.elementor-widget-container > div');

				if(! el.length){
					return;
				}

				var settings = $.extend({
					default: 'settings'
				}, this.getSettings(el));

				var instance = new RequiredJsModule();

				instance.initialize(settings);
			},
		},

		/**
		 * Parse JSON settings from data attribute
		 */
		getSettings: function(el){

			try {

				var settings = $(el).attr('data-settings');
				settings = JSON.parse(settings);
			} catch(error) {

				settings = {};
			}

			return settings;
		},

		onInit: function(){

			for(var widget in this.widgets){
				frontend.hooks.addAction('frontend/element_ready/' + widget, this.widgets[widget].bind(this));
			}

			if (frontend.isEditMode()) {
				window.elementor.hooks.addAction('panel/open_editor/widget/demo-widget', function(panel, model, view) {
					//console.log({panel:panel, model:model, view:view});
					console.log(view);
				});
			}
		}
	};

	window.ElementorElements = new ElementorElements();


}(jQuery, window.elementorFrontend, window.elementor));