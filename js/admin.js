/**
 * WP Present - admin.js
 *
 */

// Hide the description fields as fast as possible
jQuery('label[for="description"]').parent().parent().hide();
jQuery('#tag-description').closest('.form-field').hide();
jQuery('#description').closest('.form-field').hide();
jQuery('.column-description').hide();

jQuery(document).ready(function($){

	//$('.column-inner' ).css('border', '1px solid red' );

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

	$('#add-button').click(function(e) {
		e.preventDefault();

		$('#slide-modal').dialog({
			autoOpen: true,
			modal: true,
			title: "Email Dialog",
			open: addTinyMCE,
			close: function() {
				removeTinyMCE();
				$(this).dialog('destroy');
			},
			buttons:  {
				'Send': function() {},
				'Cancel': function() {
					removeTinyMCE();
					$(this).dialog('destroy');
				}
			}
		});

		return;
		var text = 'test';
		var $li = $("<div class='ui-state-default'/>").text(text);
		var test = '<div id="slide-1689" class=" portlet widget"><div class="widget-top"><div class="widget-title-action"><a class="widget-action hide-if-no-js" href="#available-widgets"></a><a class="widget-control-edit hide-if-js" href=""><span class="edit">Edit</span><span class="add">Add</span><span class="screen-reader-text">Why PHP Developers Should Leverage WordPress</span></a></div><div class="widget-title"><h4>Why PHP Developers Should Leverage WordPress<span class="in-widget-title"></span></h4></div></div><div class="widget-description">Render an ad from an ad shortcode.</div></div>';

	// Pay attention here
		$("#col-3").append(test);
		$(".column-inner").sortable('refresh');
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
});