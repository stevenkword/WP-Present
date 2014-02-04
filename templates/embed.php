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
		<?php
			// @todo Unhook theme customizer styles
		?>
		<?php wp_head(); ?>
	</head>
	<body <?php body_class(); ?>>
		<?php if ( have_posts() ) : ?>
			<div class="reveal">
				<div id="branding" style="position: fixed; width: 100%;height: 75px;z-index: 30;left: 25px;bottom: 25px;">
					<!--
					<img style="float:left" height="75px" width="75px" src="https://fbcdn-profile-a.akamaihd.net/hprofile-ak-ash2/c34.34.432.432/s160x160/1235230_634351096617288_313239610_n.png" />
					<span style="font-weight: normal; height: 70px; display: table-cell; vertical-align: middle;padding-left: 20px;">@stevenkword</span>
					-->
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
									$background_image = wp_get_attachment_url( get_post_thumbnail_id( $post->ID ) );
									$post_thumbnail_url = wp_get_attachment_url( get_post_thumbnail_id( $post->ID ) );
									$background_color = get_post_meta( $post->ID, 'background-color', true );
									$text_color = get_post_meta( $post->ID, 'text-color', true );
									$link_color = get_post_meta( $post->ID, 'link-color', true );

									// Give priority to the thumbnail, but fallback to the background color
									$data_background = ( isset( $post_thumbnail_url ) && ! empty( $post_thumbnail_url ) ) ? $post_thumbnail_url : '';
									if( ! $data_background )
										$data_background = ( isset( $background_color ) && ! empty( $background_color ) ) ? $background_color : '';

									$style = "color: $text_color; ";
									?>
									<section id="<?php echo esc_attr( $post->post_name); ?>" data-transition="linear"  <?php if( isset( $data_background )  ) echo 'data-background="' . $data_background . '"';?>>
										<div style="<?php echo $style; ?>">
											<?php the_content(); ?>
											<p>
												<small><?php wp_link_pages( array( 'before' => '<div class="page-links">' . __( 'Pages:', '_s' ), 'after' => '</div>' ) ); ?></small>
											</p>
										</div>
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

			<?php wp_footer(); ?>

		<?php endif; ?>

	</body>
</html>