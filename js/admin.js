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
//		tinymce.execCommand('mceRemoveControl',true,'editor_slide');
//		tinymce.execCommand('mceAddControl',true,'editor_slide');
	}

	$('#add-button').on('click', function(e) {
		e.preventDefault;
		$('#editor_slide-tmce').click(); //Necessary on subsequent loads of the editor
		$( "#dialog" ).dialog({
		  autoOpen: true,
		  width: 600,
		  height: 450,
		  modal: false,
		  buttons: {
			"Edit Slide": function() {
				var editorContents = tinymce.get('editor_slide').getContent();
				$('#description').val( editorContents );
				closeModal();
			},
			Cancel: function() {
				closeModal();
			}
		  },
		  close: function() {
				closeModal();
		  }
		});
	});

	function closeModal() {
		tinymce.execCommand('mceRemoveControl',true,'editor_slide');
		$( '#dialog' ).dialog( "close" );
	}


	$('.widget-title-action').on('click', function(e) {
		$( this ).parents('.widget').children('.widget-inside').toggle();
	});


});

jQuery(document).ready(function($){

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

	function updateColumns(){
		var columns = { }; // Creates a new object
		var i = 1;
		$( '.column-inner' ).each(function( index ) {

			var $order = $(this).sortable( "toArray" );
			columns['col-'+i] = $order;
			i++;

			//console.log( index );
			//console.log( $order );
		});
		//console.log(columns);
		var encoded = JSON.stringify( columns );

		$('#description').val(encoded);
	}

	function consolidateColumns(){

		$( '.column-inner' ).each(function ( index ) {

		var i = index + 1;
		var html = $(this).html().trim();

		console.log( 'index: ' + i );
		//console.log( 'html: "' + html + '"' );

		if( typeof(html) === 'string' && '' != html ) {
			console.log( html.substr(0,100) );
		} else {

			var meh = '#col-' + ( i + 1 );

			console.log( 'i: ' + meh );

			var $nextCol = $(meh);
			//console.log( 'test' + $nextCol.html() );
			$(this).html( $nextCol.html() ) ;
			$nextCol.html('');
		}
	});

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