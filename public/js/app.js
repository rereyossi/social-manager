/*eslint no-unused-vars: ["error", { "vars": "local", "varsIgnorePattern": "^social" }]*/
(function( window, $ ) {

	'use strict';

	var socialButtonsContent,
		socialButtonsImage,
		socialButtonsModel,
		$template,
		$templateHTML;

	if ( _.isUndefined( window.nineCodesSocialManagerAPI ) ||
		 _.isUndefined( window.nineCodesSocialManagerAPI.id ) ) {
		return;
	}

	_.templateSettings = {
		interpolate: /\{\{(.+?)\}\}/g
	};

	window.nineCodesSocialManager = window.nineCodesSocialManagerAPI || {};

	nineCodesSocialManager.app = nineCodesSocialManager.app || {};
	nineCodesSocialManager.app.route = nineCodesSocialManager.root + nineCodesSocialManager.namespace;
	nineCodesSocialManager.app.sync = function(method, model, options) {

		_.extend(options, {
			url: nineCodesSocialManager.app.route + '/social-manager/buttons/' + (_.isFunction(model.url) ? model.url() : model.url)
		});

		return Backbone.sync(method, model, options);
	};

	nineCodesSocialManager.Buttons = nineCodesSocialManager.Buttons || {};
	nineCodesSocialManager.Buttons = {
		Collection: {},
		Model: {},
		View: {}
	};

	nineCodesSocialManager.Buttons.Model = Backbone.Model.extend({
		sync: nineCodesSocialManager.app.sync,
		defaults: {
			id: null,
			content: {},
			images: []
		}
	});

	nineCodesSocialManager.Buttons.View = Backbone.View.extend({

		el: 'body',

		initialize: function() {

			$template = $( this.template );

			if ( 0 === $template.length ) {
				console.info( 'Template ' + this.template + ' is not available.' );
				return;
			}

			$templateHTML = $template.html().trim();

			if ( '' === $templateHTML ) {
				console.info( 'Template HTML of ' + this.template + ' is empty.' );
				return;
			}

			this.template = _.template( $templateHTML );
			this.listenTo( this.model, 'change:id', this.render );
		},

		buttonDialog: function( event ) {

			event.preventDefault();
			event.stopImmediatePropagation();

			var target = event.currentTarget,
				source = target.getAttribute( 'href' );

			if ( ! source || '' !== source ) {
				this.windowPopup( source );
				return;
			}

			return;
		},

		windowPopup: function( url ) {

			var wind = window,
				docu = document,
				screenLeft = undefined !== wind.screenLeft ? wind.screenLeft : screen.left,
				screenTop = undefined !== wind.screenTop ? wind.screenTop : screen.top,
				screenWidth = wind.innerWidth ? wind.innerWidth : docu.documentElement.clientWidth ? docu.documentElement.clientWidth : screen.width,
				screenHeight = wind.innerHeight ? wind.innerHeight : docu.documentElement.clientHeight ? docu.documentElement.clientHeight : screen.height,

				width = 560,
				height = 430,
				divide = 2,

				left = screenWidth / divide  -  width / divide   + screenLeft,
				top = screenHeight / divide  -  height / divide   + screenTop,

				newWindow = wind.open( url, '', 'scrollbars=no,width=' + width + ',height=' + height + ',top=' + top + ',left=' + left );

			if ( newWindow ) {
				newWindow.focus();
			}
		}
	});

	nineCodesSocialManager.Buttons.View.Content = nineCodesSocialManager.Buttons.View.extend({

		template: '#tmpl-buttons-content',

		events: {
			'click [data-social-buttons="content"] a': 'buttonDialog'
		},

		render: function( model ) {

			var resp = model.toJSON();

			$( '#' + nineCodesSocialManager.attrPrefix + '-buttons-' + resp.id )
				.append( this.template({
					data: resp.content
				}) );

			return this;
		}
	});

	nineCodesSocialManager.Buttons.View.Images = nineCodesSocialManager.Buttons.View.extend({

		template: '#tmpl-buttons-image',

		events: {
			'click [data-social-buttons="image"] a': 'buttonDialog'
		},

		render: function( model ) {

			var self = this,
				resp = model.toJSON(),
				$images = $( '.' + nineCodesSocialManager.attrPrefix + '-buttons--' + resp.id );

			$images.each( function( index ) {
				$( this ).append( self.template({
					data: resp.images[index]
				}) );
			});

			return this;
		}
	});

	socialButtonsModel = new nineCodesSocialManager.Buttons.Model();
	socialButtonsModel.url = nineCodesSocialManager.id;
	socialButtonsModel.fetch();

	socialButtonsContent = new nineCodesSocialManager.Buttons.View.Content({
		model: socialButtonsModel
	});

	socialButtonsImage = new nineCodesSocialManager.Buttons.View.Images({
		model: socialButtonsModel
	});

})(window, jQuery );
