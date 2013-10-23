<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title><?php wp_title( '|', true, 'right' ); ?></title>
		<link rel="profile" href="http://gmpg.org/xfn/11" />
		<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
		<!--[if lt IE 9]>
		<script src="<?php echo get_template_directory_uri(); ?>/js/html5.js" type="text/javascript"></script>
		<![endif]-->
		<?php wp_head(); ?>
	</head>
	<body <?php body_class(); ?>>
		<?php if ( have_posts() ) : ?>
			<div class="reveal">
				<div id="branding" style="position: fixed; left: 0; bottom: 0; padding: 1em;">
					<img height="75px" width="75px" src="http://localhost/sandbox/wp-content/uploads/2013/10/Oomph_Box_RGB_solid_Red_152x152.png" />
					<!--<p>@stevenkword</p>-->
				</div>
				<section id="primary" class="content-area">
				<!-- Any section element inside of this container is displayed as a slide -->
				<div class="slides">
				<?php
					$obj = get_queried_object();
					if( ! isset( $obj->term_taxonomy_id ) )
						return '';

					$term_description =  ! empty( $obj->description ) ? json_decode( $obj->description, $asArray = true ) : '';

					// Calculate the number of columns we need
					$columns = array();
					if( is_array( $term_description ) && ! empty( $term_description ) ) {
						foreach( $term_description as $c => $column ) {
							if( ! empty( $term_description[ $c ] ) )
								$columns[] = $c;
						}
					}

					//Let's take a look at the column array;
					global $post, $wp_query;
					for ($j = 1; $j <= count( $columns ); $j++) { ?>
						<section>
						<?php
							if( isset( $term_description[ 'col-' . $j ] ) )
								$slides = $term_description[ 'col-' . $j ];
							if( is_array( $slides ) ) {
								$i = 1;
								foreach( $slides as $key => $slide ) {
									list( $rubbish, $slide_id ) =  explode( '-', $slide );
									$post = get_post( $slide_id );
									setup_postdata( $post );
									?>
									<section id="<?php echo esc_attr( $post->post_name); ?>" data-transition="linear">
										<?php the_content(); ?>
										<p>
											<small><?php wp_link_pages( array( 'before' => '<div class="page-links">' . __( 'Pages:', '_s' ), 'after' => '</div>' ) ); ?></small>
										</p>
									</section>
									<?php
									$i++;
								}
								unset( $i );
							}
							wp_reset_postdata();
						?>
						</section>
					<?php
					}
					unset( $j );
				?>
				</div><!--/.slides-->
			</div><!-- .reveal -->

			<!-- Enqueue these things, for reals -->
			<?php
			// Get plugin path
			$plugin_path = dirname( __FILE__ );
			$plugin_url = plugins_url( '/wp-present/' );
			//echo $plugin_url . '/js/reveal.js/lib/js/head.min.js';
			?>

			<?php wp_footer(); ?>

			<script type="text/javascript">
				// Full list of configuration options available here:
				// https://github.com/hakimel/reveal.js#configuration
				Reveal.initialize({
					// The "normal" size of the presentation, aspect ratio will be preserved
					// when the presentation is scaled to fit different resolutions. Can be
					// specified using percentage units.
					width: 1024,
					height: 768,
					margin: 0.1, // Factor of the display size that should remain empty around the content
					minScale: 0.2, // Bounds for smallest/largest possible scale to apply to content
					maxScale: 1.0, // Bounds for smallest/largest possible scale to apply to content
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

					// Optional libraries used to extend on reveal.js
					dependencies: [
						{ src: '<?php echo $plugin_url;?>/js/reveal.js/lib/js/classList.js', condition: function() { return !document.body.classList; } },
						{ src: '<?php echo $plugin_url;?>/js/reveal.js/plugin/markdown/marked.js', condition: function() { return !!document.querySelector( '[data-markdown]' ); } },
						{ src: '<?php echo $plugin_url;?>/js/reveal.js/plugin/markdown/markdown.js', condition: function() { return !!document.querySelector( '[data-markdown]' ); } },
						{ src: '<?php echo $plugin_url;?>/js/reveal.js/plugin/highlight/highlight.js', async: true, callback: function() { hljs.initHighlightingOnLoad(); } },
						{ src: '<?php echo $plugin_url;?>/js/reveal.js/plugin/zoom-js/zoom.js', async: true, condition: function() { return !!document.body.classList; } },
						{ src: '<?php echo $plugin_url;?>/js/reveal.js/plugin/notes/notes.js', async: true, condition: function() { return !!document.body.classList; } }
						// { src: '<?php echo $plugin_url;?>/js/reveal.js/plugin/search/search.js', async: true, condition: function() { return !!document.body.classList; } }
						// { src: '<?php echo $plugin_url;?>/js/reveal.js/plugin/remotes/remotes.js', async: true, condition: function() { return !!document.body.classList; } }
					]
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
			</script>
		<?php endif; ?>
	</body>
</html>