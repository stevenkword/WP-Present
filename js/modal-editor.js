(function($) {


	function alertMe(){
		alert('me');
	}

	$(document).ready( function() {
		// Create a modal view.
		var modal = new wp.media.view.Modal({
			// A controller object is expected, but let's just pass
			// a fake one to illustrate this proof of concept without
			// getting console errors.
			controller: { trigger: function() {} }
		});
		// Create a modal content view.
		var ModalContentView = wp.Backbone.View.extend({
			template: wp.template( 'modal-content' )
		});

		// When the user clicks a button, open a modal.
		$('.js--open-media-modal').click( function( event ) {
			event.preventDefault();

			// Assign the ModalContentView to the modal as the `content` subview.
			// Proxies to View.views.set( '.media-modal-content', content );
			modal.content( new ModalContentView() );
			// Out of the box, the modal is closed, so we need to open() it.
			modal.open();

			// Add a callback to TinyMCE
			var preInit = tinyMCEPreInit.mceInit['wpp-modal-editor'];
			preInit.init_instance_callback = 'callbackTinyMCEloaded';

			// Initialize the editor
			tinyMCE.init(preInit);

			// Clear the form out before we show it
			//WPPresentAdmin.prototype.resetModal();

			/*
			 * Open Actions
			 */
			$('#update-button').hide();
			$('#publish-button').show();

			var $activeColumn = $('.widget-title.active').parent('.widget-top').parent('.column').children('.column-inner');
			var nonce = $('#wp-present-nonce').val();

			// When the user clicks cancel, close the modal
			$('.modal-buttons #cancel-button').click( function(event) {
				event.preventDefault();
				modal.close();
				tinyMCE.activeEditor.destroy();
			});

			/*
			 * Button bindings
			 */
			// Bind the publish button
			$('.modal-buttons #publish-button').click( function(event) {
				event.preventDefault();

				var $activeColumn      = $('.widget-title.active').parent('.widget-top').parent('.column').children('.column-inner');
				var nonce              = $('#wp-present-nonce').val();
				var editorContents     = tinyMCE.activeEditor.getContent(); // tinymce.get('editor_slide').getContent();
				var postTitle          = $( '#slide-title' ).val();
				var backgroundImage    = $('#customize-control-wp_present_background_image img');
				var backgroundImageURL = '';
				var colorBackground    = $('#customize-control-wp_present_background_color .color-picker-hex').val();
				var colorText          = $('#customize-control-wp_present_text_color .color-picker-hex').val();
				var colorLink          = $('#customize-control-wp_present_link_color .color-picker-hex').val();

				// Sanitize the background Image URL
				if( 'none' != backgroundImage.css( 'display' ) ) {
					backgroundImageURL = backgroundImage.attr('src');
				} else {
					backgroundImageURL = '';
				}

				// Setup parameters for the AJAX request
				var params = {
					'content'          : editorContents,
					'title'            : postTitle,
					'background-image' : backgroundImageURL,
					'background-color' : colorBackground,
					'text-color'       : colorText,
					'link-color'       : colorLink,
					'nonce'            : nonce
				};

				// Send an AJAX request to add a new slide
				$.ajax({
					url: ajaxurl + '?action=new_slide',
					type: 'POST',
					data: jQuery.param(params),
					success: function(result) {
						$activeColumn.append(result);
						WPPNumSlides[0]++;
						WPPresentAdmin.prototype.refreshUI();
						WPPresentAdmin.prototype.updateTaxonomyDescription();
					}
				}).done( function() {
					modal.close();
					tinyMCE.activeEditor.destroy();
				});
			});

			// Bind the publish button
			$('.modal-buttons #update-button').click( function(event) {
				event.preventDefault();
			});

			// Bind the escape key -- interferes with media modal add attachments
			/*
			$(document).keyup(function(e){
				if(e.keyCode === 27) {
					modal.close();
					tinyMCE.activeEditor.destroy();
				}
			});
			*/

		}); // .js--open-media-modal

		// When the user clicks a button, open a modal.
		$('.widget-control-edit').click( function( event ) {
			event.preventDefault();

			// Assign the ModalContentView to the modal as the `content` subview.
			// Proxies to View.views.set( '.media-modal-content', content );
			modal.content( new ModalContentView() );
			// Out of the box, the modal is closed, so we need to open() it.
			modal.open();

			// Add a callback to TinyMCE
			var preInit = tinyMCEPreInit.mceInit['wpp-modal-editor'];
			preInit.init_instance_callback = 'callbackTinyMCEloaded';

			// Initialize the editor
			tinyMCE.init(preInit);

			// Clear the form out before we show it
			//WPPresentAdmin.prototype.resetModal();

			/*
			 * Open Actions
			 */
			$('#update-button').show();
			$('#publish-button').hide();

			/*
			 * Load the existing content
			 */
			var $editorIframe = $('#editor_slide_ifr').contents();

			var $button        = $(this);
			var $parentWidget  = $button.parents('.widget');
			var $widgetPreview = $parentWidget.find('.widget-preview');
			var $widgetTitle   = $parentWidget.find( '.widget-title h4' );

			// Send the contents from the widget to the editor
			var widgetID       = $parentWidget.find('.slide-id').val();
			var nonce          = $('#wp-present-nonce').val();

			// Load the contents of the existing post
			var params = { 'id':widgetID, 'nonce':nonce };
			$.ajax({
				url: ajaxurl + '?action=get_slide',
				data: jQuery.param(params),
				beforeSend: function() {
					$('.spinner').show();
				},
				complete: function() {
					$('.spinner').hide();
				},
				success: function( contentEditor ) {

					// If this throws an error, check for PHP notices in the ajax response
					var slide = jQuery.parseJSON( contentEditor );
					tinyMCE.activeEditor.setContent( slide.post_content ); //tinymce.get( 'editor_slide' ).setContent( slide.post_content );

					$( '#slide-title' ).val( slide.post_title );
					$( '#slide-slug' ).val( slide.post_name );
					$('.theme-name').html( slide.post_title );
					$('.preview-notice-text').html('You are editing');

					// Background
					var api = wp.customize, backgroundImage = api.instance( 'wp_present_background_image' );

					if( false !== slide.post_thumbnail_url ) {
						backgroundImage.set(''); // Necessary because the thumbnail image wont init without a change event
						backgroundImage.set(slide.post_thumbnail_url);
						$( '#editor_slide_ifr' ).contents().find('.reveal').css("background-image", "url('"+slide.post_thumbnail_url+"')");
					}

					// Colors
					$('#customize-control-wp_present_background_color .color-picker-hex').iris( 'color', slide.background_color );
					$('#customize-control-wp_present_text_color .color-picker-hex').iris( 'color', slide.text_color );
					$('#customize-control-wp_present_link_color .color-picker-hex').iris( 'color', slide.link_color );

					// This has to be the most hacky thing in this entire project
					self.resizeModal();

					// Hack for getting the reveal class added to tinymce editor body
					// @todo: look at wp_editor in wp/inc/class-wp-editor.php
					var $editorIframe = $('#editor_slide_ifr').contents();
					$editorIframe.find('body').addClass('reveal');
				}
			});

			/*
			 * Button bindings
			 */
			// When the user clicks cancel, close the modal
			$('.modal-buttons #cancel-button').click( function(event) {
				event.preventDefault();
				modal.close();
				tinyMCE.activeEditor.destroy();
			});

			$('.modal-buttons #update-button').click( function(event) {
				event.preventDefault();

				var editorContents     = tinyMCE.activeEditor.getContent(); // tinymce.get('editor_slide').getContent();
				var postTitle          = $( '#slide-title' ).val();
				var backgroundImage    = $('#customize-control-wp_present_background_image img');
				var backgroundImageURL = '';

				if( 'none' != backgroundImage.css( 'display' ) ) {
					backgroundImageURL = backgroundImage.attr('src');
				} else {
					backgroundImageURL = '';
				}

				var colorBackground = $('#customize-control-wp_present_background_color .color-picker-hex').val();
				var colorText       = $('#customize-control-wp_present_text_color .color-picker-hex').val();
				var colorLink       = $('#customize-control-wp_present_link_color .color-picker-hex').val();

				var params = {
					'id'               : widgetID,
					'content'          : editorContents,
					'title'            : postTitle,
					'background-image' : backgroundImageURL,
					'background-color' : colorBackground,
					'text-color'       : colorText,
					'link-color'       : colorLink,
					'nonce'            : nonce
				};

				// Send the contents of the existing post
				$.ajax({
					url: ajaxurl + '?action=update_slide',
					type: 'POST',
					data: jQuery.param(params),
					beforeSend: function() {
						$('.spinner').show();
					},
					complete: function() {
						//$('.spinner').hide();
					},
					success: function(result) {
						$('.spinner').hide();
						// Return the excerpt from the editor
						$widgetPreview.html( result );
						$widgetTitle.text( postTitle );
					}
				});

				// Destruct
				modal.close();
				tinyMCE.activeEditor.destroy();
			});

		});
	});

})(jQuery);

function callbackTinyMCEloaded(){

	// Resize the editor to fit nicely inside the modal frame
	resizeModal();

	// Load existing content or set to null here
	tinyMCE.activeEditor.setContent('lorem ipsum foo bar');

	// Debug
	var debug = 1;
	if( debug === 1 ){
		console.log(tinyMCE);
		console.log(tinyMCEPreInit.mceInit);
		console.log(tinyMCE.activeEditor);
	}
}

function resizeModal() {
	var self = this;

	// I would really lie to get the value for the iFrame from tinyMCE.activeEditor.getSomething()

	// Reside the TinyMCE Editor ( could be improved upon )
	var $editorIframe = jQuery( '#wpp-modal-editor_ifr' );

	/* This constant value needs to be replaced */
	var resize = jQuery('.modal-inner-right' ).height() - 125;

	$editorIframe.height( resize );
}
