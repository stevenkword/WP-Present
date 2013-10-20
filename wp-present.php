<?php
/*
Plugin Name: WP Present
Plugin URI: http://stevenword.com/plugins/wp-present/
Description: Easily create slideshow presentations with the the power of WordPress and the elegance of reveal.js
Author: stevenkword
Version: 0.999
Author URI: http://stevenword.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Copyright 2013 Steven K. Word

GNU General Public License, Free Software Foundation <http://creativecommons.org/licenses/GPL/2.0/>

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

class WP_Present {

	/* Post Type */
	public $post_type_slug = 'slide';
	public $post_type_name = 'Slides';
	public $post_type_singular_name = 'Slide';
	public $post_type_capability_type = 'post';
	public $post_types = array( 'slide' );

	/* Taxonomy */
	public $taxonomy_slug = 'presentation';
	public $taxonomy_name = 'Presentations';
	public $taxonomy_singular_name = 'Presentation';

	/* Options */
	public $option_name = 'presentation-options';
	public $option_title = 'Presentation Options';

	/* Misc */
	public $capability = 'edit_others_posts';
	public $nonce_field = 'wp-present-nonce';
	public $nonce_fail_message = "fail!";
	public $scripts_version = 200131007;
	public $default_theme = 'simple.css';
	//public $max_num_slides = 250; //not currently used, proposed variable

	public $plugins_url = '';

	/* Define and register singleton */
	private static $instance = false;
	public static function instance() {
	if( ! self::$instance )
		self::$instance = new WP_Present;
		return self::$instance;
	}

	/**
	 * Gene manipulation algorithms go here
	 */
	private function __clone() { }

	/**
	 * Register actions and filters
	 *
	 * @uses add_action()
	 * @return null
	 */
	public function __construct() {

		// Setup
		$this->plugins_url = plugins_url( '/wp-present' );

		// On Activation
		register_activation_hook( __FILE__, array( $this, 'activate' ) );

		// On Dactivations
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		// Initialize
		add_action( 'init', array( $this, 'action_init_register_post_type' ) );
		add_action( 'init', array( $this, 'action_init_register_taxonomy' ) );
		add_action( 'init', array( $this, 'action_init_editor_styles' ) );

		// Front End
		add_action( 'wp_head', array( $this, 'action_wp_head' ), 99 );
		add_action( 'wp_enqueue_scripts', array( $this, 'action_wp_enqueue_scripts' ), 99 );
		add_action( 'wp_footer', array( $this, 'action_wp_footer' ), 99 );

		// Template
		add_filter( 'template_include', array( $this, 'filter_template_include' ) );

		// Admin
		add_action( 'admin_menu', array( $this, 'action_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'action_admin_enqueue_scripts' ) );
		add_action( 'admin_head', array( $this, 'action_admin_head' ), 20 );
		add_action( 'save_post', array( $this, 'action_save_post' ) );
		add_action( 'admin_footer', array( $this, 'action_admin_footer' ), 20 );

		//Hide screen options
		add_filter('screen_options_show_screen', '__return_false'); // a test

		// Taxonomy
		add_action( $this->taxonomy_slug . '_edit_form', array( $this, 'taxonomy_edit_form' ), 9, 2 );

		add_action( 'restrict_manage_posts', array( $this, 'action_restrict_manage_posts' ) );
		add_action( 'parse_query', array( $this, 'action_parse_query' ) );

		//Update the post links for slides
		add_filter( 'post_type_link', array( $this, 'append_query_string' ), 10, 2 );

		// AJAX
		add_action( 'wp_ajax_get_slide', array( $this, 'action_wp_ajax_get_slide' ) );
		add_action( 'wp_ajax_update_slide', array( $this, 'action_wp_ajax_update_slide' ) );
		add_action( 'wp_ajax_new_slide', array( $this, 'action_wp_ajax_new_slide' ) );
		add_action( 'wp_ajax_delete_slide', array( $this, 'action_wp_ajax_delete_slide' ) );
		add_action( 'wp_ajax_update_presentation', array( $this, 'action_wp_ajax_update_presentation' ) );

		// TinyMCE
		add_filter( 'tiny_mce_before_init', array( $this, 'filter_tiny_mce_before_init' ) );

		// Hide taxonomy description column
		add_filter( 'manage_edit-' . $this->taxonomy_slug . '_columns', array( $this, 'filter_manage_edit_columns' ) );
	}

	/**
	 * Remove the description column from the taxonomy overview page
	 *
	 * @return array
	 */
	function filter_manage_edit_columns( $theme_columns ) {
		unset( $theme_columns['description'] );
		return $theme_columns;
	}

	/**
	 * Reality check
	 *
	 * @uses maths
	 * @return bool (hopefully)
	 */
	function is() {
		return ( 2 + 2 ) != 4 ? false : true;
	}

	/**
	 * On plugin activation
	 *
	 * @uses flush_rewrite_rules()
	 * @return null
	 */
	function activate() {
		$this->action_init_register_post_type();
		$this->action_init_register_taxonomy();
		flush_rewrite_rules();
	}

	/**
	 * On plugin deactivation
	 *
	 * @uses flush_rewrite_rules()
	 * @return null
	 */
	function deactivate() {
		flush_rewrite_rules();
	}

	/**
	 * Register the post type
	 *
	 * @uses add_action()
	 * @return null
	 */
	function action_init_register_post_type() {
		register_post_type( $this->post_type_slug, array(
			'labels' => array(
				//@todo http://codex.wordpress.org/Function_Reference/register_post_type
				'name' => __( $this->post_type_name ),
				'singular_name' => __( $this->post_type_singular_name ),
				'add_new_item' => __( 'Add New ' . $this->post_type_singular_name ),
				'edit_item' => __( 'Edit ' . $this->post_type_singular_name ),
				'new_item' => __( 'New ' . $this->post_type_singular_name ),
				'view_item' => __( 'View ' . $this->post_type_singular_name ),
				'search_items' => __( 'Search' . $this->post_type_name ),
				/*'menu_name' => __( 'asdf' )*/
			),
			'public' => true,
			'capability_type' => $this->post_type_capability_type,
			'has_archive' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			//'menu_position' => 5,
			'hierarchical' => true, //@todo within the same category?
			'supports' => array( 'title', 'editor', 'page-attributes' ),
			'taxonomies' => array( $this->taxonomy_slug )
		) );
	}

	/**
	 * Register the taxonomy
	 *
	 * @uses add_action()
	 * @return null
	 */
	function action_init_register_taxonomy() {
		register_taxonomy( $this->taxonomy_slug, $this->post_types, array(
			'labels'              	=> array(
				'name'            		=> _x( $this->taxonomy_name, 'taxonomy general name' ),
				'singular_name'       	=> _x( $this->taxonomy_singular_name, 'taxonomy singular name' ),
				'search_items'        	=> __( 'Search ' . $this->taxonomy_name ),
				'all_items'           	=> __( 'All ' . $this->taxonomy_name ),
				'parent_item'         	=> __( 'Parent ' . $this->taxonomy_singular_name ),
				'parent_item_colon'   	=> __( 'Parent ' . $this->taxonomy_singular_name . ':' ),
				'edit_item'           	=> __( 'Edit ' . $this->taxonomy_singular_name ),
				'update_item'         	=> __( 'Update ' . $this->taxonomy_singular_name ),
				'add_new_item'        	=> __( 'Add New ' . $this->taxonomy_singular_name ),
				'new_item_name'       	=> __( 'New ' . $this->taxonomy_singular_name. ' Name' ),
				'menu_name'           	=> __( $this->taxonomy_name ),
				'view_item'           	=> __( 'View ' . $this->taxonomy_singular_name )
			),
			'hierarchical'        	=> true,
			'show_ui'             	=> true,
			'show_admin_column'   	=> true,
			'query_var'         	=> true,
			'rewrite'             	=> array( 'slug' => $this->taxonomy_slug )
		) );
	}


	/**
	 * Register editor styles
	 *
	 * @uses add_action()
	 * @return null
	 */
	function action_init_editor_styles() { // also should peep at mce_css
		global $pagenow, $post;

		// Only on the edit taxonomy and edit post type admin pages
		$is_tax = ( 'edit-tags.php' == $pagenow || ( isset( $_GET['taxonomy'] ) && $this->taxonomy_slug == $_GET['taxonomy'] ) ) ? true : false;
		$is_cpt = ( 'post.php' == $pagenow && isset( $_GET[ 'post' ] ) && $this->post_type_slug == get_post_type( $_GET[ 'post' ] ) ) ? true : false;

		if( ! $is_tax && ! $is_cpt )
			return;

		//If not page now tax or slide : return;
		remove_editor_styles();
		add_editor_style( plugins_url( '/wp-present/css/reset.css' ) );
		add_editor_style( plugins_url( '/wp-present/js/reveal.js/css/theme/' . $this->default_theme ) );
		add_editor_style( plugins_url( '/wp-present/js/reveal.js/lib/css/zenburn.css' ) );
		add_editor_style( plugins_url( '/wp-present/css/custom.css?v=' . $this->scripts_version ) );
	}

	/**
	 * Enqueue necessary scripts
	 *
	 * @uses wp_enqueue_script
	 * @return null
	 */
	function action_wp_enqueue_scripts() {
		if( ! is_tax( $this->taxonomy_slug ) )
			return;

		/* Browser reset styles */
		wp_enqueue_style( 'reset', $this->plugins_url . '/css/reset.css', '', $this->scripts_version );

		/* Reveal Styles */
		wp_enqueue_style( 'reveal', $this->plugins_url . '/js/reveal.js/css/reveal.css', '', $this->scripts_version );
		wp_enqueue_style( 'reveal-theme', $this->plugins_url . '/js/reveal.js/css/theme/' . $this->default_theme, array('reveal'), $this->scripts_version );
		wp_enqueue_style( 'zenburn', $this->plugins_url . '/js/reveal.js/lib/css/zenburn.css', '', $this->scripts_version, false );

		/* Last run styles */
		//wp_enqueue_style( 'home', $this->plugins_url . '/css/home.css', array('reveal-theme'), $ver_reveal );
		wp_enqueue_style( 'custom', $this->plugins_url . '/css/custom.css', array('reveal'), $this->scripts_version );

		/* Reveal Scripts */
		wp_enqueue_script( 'reveal-head', $this->plugins_url . '/js/reveal.js/lib/js/head.min.js', array( 'jquery' ), $this->scripts_version, true );
		wp_enqueue_script( 'reveal', $this->plugins_url . '/js/reveal.js/js/reveal.min.js', array( 'jquery' ), $this->scripts_version, true );
		//wp_enqueue_script( 'reveal-config', $this->plugins_url . '/js/reveal-config.js', array( 'jquery' ), $this->scripts_version );
	}

	/**
	 * Select appropriate template based on post type and available templates.
	 * Returns an array with name and path keys for available template or false if no template is found.
	 * Based on a similar method from wp-print-friendly
	 *
	 * @uses get_queried_object, is_home, is_front_page, locate_template
	 * @return array or false
	 */
	public function template_chooser() {
		// Get queried object to check post type
		$queried_object = get_queried_object();

		//Get plugin path
		$plugin_path = dirname( __FILE__ );

		if ( file_exists( $plugin_path . '/default-template.php' ) && $this->is() )
			$template = array(
				'name' => 'wp-presents-default',
				'path' => $plugin_path . '/default-template.php'
			);

		return isset( $template ) ? $template : false;
	}

	/**
	 * Filter template include to return print template if requested.
	 * Based on a similar method from wp-print-friendly
	 *
	 * @param string $template
	 * @filter template_include
	 * @uses this::is_protected
	 * @return string
	 */
	public function filter_template_include( $template ) {
		if ( is_tax( $this->taxonomy_slug ) && ( $taxonomy_template = $this->template_chooser() ) )
			$template = $taxonomy_template[ 'path' ];

		return $template;
	}

	/**
	 * Output for the <head>
	 *
	 * @uses is_tax
	 * @return null
	 */
	function action_wp_head() {
		if( ! is_tax( $this->taxonomy_slug ) )
			return false;
		?>
		<!-- Reveal -->
		<meta name="apple-mobile-web-app-capable" content="yes" />
		<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

		<script type="text/javascript">
			jQuery(function($){
				$('#wpadminbar').show();
				$('#toggle-wpadminbar').click( function() {
					$('#wpadminbar').toggle();
				} );

			});
		</script>
		<?php
	}

	/**
	 * Output for the <footer>
	 *
	 * @uses is_tax
	 * @return null
	 */
	function action_wp_footer() {
		//if( ! is_tax( $this->taxonomy_slug ) )
		//	return false;
		?>
		<script>
		/* Custom jQuery Reveal Code */
		jQuery(document).ready(function($) {

			// Full list of configuration options available here:
			// https://github.com/hakimel/reveal.js#configuration
			Reveal.initialize({
				width: 960,
				height: 720,
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
			});
		});
		</script>
		<?php
	}

	/**
	 * Add the necessary menu pages
	 *
	 * @return null
	 */
	function action_admin_menu(){
		global $menu, $submenu;

		// Taxonomy Menu
		$taxonomy_url = 'edit-tags.php?taxonomy=' . $this->taxonomy_slug . '&post_type='.$this->post_type_slug;
		$post_type_url = 'edit.php?post_type=' . $this->post_type_slug;


		// Add the options page
		add_submenu_page( $post_type_url, $this->option_title, 'Options', $this->capability, $this->option_name, array( $this, 'options_page' ) );

		// Rename the menu item
		foreach( $menu as $menu_key => $menu_item ) {
			if( $this->post_type_name == $menu_item[0] ) {
				$menu[ $menu_key ][0] = $this->taxonomy_name;
			}
		}

		//It owuld be better to search for the keys based on url

		// Move the taxonomy menu to the top
		foreach( $submenu as $submenu_key => $submenu_item ) {
			if( $this->taxonomy_name == $submenu_item[15][0] ) {
				//$submenu[$submenu_key][15][0] = 'toast';

				// This is a bit of hackery.  I should search for these keys
				$submenu[$submenu_key][2] = $submenu[$submenu_key][15];
				unset( $submenu[$submenu_key][15] );

				//Not a fan of the add new bit
				unset( $submenu[$submenu_key][10] );

				ksort( $submenu[$post_type_url] );
			}
		}

	}

	/**
	 * Markup for the Options page
	 *
	 * @return null
	 */
	function options_page(){
		?>
		<div id="wpbody">
			<div id="wpbody-content" aria-label="Main content" tabindex="0">
				<div class="wrap">
					<h2><?php echo $this->option_title; ?></h2>
					<h3>Select a Theme</h3>
						<p>Current Theme: <?php echo $this->default_theme; ?></p>
					<h3>Branding</h3>
						<p>Branding HTML textarea goes here</p>
					<h3>Coming soon</h3>
					<ul>
						<li>Add metaboxes for reveal.js speaker notes (asides)</li>
						<li>Add metaboxes for appearance/motion overrides</li>
						<li>Append a bodyclass to the <body> tag for more specific style targetting</li>
						<li>Ability to suppress titles</li>
						<li>Peek at how MP6 does fonts / icons (OPEN SANS!)</li>
						<li>REVEAL theme selector option for the theme customizer</li>
						<li>Make the placeholders for the columns larger than the widget placeholders</li>
						<li>Fix column backfilling when empty columns are presentwq</li>
						<li>Clean up the admin menu hackery</li>
					</ul>
				</div>
				<div class="clear"></div>
			</div><!-- wpbody-content -->
			<div class="clear"></div>
		</div>
		<?php
	}

	/**
	 * Enqueue necessary admin scripts
	 *
	 * @uses wp_enqueue_script
	 * @return null
	 */
	function action_admin_enqueue_scripts() {
		global $pagenow;
		// Only on the edit taxonomy page
		if( 'edit-tags.php' != $pagenow || !isset( $_GET['taxonomy'] ) || $this->taxonomy_slug != $_GET['taxonomy'] )
			return;

		// Admin Styles
		wp_enqueue_style( 'jquery-ui-fresh', $this->plugins_url . '/css/wp-admin-jquery-ui/jquery-ui-fresh.css', '', $this->scripts_version );	// Thanks Helen
		wp_enqueue_style( 'wp-present-admin', $this->plugins_url . '/css/admin.css', '', $this->scripts_version );

		// Admin Scripts
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'jquery-ui-resizable' );
		wp_enqueue_script( 'jquery-ui-dialog' );

		wp_enqueue_script( 'wp-present-admin', $this->plugins_url . '/js/admin.js', array( 'jquery' ), $this->scripts_version, true );
		wp_localize_script( 'wp-present-admin', 'presentation', $_REQUEST['tag_ID'] );
	}

	/**
	 * Output for the admin <head>
	 *
	 * @return null
	 */
	function action_admin_head() {
		// Only add this variable on the edit taxonomy page
		global $pagenow;
		if( 'edit-tags.php' != $pagenow || ! isset( $_GET['taxonomy'] ) || $this->taxonomy_slug != $_GET['taxonomy'] )
			return;

		$num_slides = ( isset( $_GET[ 'tag_ID' ] ) ) ? count( $this->get_associated_slide_ids( $_GET[ 'tag_ID' ], $_GET[ 'taxonomy' ] ) ) : '';
		wp_localize_script( 'wp-present-admin', 'WPPNumSlides', array( intval( $num_slides ) ) );
		wp_localize_script( 'wp-present-admin', 'WPPTaxonomyURL', array( get_term_link( (int) $_GET[ 'tag_ID' ], $this->taxonomy_slug ) ) );

		// Make the admin outer-container div big enough to prevent wrapping
		$container_size = ( $num_slides + 1 ) * 210;
		?>
		<style type="text/css">
			#container{ width: <?php echo $container_size; ?>px;}
		</style>
		<?php
	}

	/*
	 * Save chosen primary presentaiton as post meta
	 * @param int $post_id
	 * @uses wp_verify_nonce, current_user_can, update_post_meta, delete_post_meta, wp_die
	 * @action save_post
	 * @return null
	 */
	public function action_save_post( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return;

		// Broken
		//if ( ! isset( $_POST[ $this->nonce_field ] ) || ! wp_verify_nonce( $_POST[ $this->nonce_field ], $this->nonce_field ) )
			//return;

		if ( 'page' == get_post_type( $post_id ) && ! current_user_can( 'edit_page', $post_id ) )
				return;
		elseif ( ! current_user_can( 'edit_post', $post_id ) )
				return;

		//wp_die( 'You must choose a presentation', 'ERROR', array( 'back_link' => true ) );
	}

	/**
	 * Output for the admin <footer>
	 *
	 * @return null
	 */
	function action_admin_footer() {
		// Only run on the edit taxonomy page
		global $pagenow;
		if( 'edit-tags.php' != $pagenow || ! isset( $_GET['taxonomy'] ) || $this->taxonomy_slug != $_GET['taxonomy'] )
			return;
	}

	/* Find which slides are already found in the DB before auto-populating the backfill
	 *
	 * @return array
	 */
	function get_associated_slide_ids( $term, $taxonomy ) {
		$term_description =  $this->get_term_description( $term, $taxonomy );
		if( ! is_array( $term_description ) )
			return false;

		$associated_slides =  array();

		// Calculate the number of columns we need
		$columns = array();
		foreach( $term_description as $c => $column ) {
			if( ! empty( $term_description[ $c ] ) )
				$num_columns[] = $c;
		}

		global $post, $wp_query;
		for ( $col = 1; $col <= count( $num_columns ); $col++ ) {
			$slides = $term_description[ 'col-' . $col ];
			foreach( $slides as $key => $slide ) {
				list( $rubbish, $slide_id ) =  explode( '-', $slide );
				$post = get_post( $slide_id );
				setup_postdata( $post );
				$associated_slides[] = get_the_ID();
				wp_reset_postdata();
			}
		}
		unset( $col );
		return $associated_slides;
	}

	/* Get the term description
	 *
	 * @return array
	 */
	function get_term_description( $term, $taxonomy ) {
		$obj = get_term( $term, $taxonomy );
		if( ! isset( $obj->term_taxonomy_id ) )
			return '';
		return $term_description =  ! empty( $obj->description ) ? json_decode( $obj->description, $asArray = true ) : '';
	}


	/* Render a slide for reveal.js
	 *
	 * @return null
	 */
	function admin_render_slide( $post ) {
		setup_postdata( $post );
		?>
		<div id="slide-<?php the_ID(); ?>" class=" portlet widget">
			<div class="widget-top">
				<div class="widget-title-action">
					<a class="widget-action hide-if-no-js" href="#available-widgets"></a>
					<a class="widget-control-edit hide-if-js" href="">
						<span class="edit">Edit</span>
						<span class="add">Add</span>
						<span class="screen-reader-text"><?php the_title(); ?></span>
					</a>
				</div>
				<div class="widget-title">
					<h4><?php the_title(); ?><span class="in-widget-title"></span></h4>
				</div>
			</div>
			<div class="widget-inside" style="display: none;">
				<input class="slide-id" id="input-<?php the_ID(); ?>" type="hidden" value="<?php the_ID(); ?>"></input>
				<div class='widget-preview'>
					<?php the_excerpt(); ?>
				</div>
				<div class="widget-control-actions">
					<div class="alignleft">
						<a class="widget-control-edit" href="<?php echo get_edit_post_link( get_the_ID() ); ?>" target="_blank">Edit</a>
						<span class='widget-control-separator'>|</span>
						<a class="widget-control-remove" href="#remove">Delete</a>
					</div>
					<div class="widget-control-actions alignright">
						<a class="widget-control-view" href="<?php echo get_permalink( get_the_ID() ); ?>" target="_blank">View</a>
					</div>
					<br class="clear">
				</div>
			</div>
		</div>
		<?php
		wp_reset_postdata();
	}

	/* Output the columns in the admin edit taxonomy page
	 *
	 * @return
	 */
	function admin_render_columns( $term, $taxonomy ) {
		global $post, $wp_query;
		$term_description =  $this->get_term_description( $term, $taxonomy );

		// Calculate the number of columns we need
		$columns = array();
		foreach( $term_description as $c => $column ) {
			if( ! empty( $term_description[ $c ] ) )
				$columns[] = $c;
		}
		// Let's take a look at the column array;
		for ($col = 1; $col <= count( $columns ); $col++) {
			?>
			<div class="column autopop" id="col-<?php echo intval( $col ); ?>">
				<div class="widget-top">
					<div class="widget-title">
						<h4 class="hndle"><?php echo $col; ?><span class="in-widget-title"></span></h4>
					</div>
				</div>
				<div class="column-inner">
				<?php
				$slides = $term_description[ 'col-' . $col ];
				foreach( $slides as $key => $slide ) {
					list( $rubbish, $slide_id ) =  explode( '-', $slide );
					$post = get_post( $slide_id );
					$this->admin_render_slide( $post );
				}
				?>
				</div><!--/.column-inner-->

			</div>
			<?php
		}
		unset( $col );
	}

	/**
	 * Edit Term Control
	 *
	 * Create image control for wp-admin/edit-tag-form.php.
	 * Hooked into the '{$taxonomy}_edit_form_fields' action.
	 *
	 * @param	stdClass Term object.
	 * @param	string Taxonomy slug
	 * @uses	add_action()
	 * @uses	get_taxonomy()
	 * @uses	get_term_field
	 * @return 	null
	 */
	function taxonomy_edit_form( $term, $taxonomy ) {
		global $post;
		$associated_slides = $this->get_associated_slide_ids( $term, $taxonomy );
		//var_dump( $associated_slides );

		wp_nonce_field( $this->nonce_field, $this->nonce_field, false );
		?>
		<tr class="form-field hide-if-no-js">
			<th scope="row" valign="top">
				<p class="action-buttons">
					<?php submit_button( __('Update'), 'primary', 'submit', $wrap = false ); ?>
					<?php //submit_button( __('Add New'), '', 'add-button', $wrap = false ); ?>
					<button id="add-button" class="button">New <?php echo $this->post_type_singular_name; ?></button>
					<button id="add-column" class="button">New Column</button>
					<!--<button id="tidy-button" class="button">Tidy</button>-->
					<button id="view-button" class="button">View <?php echo $this->taxonomy_singular_name; ?></button>
					<span class="spinner"></span>
				</p>

				<div id="dialog" title="Edit <?php echo $this->post_type_singular_name; ?>" style="display: none;">
					<?php $this->modal_editor(); ?>
				</div>
			</th>
			<td>
				<div id="outer-container"  class="ui-widget-content">
					<!--<h3 class="ui-widget-header">Resizable</h3>-->
					<div id="container">
						<?php
						//THE NEW WAY
						$this->admin_render_columns( $term, $taxonomy );

						// Calculate the number of columns we need
						$columns = array();
						$term_description =  $this->get_term_description( $term, $taxonomy );
						foreach( $term_description as $c => $column ) {
							if( ! empty( $term_description[ $c ] ) )
								$columns[] = $c;
						}

						// The Slides Query
						$slides_query = new WP_Query( array(
							'post_type' => $this->post_types,
							'post_status' => 'publish',
							'orderby' => 'date',
							'order' => 'ASC',
							'cache_results' => true,
							'tax_query' => array( array(
								'taxonomy' => $this->taxonomy_slug,
								'field' => 'id',
								'terms' => $term->term_id
							) ),
							'posts_per_page' => -1, //consider making this something like 250 or 500 just to set a limit of some sort
							'post__not_in' => $associated_slides
						) );

						// The Loop
						if ( $slides_query->have_posts() ) {
							$col = count( $columns ) + 1; //Start with the number of existing cols
							while ( $slides_query->have_posts() ) {
								$slides_query->the_post();
								?>
								<div class="column backfill" id="col-<?php echo $col; ?>">
									<div class="widget-top">
										<div class="widget-title">
											<h4 class="hndle"><?php echo $col; ?><span class="in-widget-title"></span></h4>
										</div>
									</div>
									<div class="column-inner">
										<?php $this->admin_render_slide( $post ); ?>
									</div>
								</div>
								<?php
								$col++;
							}
							unset( $col );
						} elseif( 0 == count( $associated_slides ) ){ // If there are 0 slides
							//echo '<p>Sorry, No ' . $this->post_type_name . ' found!</p>';
							?>
							<div class="column backfill" id="col-1">
								<div class="widget-top">
									<div class="widget-title">
										<h4 class="hndle"><?php echo '1'; ?><span class="in-widget-title"></span></h4>
									</div>
								</div>
							</div>
							<?php
						}

						?>
					</div><!--/#container-->
				</div><!--/#outer-container-->
			</td>
		</tr>
		<?php
		// Cleanup
		wp_reset_postdata();
		unset( $slides_query );
	}

	/**
	 * Filter the taxonomy description
	 *
	 * Decodes the serialized description field
	 *
	 * @return stdClass
	 */
	function filter_get_terms( $terms, $taxonomies, $args ) {
		global $wpdb, $pagenow;

		/**********************************************
		* NOT currently working for category taxonomy *
		*********************************************/

 		/* Bail if we are not looking at this taxonomy's directory */
		if( 'edit-tags.php' != $pagenow || ( $this->taxonomy_slug != $_GET[ 'taxonomy' ] && 'category' != $_GET[ 'taxonomy' ] ) || isset( $_GET[ 'tag_ID' ] ) )
			return $terms;

		$taxonomy = $taxonomies[0];
		if ( ! is_array( $terms ) && count( $terms ) < 1 )
			return $terms;

		$filtered_terms = array();
		foreach ( $terms as $term ) {
			$term_decoded = json_decode( $term->description );
			if ( is_object( $term_decoded ) )
				$term->description = $term_decoded->description;
			$filtered_terms[] = $term;
		}
		return $filtered_terms;
	}

	/**
	 * Fetch the taxonomy slug
	 *
	 * @return string
	 */
	function get_taxonomy_slug() {
		return $this->taxonomy_slug;
	}

	/**
	 * FILL THIS OUT
	 *
	 * @return
	 */
	function action_restrict_manage_posts() {
		global $typenow;

		if ( $typenow == $this->post_type_slug ) {
			$selected = isset( $_GET[ $this->taxonomy_slug ] ) ? $_GET[ $this->taxonomy_slug ] : '';
			$info_taxonomy = get_taxonomy( $this->taxonomy_slug );
			wp_dropdown_categories( array(
				'show_option_all' => __( "Show All {$info_taxonomy->label}" ),
				'taxonomy' => $this->taxonomy_slug,
				'name' => $this->taxonomy_slug,
				'orderby' => 'name',
				'selected' => $selected,
				'show_count' => true,
				'hide_empty' => true,
			) );
		}
	}

	/**
	 * FILL THIS OUT
	 *
	 * @return
	 */
	function action_parse_query( $query ) {
		global $pagenow;

		if ( $pagenow == 'edit.php' && isset( $query->query_vars[ 'post_type' ] ) && $query->query_vars[ 'post_type' ] == $this->post_type_slug
		&& isset( $query->query_vars[ $this->taxonomy_slug] ) && is_numeric( $query->query_vars[ $this->taxonomy_slug ] ) && $query->query_vars[ $this->taxonomy_slug ] != 0 ) {
			$term = get_term_by( 'id', $q_vars[$taxonomy], $taxonomy );
			$query->query_vars[ $this->taxonomy_slug ] = $term->slug;
		}
	}

	/**
	 * Rewrite the slide permalinks in order to play nice with reveal.js
	 *
	 * @return string
	 */
	function append_query_string( $url, $post ) {
		global $pagenow;

		// Do not do this on the create new post screen since there is no post ID yet
		if( $pagenow != 'post-new.php' && $this->post_type_slug == $post->post_type ) {
			$terms = array_values( get_the_terms( $post->ID, $this->taxonomy_slug ) );
			$term = $terms[0];
			$url = home_url( implode( '/', array( $this->taxonomy_slug, $term->slug, '#', $post->post_name ) ) );
		}
		return $url;
	}

	/**
	 * Render the TinyMCE editor
	 *
	 * @return null
	 */
    function modal_editor( $post_id = '' ) {
        wp_editor( $content = '', $editor_id = 'editor_' . $this->post_type_slug, array(
			'wpautop' => false, // use wpautop?
			'media_buttons' => true, // show insert/upload button(s)
			'textarea_name' => $editor_id, // set the textarea name to something different, square brackets [] can be used here
			'textarea_rows' => 20,
			'tabindex' => '',
			'tabfocus_elements' => ':prev,:next', // the previous and next element ID to move the focus to when pressing the Tab key in TinyMCE
			'editor_css' => '', // intended for extra styles for both visual and Text editors buttons, needs to include the <style> tags, can use "scoped".
			'editor_class' => 'reveal-steven', // add extra class(es) to the editor textarea
			'teeny' => false, // output the minimal editor config used in Press This
			'dfw' => false, // replace the default fullscreen with DFW (needs specific DOM elements and css)
            'tinymce' => array(
            	'plugins' => 'inlinepopups, fullscreen, wordpress, wplink, wpdialogs',
             ),
			'quicktags' => true // load Quicktags, can be used to pass settings directly to Quicktags using an array()
		) );
    }

	/**
	 * Modify the TinyMCE editor
	 *
	 * @return array
	 */
	function filter_tiny_mce_before_init( $args ) {
   		$args['body_class'] = 'reveal';
   		$args['height'] = "450";
    	return $args;
	}

	/**
	 * AJAX Get Slide
	 *
	 * @return array
	 */
	function action_wp_ajax_get_slide() {
		// Nonce check
		if ( ! wp_verify_nonce( $_REQUEST[ 'nonce' ], $this->nonce_field ) ) {
			wp_die( $this->nonce_fail_message );
		}

		$post_id = $_REQUEST[ 'id' ];
		$post = get_post( $post_id );
		echo $post->post_content;
		die();
	}

	/**
	 * AJAX Update Slide
	 *
	 * @return array
	 */
	function action_wp_ajax_update_slide() {
		// Nonce check
		if ( ! wp_verify_nonce( $_REQUEST[ 'nonce' ], $this->nonce_field ) ) {
			wp_die( $this->nonce_fail_message );
		}

		global $post;

		$post_id = $_REQUEST[ 'id' ];
		$safe_content = wp_kses_post( $_REQUEST[ 'content' ] );

		$updated_post = array(
			'ID' => $post_id,
			'post_content' => $safe_content,
		);
		wp_update_post( $updated_post );

		$post = get_post( $post_id );
		setup_postdata( $post );
		the_excerpt();
		wp_reset_postdata();

		die();
	}

	/**
	 * AJAX Add Slide
	 *
	 * @return array
	 */
	function action_wp_ajax_new_slide() {
		// Nonce check
		if ( ! wp_verify_nonce( $_REQUEST[ 'nonce' ], $this->nonce_field ) ) {
			wp_die( $this->nonce_fail_message );
		}

		global $post;
		$safe_content = wp_kses_post( $_REQUEST[ 'content' ] );

		$presentation = get_term_by( 'id', $_REQUEST['presentation'], $this->taxonomy_slug );

		$new_post = array(
			'post_title' => strip_tags($safe_content),
			'post_content' => $safe_content,
			'post_status' => 'publish',
			'post_type' => $this->post_type_slug
		);

		$post_id = wp_insert_post( $new_post );
		wp_set_object_terms( $post_id , $presentation->name, $this->taxonomy_slug );

		$post = get_post( $post_id );
		$this->admin_render_slide( $post );

		die();
	}

	/**
	 * AJAX Delete Slide
	 *
	 * @return array
	 */
	function action_wp_ajax_delete_slide() {
		// Nonce check
		if ( ! wp_verify_nonce( $_REQUEST[ 'nonce' ], $this->nonce_field ) ) {
			wp_die( $this->nonce_fail_message );
		}

		global $post;
		$post_id = $_REQUEST[ 'id' ];
		wp_delete_post( $_REQUEST[ 'id' ], false );
		die();
	}

	/**
	 * AJAX Save Presentation
	 *
	 * @return array
	 */
	function action_wp_ajax_update_presentation() {
		// Nonce check
		if ( ! wp_verify_nonce( $_REQUEST[ 'nonce' ], $this->nonce_field ) ) {
			wp_die( $this->nonce_fail_message );
		}

		$presentation_id = $_REQUEST[ 'id' ];
		$safe_description = sanitize_text_field( $_REQUEST[ 'content' ] );

		$updated_presentation = array(
			'description' => $safe_description
		);

		wp_update_term( $presentation_id, $this->taxonomy_slug, $updated_presentation );
		die();
	}

} // Class
WP_Present::instance();

