/**
 * WP Present - admin.js
 *
 */

// Hide the description fields as fast as possible
/*
jQuery('label[for="description"]').parent().parent().hide();
jQuery('#tag-description').closest('.form-field').hide();
jQuery('#description').closest('.form-field').hide();
jQuery('.column-description').hide();
*/

var SlideManager
(function($) {

	SlideManager = function() {
		this.init();
		return this;
	}

	SlideManager.prototype = {
		init: function() {
			// Select the first column on load
			this.activateColumn( $('#col-1').children('.widget-top').children('.widget-title') );
			return this;
		},

		setup: function () {
			var self = this;
		},

		/**
		 * Backfill Slides
		 */
		backfillSlides: function () {
			var numSlides = WPP_NumSlides;
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
			$('.widget-title').removeClass('active');

			// Select the given column
			$col.css('background-color', '#2ea2cc').css('color','#ffffff');
			$col.addClass('active');
		},

		/**
		 * Close Modal
		 */
		closeModal: function() {
			tinymce.execCommand('mceRemoveControl',true,'editor_slide');
			$( '#dialog' ).dialog( "close" );
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

		/**
		 * AJAX request to update the current presentation taxonomy
		 */
		updatePresentation: function () {
			var params = { content: $('#description').val() };
			$.ajax({
				url: ajaxurl + '?action=update_presentation&id=' + presentation,
				type: 'POST',
				data: jQuery.param(params),
				success: function(result) {
					// Return the excerpt from the editor
					console.log('presentation updated');
				}
			});
		}
	};

})(jQuery);
var slideManager = new SlideManager();

// jQuery Modal Things
jQuery(document).ready(function($) {

	function activateColumn( $col ) {
		$('.widget-title').css('background-color', '').css('color', 'inherit');
		$('.widget-title').removeClass('active');
		//if there are no active columns.
		$col.css('background-color', '#2ea2cc').css('color','#ffffff');
		$col.addClass('active');
	}

	// Update Taxonomy
	function updatePresentation() {
		var params = { content: $('#description').val() };
		$.ajax({
			url: ajaxurl + '?action=update_presentation&id=' + presentation,
			type: 'POST',
			data: jQuery.param(params),
			success: function(result) {
				// Return the excerpt from the editor
				console.log('presentation updated');
			}
		});
	};

	/**
	 * Close Modal
	 */
	function closeModal() {
		tinymce.execCommand('mceRemoveControl',true,'editor_slide');
		$( '#dialog' ).dialog( "close" );
	}

	/**
	 * Backfill Slides
	 */
	function backfillSlides() {
		var numSlides = WPP_NumSlides;
		var numExisting = $('#container > .column').size();

		//alert( 'existing: ' + numExisting + ', needed: ' + numSlides );

		for (var col=numExisting+1;col<=numSlides;col++){
			$('#container').append( '<div class="column ui-sortable" id="col-'+col+'"><div class="widget-top"><div class="widget-title"><h4 class="hndle">'+col+'<span class="in-widget-title"></span></h4></div></div></div>' );
		}
		$('#container').append( '<div style="clear: both;"></div>' );
	}

	function updateColumns() {

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
		updatePresentation();
	}

	// AKA "Tidy Button"
	function consolidateColumns(){

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
				updateColumns();
			}),
			create: (function( event, ui ) {
				updateColumns();
			})
		});
		updateColumns();
	}

	function columnHandle(){
		$('.column').children('.widget-top').children('.widget-title').on('click', function() {
			$col = $(this);
			activateColumn($col);
		});
	}

	// Bind Edit button
	function widgetButtonEdit() {
		$('.widget-control-edit').on('click', function(e) {

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
						  	success: function(result) {
								// Return the excerpt from the editor
								$widgetPreview.html( result );
						  }
						});
						closeModal();
					},
					Cancel: function() {
						closeModal();
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
						  success: function(contentEditor) {
						  		tinymce.get('editor_slide').setContent(contentEditor);
						  }
						});

						// Hack for getting the reveal class added to tinymce editor body
						var $editorIframe = $('#editor_slide_ifr').contents();
						$editorIframe.find('body').addClass('reveal');



				  },
				  close: function() {
						closeModal();
				  }
				});
		});
	}

	// Bind Delete button
	function widgetButtonDelete() {
		$('.widget-control-remove').on('click', function(e) {
			e.preventDefault();

			var $button = $(this);
			var $parentWidget = $button.parents('.widget');
			var widgetID = $parentWidget.find('.slide-id').val();
			var params = null;

			$.ajax({
				url: ajaxurl + '?action=delete_slide&id=' + widgetID,
				type: 'POST',
				//data: jQuery.param(params),
				success: function(result) {
					$parentWidget.remove();
					updateColumns();
				}
			});
		});
	}

	// Bind Add button
	function widgetButtonAdd() {
		$('#add-button').on('click', function(e) {

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
						updateColumns();
				  }
				});
				updateColumns();
				closeModal();
			},
			Cancel: function() {
				closeModal();
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
				closeModal();
		  }
		});
		});
	}

	// Bind Tidy button
	function widgetButtonTidy() {
		$('#tidy-button').click(function(e) {
			e.preventDefault();
			consolidateColumns();
			// Why does this break?
			//updatePresentation();
		});
	}

	/**
	 * Expand slide details
	 */
	function widgetButtonExpand() {
		$('.widget-title-action').on('click', function(e) {
			$( this ).parents('.widget').children('.widget-inside').toggle();
		});
	}

	// Sortables and such
	function uiSetup() {
		var self = this;

		// Make the outer container resizeable
		$( "#outer-container" ).resizable();

		// Resize the container assuming only 1 slide per columnx
		// 25px is to allow for the padding between cells
		//$('#container').width( ( $( ".portlet" ).length ) * ($( ".column" ).width()+25) );

		backfillSlides();

		$( ".column-inner" ).sortable({
			connectWith: ".column-inner",
			stop: function( event, ui ) {
				updateColumns();
			}
		});

		$( "#container" ).sortable({
			stop: function( event, ui ) {
				updateColumns();
			}
		});

		updateColumns();

		$( ".column-inner" ).disableSelection();

		// Append an inner column to each column that doesn't contain any slides.
		jQuery('.column' ).not(":has(div.column-inner)").append('<div class="column-inner ui-sortable"></div>');

		$( ".column-inner" ).sortable({
			connectWith: ".column-inner",
			stop: function( event, ui ) {
				updateColumns();
			}
		});
	}

	widgetButtonEdit();
	widgetButtonDelete();
	widgetButtonAdd();
	widgetButtonTidy();
	widgetButtonExpand();
	uiSetup();
	columnHandle();
});