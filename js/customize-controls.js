/**
 * Theme Customizer enhancements for a better user experience.
 *
 * Contains handlers to make Theme Customizer preview reload changes asynchronously.
 */
( function( $ ) {

	// Background Color
	wp.customize( 'wp_present_background_color', function( value ) {
		value.bind( function( newval ) {
			$( '#editor_slide_ifr' ).contents().find('.reveal').css('background-color', newval );
		} );
	} );

	// Text Color
	wp.customize( 'wp_present_text_color', function( value ) {
		value.bind( function( newval ) {
			$( '#editor_slide_ifr' ).contents().find('.reveal, .reveal h1, .reveal h2, .reveal h3').css('color', newval );
		} );
	} );

	// Link Color
	wp.customize( 'wp_present_link_color', function( value ) {
		value.bind( function( newval ) {
			$( '#editor_slide_ifr' ).contents().find('.reveal a, .reveal h1 a, .reveal h2 a, .reveal h3 a').css('color', newval );
		} );
	} );

	// Update the background header image
	wp.customize( 'wp_present_background_image', function( value ) {
	value.bind( function( newval ) {
			$( '#editor_slide_ifr' ).contents().find('.reveal').css("background-image", "url('"+newval+"')");
		} );
	} );

} )( jQuery );