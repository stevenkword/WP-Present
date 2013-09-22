

	// Full list of configuration options available here:
	// https://github.com/hakimel/reveal.js#configuration
	Reveal.initialize({
		width: 1020,
		height: 540,
		controls: true,
		progress: false,
		history: true,
		center: true,
		autoSlide: 0, // in milliseconds, 0 to disable
		loop: false,
		mouseWheel: false,
		rollingLinks: false,
		transition: 'default', // default/cube/page/concave/zoom/linear/fade/none

		theme: Reveal.getQueryHash().theme, // available themes are in /css/theme
		transition: Reveal.getQueryHash().transition || 'default', // default/cube/page/concave/zoom/linear/fade/none

		// Optional libraries used to extend on reveal.js
		dependencies: [
			//{ src: '<?php echo get_stylesheet_directory_uri();?>/js/reveal.js/lib/js/classList.js', condition: function() { return !document.body.classList; } },
			//{ src: '<?php echo get_stylesheet_directory_uri();?>/js/reveal.js/plugin/markdown/marked.js', condition: function() { return !!document.querySelector( '[data-markdown]' ); } },
			//{ src: '<?php echo get_stylesheet_directory_uri();?>/js/reveal.js/plugin/markdown/markdown.js', condition: function() { return !!document.querySelector( '[data-markdown]' ); } },
			//{ src: '<?php echo get_stylesheet_directory_uri();?>/js/reveal.js/plugin/highlight/highlight.js', async: true, callback: function() { hljs.initHighlightingOnLoad(); } },
			//{ src: '<?php echo get_stylesheet_directory_uri();?>/js/reveal.js/plugin/zoom-js/zoom.js', async: true, condition: function() { return !!document.body.classList; } },
			//{ src: '<?php echo get_stylesheet_directory_uri();?>/js/reveal.js/plugin/notes/notes.js', async: true, condition: function() { return !!document.body.classList; } }

			// { src: '<?php echo get_stylesheet_directory_uri();?>/js/reveal.js/plugin/search/search.js', async: true, condition: function() { return !!document.body.classList; } }
			// { src: '<?php echo get_stylesheet_directory_uri();?>/js/reveal.js/plugin/remotes/remotes.js', async: true, condition: function() { return !!document.body.classList; } }
		]
	});

	Reveal.addEventListener( 'slidechanged', function( event ) {
		// event.previousSlide, event.currentSlide, event.indexh, event.indexv
		console.log("x=" + event.indexh + " y=" + event.indexv);

		//$('input[id][name$="man"]')
		//jQuery('a[data-indexh$='+event.indexh+']').css('color','red');

		$(".home .main-navigation a").parent().removeClass("current-menu-item");
		$('a[data-indexh$='+event.indexh+']').parent().addClass("current-menu-item");

	});

