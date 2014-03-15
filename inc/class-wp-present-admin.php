<?php
/**
 ** WP Present Admin
 **
 ** @since 0.9.4
 **/
class WP_Present_Admin {

	const REVISION = 20131229;

	public $plugins_url = '';
	public $nonce_fail_message = '';

	// Define and register singleton
	private static $instance = false;
	public static function instance() {
		if( ! self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * Clone
	 *
	 * @since 0.9.0
	 */
	private function __clone() { }

	/**
	 * Constructor
	 *
	 * @since 0.9.0
	 */
	private function __construct() {

		// Setup
		$this->plugins_url = plugins_url( '/wp-present' );
		$this->nonce_fail_message = __( 'Cheatin&#8217; huh?' );

		// Admin
		add_action( 'admin_menu', array( $this, 'action_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'action_admin_enqueue_scripts' ) );
		add_action( 'admin_head', array( $this, 'action_admin_head' ), 20 );
		//add_action( 'save_post', array( $this, 'action_save_post' ) );
		add_action( 'admin_footer', array( $this, 'action_admin_footer' ), 20 );

		add_filter( 'admin_body_class', array( $this, 'filter_admin_body_class' ) );

	}

	/**
	 * MP6 or bust.
	 *
	 * http://make.wordpress.org/ui/2013/11/19/targeting-the-new-dashboard-design-in-a-post-mp6-world/
	 *
	 * @since 0.9.5
	 */
	function filter_admin_body_class( $classes ) {
		if ( version_compare( $GLOBALS['wp_version'], '3.8-alpha', '>' ) ) {
			$classes = explode( " ", $classes );
			if ( ! in_array( 'mp6', $classes ) ) {
				$classes[] = 'mp6';
			}
			$classes = implode( " ", $classes );
		}
		return $classes;
	}

	/**
	 * Add the necessary menu pages
	 *
	 * @return null
	 */
	public function action_admin_menu(){
		global $menu, $submenu;

		// Taxonomy Menu
		$post_type_url = 'edit.php?post_type=' . WP_Present_Core::POST_TYPE_TAXONOMY;


		// Add the options page
		add_submenu_page( $post_type_url, WP_Present_Core::OPTION_TITLE, 'Options', WP_Present_Core::CAPABILITY, WP_Present_Core::OPTION_NAME, array( $this, 'options_page' ) );

		// Remove unwanted menu items
		foreach( $menu as $menu_key => $menu_item ) {
			if( WP_Present_Core::POST_TYPE_TAXONOMY == $menu_item[0] ) {
				$menu[ $menu_key ][0] = WP_Present_Core::TAXONOMY_NAME;
			}
		}
	}

	/**
	 * Markup for the Options page
	 *
	 * @since  0.9.6
	 * @return null
	 */
	public function options_page(){
		WP_Present_Settings::settings_page();
	}

	/**
	 * Enqueue necessary admin scripts
	 *
	 * @uses wp_enqueue_script
	 * @return null
	 */
	public function action_admin_enqueue_scripts() {

		// Only add this variable on the edit taxonomy page
		global $pagenow;

		if( 'edit-tags.php' != $pagenow || ! isset( $_GET['taxonomy'] ) || WP_Present_Core::TAXONOMY_SLUG != $_GET['taxonomy'] ) {
			return;
		}

		// Hide screen options
		add_filter( 'screen_options_show_screen', '__return_false' ); // a test

		// Admin Styles
		wp_enqueue_style( 'wp-present-admin', $this->plugins_url . '/css/admin.css', '', self::REVISION );

		if(  ! isset( $_GET['tag_ID'] ) ) {
			return;
		}

		// Admin Scripts
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'jquery-ui-resizable' );
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_script( 'admin-widgets' );

		wp_enqueue_script( 'wp-present-admin', $this->plugins_url . '/js/admin.js', array( 'jquery' ), /*self::REVISION*/filemtime( __FILE__ ), true );

		//wp_enqueue_media();
		wp_enqueue_style( 'media-views' );

		if( isset( $_REQUEST['tag_ID'] ) )
			wp_localize_script( 'wp-present-admin', 'presentation', $_REQUEST['tag_ID'] );
	}

	/**
	 * Output for the admin <head>
	 *
	 * @return null
	 */
	public function action_admin_head() {

		// Presentation dashicon
		echo '<style type="text/css">.mp6 #adminmenu #menu-posts-' . WP_Present_Core::POST_TYPE_TAXONOMY . ' div.wp-menu-image:before { content: "\f181" !important; }</style>';

		// Only add this variable on the edit taxonomy page
		global $pagenow;
		if( 'edit-tags.php' != $pagenow || ! isset( $_GET['taxonomy'] ) || WP_Present_Core::TAXONOMY_SLUG != $_GET['taxonomy'] || ! isset( $_GET['tag_ID'] ) )
			return;

		$num_slides = ( isset( $_GET['tag_ID'] ) ) ? count( WP_Present_Core::get_associated_slide_ids( $_GET['tag_ID'], $_GET['taxonomy'] ) ) : '';

		$slides_query = new WP_Query( array(
			'post_type'     => WP_Present_Core::POST_TYPE_SLUG, //post type, I used 'product'
			'post_status'   => 'publish', // just tried to find all published post
			'posts_per_page' => -1,  //show all
			'tax_query' => array( array(
				'taxonomy' 	=> WP_Present_Core::TAXONOMY_SLUG,
				'terms'		=> $_GET['tag_ID']
			) )
		) );
		$num_slides = (int) $slides_query->post_count;
		unset( $slides_query );

		wp_localize_script( 'wp-present-admin', 'WPPNumSlides', array( intval( $num_slides ) ) );

		if( isset( $_REQUEST['tag_ID'] ) )
			wp_localize_script( 'wp-present-admin', 'WPPTaxonomyURL', array( get_term_link( (int) $_GET['tag_ID'], WP_Present_Core::TAXONOMY_SLUG ) ) );

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
	 *
	 * CURRENTLY UNHOOKED
	 *
	 */
	public function action_save_post( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return;

		// Broken
		//if ( ! isset( $_POST[ WP_Present_Core::NONCE_FIELD ] ) || ! wp_verify_nonce( $_POST[ WP_Present_Core::NONCE_FIELD ], WP_Present_Core::NONCE_FIELD ) )
			//return;

		if ( in_array( array( 'page', 'post' ), get_post_type( $post_id ) ) && ! current_user_can( 'edit_page', $post_id ) )
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
