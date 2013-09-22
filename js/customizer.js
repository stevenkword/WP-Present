/**
 * Theme Customizer enhancements for a better user experience.
 *
 * Contains handlers to make Theme Customizer preview reload changes asynchronously.
 */

( function( $ ) {
	// Site title and description.
	wp.customize( 'blogname', function( value ) {
		value.bind( function( to ) {
			$( '.site-title a' ).html( to );
		} );
	} );
	wp.customize( 'blogdescription', function( value ) {
		value.bind( function( to ) {
			$( '.site-description' ).html( to );
		} );
	} );

	// Update the background header image
	wp.customize( 'custom_header_background_image', function( value ) {
		value.bind( function( newval ) {
			$( '.custom-content-background-image' ).css("background", "transparent url('"+newval+"')  fixed no-repeat center top");
		} );
	} );

	//Update site background color...
	wp.customize( 'body_background_color', function( value ) {
		value.bind( function( newval ) {
			$('#background').css('background-color', newval );
		} );
	} );

	//Content Background
	wp.customize( 'content_background_color', function( value ) {
		value.bind( function( newval ) {
			$('#main').css('background-color', newval );
		} );
	} );

	//Full Logo
	wp.customize( 'logo_full', function( value ) {
		value.bind( function( newval ) {
			$('.site-title a img#logo').attr('src', newval );
		} );
	} );

	//Full Logo
	wp.customize( 'logo_retracted', function( value ) {
		value.bind( function( newval ) {
			$('.site-title a img#logo').attr('src', newval );
		} );
	} );

	//Masthead Foreground
	wp.customize( 'custom_header_color', function( value ) {
		value.bind( function( newval ) {
			$('.custom-header-color,.icon').css('color', newval );
		} );
	} );

	//Masthead Background
	wp.customize( 'custom_header_background_color', function( value ) {
		value.bind( function( newval ) {
			$('.custom-header-background-color').css('background-color', newval );
		} );
	} );
	// Text Accent
	wp.customize( 'header_footer_accent_color', function( value ) {
		value.bind( function( newval ) {
			$('a:hover, .icon','color', 'header_footer_accent_color').css('color', newval );
		} );
	} );


	// Body Background
	wp.customize( 'background_color', function( value ) {
		value.bind( function( newval ) {
			$('.custom-body-background-color').css('background-color', newval );
		} );
	} );
	// Body Color
	wp.customize( 'custom_body_color', function( value ) {
		value.bind( function( newval ) {
			$('.custom-body-color').css('color', newval );
		} );
	} );
} )( jQuery );