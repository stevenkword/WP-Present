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

// jQuery Modal Things
jQuery(document).ready(function($) {

	if( 'undefined' == typeof(tinymce) ) {
//		alert( 'tinymce init' );
//		tinymce.execCommand('mceRemoveControl',true,'editor_slide');
//		tinymce.execCommand('mceAddControl',true,'editor_slide');
	}


	/* Column click columns */
	function activateColumn( $col ) {
		$('.widget-title').css('background', '');
		$('.widget-title').removeClass('active');
		//if there are no active columns.
		$col.css('background', 'cyan');
		$col.addClass('active');
	}
	activateColumn( $('#col-1').children('.widget-top').children('.widget-title') );

	$('.column').children('.widget-top').children('.widget-title').on('click', function() {
		$col = $(this);
		activateColumn($col);
	});


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
		  width: 600,
		  height: 600,
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

//				tinymce.ScriptLoader.load('customizer.js');

				// Load a script from a specific URL using the global script loader
		        //tinymce.ScriptLoader.load('http://localhost/sandbox/wp-includes/js/admin-bar.min.js');

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
						alert('you have to refresh');
						$activeColumn.css('background','lime');
				  }
				});
				updateColumns();
				//updatePresentation();
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
				// Hack for getting the reveal class added to tinymce editor body
				var $editorIframe = $('#editor_slide_ifr').contents();
				$editorIframe.find('body').addClass('reveal');
		  },
		  close: function() {
				closeModal();
		  }
		});
	});


	// Delete Slide
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

	function updatePresentation( $description ) {
		var params = { content:$('#description').val() };
		$.ajax({
			url: ajaxurl + '?action=update_presentation&id=' + presentation,
			type: 'POST',
			data: jQuery.param(params),
			success: function(result) {
				// Return the excerpt from the editor
				//$widgetPreview.html( result );
				console.log('presentation updated');
			}
		});
	};

	function closeModal() {
		tinymce.execCommand('mceRemoveControl',true,'editor_slide');
		$( '#dialog' ).dialog( "close" );
//		console.log('tinymce shutdown');
	}


	// Expand slide details
	$('.widget-title-action').on('click', function(e) {
		$( this ).parents('.widget').children('.widget-inside').toggle();
	});



	function backfillSlides() {
		var numSlides = WPP_NumSlides;
		var numExisting = $('#container > .column').size();

		//alert( 'existing: ' + numExisting + ', needed: ' + numSlides );

		for (var col=numExisting+1;col<=numSlides;col++){
			$('#container').append( '<div class="column ui-sortable" id="col-'+col+'"><div class="widget-top"><div class="widget-title"><h4 class="hndle">'+col+'<span class="in-widget-title"></span></h4></div></div></div>' );
		}
		$('#container').append( '<div style="clear: both;"></div>' );
	}

	// Make the outer container resizeable
	$( "#outer-container" ).resizable();

	// Resize the container assuming only 1 slide per column
	// 25px is to allow for the padding between cells
	$('#container').width( ( $( ".portlet" ).length ) * ($( ".column" ).width()+25) );

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

	// Not part of the other stuff;
	$('#tidy-button').click(function(e) {
		e.preventDefault();
		consolidateColumns();
	});

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
	}

	function consolidateColumns(){ // AKA "Tidy Button"

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
	} // end consolidate

	// Append an inner column to each column that doesn't contain any slides.
	jQuery('.column' ).not(":has(div.column-inner)").append('<div class="column-inner ui-sortable"></div>');
	$( ".column-inner" ).sortable({
		connectWith: ".column-inner",
		stop: function( event, ui ) {
			updateColumns();
		}
	});


	// Prevent the post from being published or updated if no primary category is selected
	$(document).on('.button', '#publishing-action', function() {
		//if( $('#ione_primary_category_select_id' ).val() < 0 ) {
			alert( 'You must first select a Primary Category.' );
			return false;
		//}
		//else{
//			// Ensure the category checkbox corresponding to the selected primary category is checked
//			var checkbox_id = 'in-category-' + $('#ione_primary_category_select_id' ).val();
//			$('#'+checkbox_id).attr('checked', true);
		//}
	} );
	return false;

});