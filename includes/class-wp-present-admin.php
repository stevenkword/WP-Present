<?php
/**
 ** WP Present Admin
 **
 ** @since 0.9.4
 **/
class WP_Present_Admin {

	const REVISION = 20131204;

	public $plugins_url = '';
	public $nonce_fail_message = '';

	/* Define and register singleton */
	private static $instance = false;
	public static function instance() {
		if( ! self::$instance ) {
			self::$instance = new WP_Present_Admin;
		}
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
		$this->nonce_fail_message = __( 'Cheatin&#8217; huh?' );

		// Admin
		add_action( 'admin_menu', array( $this, 'action_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'action_admin_enqueue_scripts' ) );
		add_action( 'admin_head', array( $this, 'action_admin_head' ), 20 );
		add_action( 'save_post', array( $this, 'action_save_post' ) );
		add_action( 'admin_footer', array( $this, 'action_admin_footer' ), 20 );

	}

	/**
	 * Add the necessary menu pages
	 *
	 * @return null
	 */
	public function action_admin_menu(){
		global $menu, $submenu;

		// Taxonomy Menu
		$taxonomy_url = 'edit-tags.php?taxonomy=' . WP_Present_Core::TAXONOMY_SLUG . '&post_type='.WP_Present_Core::POST_TYPE_SLUG;
		$post_type_url = 'edit.php?post_type=' . WP_Present_Core::POST_TYPE_SLUG;


		// Add the options page
		add_submenu_page( $post_type_url, WP_Present_Core::OPTION_TITLE, 'Options', WP_Present_Core::CAPABILITY, WP_Present_Core::OPTION_NAME, array( $this, 'options_page' ) );

		// Rename the menu item
		foreach( $menu as $menu_key => $menu_item ) {
			if( WP_Present_Core::POST_TYPE_NAME == $menu_item[0] ) {
				$menu[ $menu_key ][0] = WP_Present_Core::TAXONOMY_NAME;
			}
		}

		// Move the taxonomy menu to the top
		// TODO: It would be better to search for the keys based on url
		foreach( $submenu as $submenu_key => $submenu_item ) {
			if( isset( $submenu_item[15][0] ) && WP_Present_Core::TAXONOMY_NAME == $submenu_item[15][0] ) {
				// This is a bit of hackery.  I should search for these keys
				$submenu[$submenu_key][2] = $submenu[$submenu_key][15];
				unset( $submenu[$submenu_key][15] );

				// Not a fan of the add new bit
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
	public function options_page(){
		?>
		<div id="wpbody">
			<div id="wpbody-content" aria-label="Main content" tabindex="0">
				<div class="wrap">
					<h2><?php echo WP_Present_Core::OPTION_TITLE; ?></h2>
					<h3>Select a Theme</h3>
						<p>Current Theme: <?php echo WP_Present_Core::DEFAULT_THEME; ?></p>
					<h3>Resolution</h3>
						<p>1024x768</p>
					<h3>Branding</h3>
						<p><textarea>Branding HTML textarea goes here</textarea></p>
					<h3>Coming soon</h3>
						<?php
						//Get plugin path
						$plugin_path = dirname( dirname( __FILE__ ) );
						$master_plan_file = fopen( $plugin_path . '/master.plan', 'r' );
						while ( ! feof( $master_plan_file ) )
							echo fgets( $master_plan_file ) . '<br />';
						fclose( $master_plan_file );
						?>
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
	public function action_admin_enqueue_scripts() {

		// Admin Styles
		wp_enqueue_style( 'wp-present-admin', $this->plugins_url . '/css/admin.css', '', self::REVISION );

		// Admin Scripts
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'jquery-ui-resizable' );
		wp_enqueue_script( 'jquery-ui-dialog' );

		wp_enqueue_script( 'wp-present-admin', $this->plugins_url . '/js/admin.js', array( 'jquery' ), self::REVISION, true );

		//wp_enqueue_media();
		wp_enqueue_style( 'media-views' );

		if( isset( $_REQUEST[ 'tag_ID' ] ) )
			wp_localize_script( 'wp-present-admin', 'presentation', $_REQUEST[ 'tag_ID' ] );
	}

	/**
	 * Output for the admin <head>
	 *
	 * @return null
	 */
	public function action_admin_head() {

		// Presentation dashicon
	    echo '<style type="text/css">.mp6 #adminmenu #menu-posts-slide div.wp-menu-image:before { content: "\f181" !important; }</style>';

		// Only add this variable on the edit taxonomy page
		global $pagenow;
		if( 'edit-tags.php' != $pagenow || ! isset( $_GET['taxonomy'] ) || WP_Present_Core::TAXONOMY_SLUG != $_GET['taxonomy'] || ! isset( $_GET[ 'tag_ID' ] ) )
			return;

		$num_slides = ( isset( $_GET[ 'tag_ID' ] ) ) ? count( WP_Present_Core::get_associated_slide_ids( $_GET[ 'tag_ID' ], $_GET[ 'taxonomy' ] ) ) : '';

		$slides_query = new WP_Query( array(
			'post_type'     => WP_Present_Core::POST_TYPE_SLUG, //post type, I used 'product'
			'post_status'   => 'publish', // just tried to find all published post
			'posts_per_page' => -1,  //show all
			'tax_query' => array( array(
				'taxonomy' 	=> WP_Present_Core::TAXONOMY_SLUG,
				'terms'		=> $_GET[ 'tag_ID' ]
			) )
		) );
		$num_slides = (int) $slides_query->post_count;
		unset( $slides_query );

		wp_localize_script( 'wp-present-admin', 'WPPNumSlides', array( intval( $num_slides ) ) );

		if( isset( $_REQUEST[ 'tag_ID' ] ) )
			wp_localize_script( 'wp-present-admin', 'WPPTaxonomyURL', array( get_term_link( (int) $_GET[ 'tag_ID' ], WP_Present_Core::TAXONOMY_SLUG ) ) );

		// Make the admin outer-container div big enough to prevent wrapping
		$column_width = 210;
		$container_size = ( $num_slides + 1 ) * $column_width;
		?>
		<style type="text/css">
			#container{ width: <?php echo $container_size; ?>px;}
		</style>
		<?php
		unset( $num_slides );
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
		//if ( ! isset( $_POST[ WP_Present_Core::NONCE_FIELD ] ) || ! wp_verify_nonce( $_POST[ WP_Present_Core::NONCE_FIELD ], WP_Present_Core::NONCE_FIELD ) )
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
	public function action_admin_footer() {
		// Only run on the edit taxonomy page
		global $pagenow;
		if( 'edit-tags.php' != $pagenow || ! isset( $_GET['taxonomy'] ) || WP_Present_Core::TAXONOMY_SLUG != $_GET['taxonomy'] )
			return;
	}


} // Class
WP_Present_Admin::instance();
