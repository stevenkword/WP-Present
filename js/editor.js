/**
 * Theme Customizer enhancements for a better user experience.
 *
 * Contains handlers to make Theme Customizer preview reload changes asynchronously.
 */

( function( $ ) {
					console.log('loaded');

					// Full list of configuration options available here:
					// https://github.com/hakimel/reveal.js#configuration
					Reveal.initialize({
						width: 1020,
						height: 720,
						controls: true,
						progress: false,
						history: true,
						center: false,
						autoSlide: 0, // in milliseconds, 0 to disable
						loop: false,
						mouseWheel: false,
						rollingLinks: false,
						transition: 'default', // default/cube/page/concave/zoom/linear/fade/none

						theme: Reveal.getQueryHash().theme, // available themes are in /css/theme
						transition: Reveal.getQueryHash().transition || 'concave', // default/cube/page/concave/zoom/linear/fade/none


					});

					Reveal.addEventListener( 'slidechanged', function( event ) {
						// event.previousSlide, event.currentSlide, event.indexh, event.indexv
						console.log("x=" + event.indexh + " y=" + event.indexv);

						//$('input[id][name$="man"]')
						//jQuery('a[data-indexh$='+event.indexh+']').css('color','red');

						console.log(jQuery(".home .main-navigation a").parent());

						jQuery(".home .main-navigation .mneu-item a").parent().removeClass("current-menu-item");
						//$('a[data-indexh$='+event.indexh+']').parent().addClass("current-menu-item");
					});
} )( jQuery );