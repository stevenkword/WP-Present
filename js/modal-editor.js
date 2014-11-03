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

			/*
			 * Open Actions
			 */

			// Add a callback to TinyMCE
			var preInit = tinyMCEPreInit.mceInit['wpp-modal-editor'];
			preInit.init_instance_callback = 'callbackTinyMCEloaded';

			// Initialize the editor
			tinyMCE.init(preInit);

			// When the user clicks cancel, close the modal
			$('.modal-buttons #cancel-button').click( function(event) {
				event.preventDefault();
				modal.close();
				tinyMCE.activeEditor.destroy();
			});

			// Bind the publish button
			$('.modal-buttons #publish-button').click( function(event) {
				event.preventDefault();
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

		function resizeModal() {
			var self = this;
			// Reside the TinyMCE Editor ( could be improved upon )
			var $editorIframe = $( '#wpp-modal-editor_ifr' );

			/* This constant value needs to be replaced */
			var resize = $('.modal-inner-right' ).height() - 125;

			$editorIframe.height( resize );
		}

	});

})(jQuery);

function callbackTinyMCEloaded(){
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
