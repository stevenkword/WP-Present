/**
 * WP Present - admin.js
 *
 */

var WPPAdmin;
(function($) {

	WPPAdmin = function() {
		this.init();
		return this;
	}

	WPPAdmin.prototype = {
		init: function() {
			var self = this;

			self.widgetButtonExpand();
			self.widgetButtonEdit();
			self.widgetButtonDelete();
			self.widgetButtonAdd();
			self.widgetButtonTidy();
			self.uiSetup();
			self.columnHandle();

			// Select the first column on load
			self.activateColumn( $('#col-1').children('.widget-top').children('.widget-title') );

			return self;
		},

		// Sortables and such
		uiSetup: function () {
			var self = this;

			// Make the outer container resizeable
			$( "#outer-container" ).resizable();

			self.backfillSlides();

			$( ".column-inner" ).sortable({
				connectWith: ".column-inner",
				stop: function( event, ui ) {
					self.updateColumns();
				}
			});

			$( "#container" ).sortable({
				stop: function( event, ui ) {
					self.updateColumns();
				}
			});

			self.updateColumns();
			$( ".column-inner" ).disableSelection();

			// Append an inner column to each column that doesn't contain any slides.
			jQuery('.column' ).not(":has(div.column-inner)").append('<div class="column-inner ui-sortable"></div>');

			$( ".column-inner" ).sortable({
				connectWith: ".column-inner",
				stop: function( event, ui ) {
					self.updateColumns();
				}
			});
		},

		/**
		 * Close Modal
		 */
		closeModal: function() {
			tinymce.execCommand('mceRemoveControl',true,'editor_slide');
			$( '#dialog' ).dialog( "close" );
		},

		/**
		 * Backfill Slides
		 */
		backfillSlides: function () {
			var numSlides = WPPNumSlides;
			var numExisting = $('#container > .column').size();

			for (var col=numExisting+1;col<=numSlides;col++){
				$('#container').append( '<div class="column ui-sortable" id="col-'+col+'"><div class="widget-top"><div class="widget-title"><h4 class="hndle">'+col+'<span class="in-widget-title"></span></h4></div></div></div>' );
			}
			$('#container').append( '<div style="clear: both;"></div>' );
		},

		/**
		 * Make the column the target for adding new columns
		 */
		activateColumn: function ( $col ) {
			// Remove the active class from all columns
			$('.widget-title').css('background', '');
			$('.widget-title').css('color', '');
			$('.widget-title').removeClass('active');

			// Select the given column
			$col.css('background-color', '#e14d43').css('color','#ffffff');
			$col.addClass('active');
		},

		/**
		 * update columns
		 */
		updateColumns: function () {
			var self = this;
			var columns = { }; // Creates a new object
			var i = 1;
			$( '.column-inner' ).each(function( index ) {
				var $order = $(this).sortable( "toArray" );
				columns['col-'+i] = $order;
				i++;
			});
			var encoded = JSON.stringify( columns );
			$('#description').val(encoded);

			// Send this change off to ajax land
			self.updatePresentation();
		},

//Dude this is not okay. Fix the post data ajax call

		/**
		 * AJAX request to update the current presentation taxonomy
		 */
		updatePresentation: function () {
			var params = { content: $('#description').val() };
			$.ajax({
				url: ajaxurl + '?action=update_presentation&id=' + presentation,
				type: 'POST',
				data: jQuery.param(params),
				beforeSend: function( xhr ) {
					$('.spinner').show();
				},
				complete: function( xhr ) {
					$('.spinner').hide();
				},
				success: function(result) {
					// Return the excerpt from the editor
					//console.log('presentation updated');
				}
			});
		},


		/**
		 * Bind Tidy button
		 */
		widgetButtonTidy: function () {
			var self = this;
			$('.action-buttons').on('click', '#tidy-button', function(e) {
				e.preventDefault();
				this.consolidateColumns();
				// Why does this break?
				//updatePresentation();
			});
		},

		/**
		 * Expand slide details
		 */
		widgetButtonExpand: function () {
			$('.column').on('click', '.widget-title-action', function(e) {
				$( this ).parents('.widget').children('.widget-inside').toggle();
			});
		},

		/**
		 * The action taken by the Tidy button
		 */
		consolidateColumns: function () {
			var self = this;
			var numCols = $('.column').length;
			// console.log(numCols);

			$('.column').each(function(outerIndex){

				// Don't have a col 0
				var outerIndex = outerIndex + 1;

				// Fixes the condition where we are looking at the last item
				if( outerIndex >= numCols )
					return;

				var $outerCol = $(this);
				var $innerCol = $outerCol.children('.column-inner');

				var innerHTML = $innerCol.html().trim();
				var outerHTML = $outerCol.html().trim();

				if( typeof(innerHTML) !== 'string' || '' == innerHTML ) {
					var $nextOuterCol = $('#col-'+(outerIndex+1));
					var $nextInnerCol = $nextOuterCol.children('.column-inner');

					var nextInnerHTML = $nextInnerCol.html().trim();
					var nextOuterHTML = $nextOuterCol.html().trim();

					$outerCol.html(nextOuterHTML);
					$nextOuterCol.html(outerHTML);
				}
			} );

			//Finally refresh description array
			$( ".column-inner" ).sortable({
				connectWith: ".column-inner",
				stop: (function( event, ui ) {
					self.updateColumns();
				}),
				create: (function( event, ui ) {
					self.updateColumns();
				})
			});
			self.updateColumns();
		},

		columnHandle: function () {
			var self = this;
			$('.column').children('.widget-top').on('click', '.widget-title', function(e) {
				$col = $(this);
				self.activateColumn($col);
			});
		},

		// Bind Edit button
		widgetButtonEdit: function () {
			var self = this;
			$('.column').on('click', '.widget-control-edit', function(e) {
				e.preventDefault();
				var $button = $(this);
				var $parentWidget = $button.parents('.widget');
				var $widgetPreview = $parentWidget.find('.widget-preview');

				// Send the contents from the widget to the editor
				var contentEditor  = $widgetPreview.html();
				var widgetID = $parentWidget.find('.slide-id').val();

				$('#editor_slide-tmce').click(); //Necessary on subsequent loads of the editor
				$( "#dialog" ).dialog({
				  autoOpen: true,
				  width: 640,
				  height: 640,
				  modal: false,
				  buttons: {
					"Update": function() {
						var editorContents = tinymce.get('editor_slide').getContent();
						var params = { content:editorContents };
						// Send the contents of the existing post
						$.ajax({
							url: ajaxurl + '?action=update_slide&id=' + widgetID,
							type: 'POST',
							data: jQuery.param(params),
							beforeSend: function( xhr ) {
								$('.spinner').show();
							},
							complete: function( xhr ) {
								$('.spinner').hide();
							},
							success: function(result) {
								// Return the excerpt from the editor
								$widgetPreview.html( result );
						  }
						});
						self.closeModal();
					},
					Cancel: function() {
						self.closeModal();
					}
				  },
				  create: function() {
						tinymce.execCommand('mceRemoveControl',true,'editor_slide');
						tinymce.execCommand('mceAddControl',true,'editor_slide');
				  },
				  open: function() {
						// Load the contents of the existing post
						$.ajax({
							url: ajaxurl + '?action=get_slide&id=' + widgetID,
							beforeSend: function( xhr ) {
								$('.spinner').show();
							},
							complete: function( xhr ) {
								$('.spinner').hide();
							},
							success: function(contentEditor) {
								tinymce.get('editor_slide').setContent(contentEditor);
							}
						});

						// Hack for getting the reveal class added to tinymce editor body
						// @todo: look at wp_editor in wp/inc/class-wp-editor.php
						var $editorIframe = $('#editor_slide_ifr').contents();
						$editorIframe.find('body').addClass('reveal');
						$editorIframe.css('height','500px');

				  },
				  close: function() {
						self.closeModal();
				  }
				});
			});
		},

		// Bind Delete button
		widgetButtonDelete: function () {
			var self = this;
			$('.column').on('click', '.widget-control-remove', function(e) {
				e.preventDefault();

				var confirmDelete = confirm("You are about to permanently delete the selected items. 'Cancel' to stop, 'OK' to delete.");
				if( false == confirmDelete )
					return;

				var $button = $(this);
				var $parentWidget = $button.parents('.widget');
				var widgetID = $parentWidget.find('.slide-id').val();
				var params = null;

				$.ajax({
					url: ajaxurl + '?action=delete_slide&id=' + widgetID,
					type: 'POST',
					//data: jQuery.param(params),
					beforeSend: function( xhr ) {
						$('.spinner').show();
					},
					complete: function( xhr ) {
						$('.spinner').hide();
					},
					success: function(result) {
						$parentWidget.remove();
						self.updateColumns();
					}
				});
			});
		},

		// Bind Add button
		widgetButtonAdd: function () {
			var self = this;
			$('.action-buttons').on('click', '#add-button', function(e) {
				e.preventDefault();
				var $button = $(this);
				var $activeColumn = $('.widget-title.active').parent('.widget-top').parent('.column').children('.column-inner');

				$('#editor_slide-tmce').click(); //Necessary on subsequent loads of the editor
				$( "#dialog" ).dialog({
				  autoOpen: true,
				  width: 600,
				  height: 600,
				  modal: false,
				  buttons: {
					"Publish": function() {
						var editorContents = tinymce.get('editor_slide').getContent();
						var params = { content:editorContents };
						// Send the contents of the existing post
						$.ajax({
							url: ajaxurl + '?action=new_slide&presentation=' + presentation,
							type: 'POST',
							data: jQuery.param(params),
							success: function(result) {
								$activeColumn.append(result);
								self.updateColumns();
						  }
						});
						self.updateColumns();
						self.closeModal();
					},
					Cancel: function() {
						self.closeModal();
					}
				  },
				  create: function() {
						tinymce.execCommand('mceRemoveControl',true,'editor_slide');
						tinymce.execCommand('mceAddControl',true,'editor_slide');
				  },
				  open: function() {
						// Clear the editor
						tinymce.get('editor_slide').setContent('');
						// Hack for getting the reveal class added to tinymce editor body
						var $editorIframe = $('#editor_slide_ifr').contents();
						$editorIframe.find('body').addClass('reveal');
				  },
				  close: function() {
						self.closeModal();
				  }
				});
			});
		}

	};

})(jQuery);
var slideManager = new WPPAdmin();