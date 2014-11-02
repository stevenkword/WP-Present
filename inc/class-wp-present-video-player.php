<?php
/**
 ** Generic Feature
 ** Since 0.9.7
 **/
class WP_Present_Video_Player {

	// Version
	const VERSION            = '1.0.0';
	const VERSION_OPTION     = 'wp_present_video_player_version';
	const REVISION           = '20140213';

	private static $version  = false;
	private static $instance = false;

	/**
	 * Implement singleton
	 *
	 * @uses self::setup
	 * @return self
	 */
	public static function get_instance() {
		if ( ! is_a( self::$instance, __CLASS__ ) ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * Clone
	 *
	 * @since 1.0.0
	 */
	private function __clone() { }

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	private function __construct() {

		// Version Check
		if( $version = get_option( self::VERSION_OPTION, false ) ) {
			$this->version = $version;
		} else {
			$this->version = self::VERSION;
			add_option( self::VERSION_OPTION, $this->version );
		}

		// Add Meta Boxes
		if( '3.0' <= $wp_version ) { // The `add_meta_boxes` action hook did not exist until WP 3.0
			add_action( 'add_meta_boxes', array( $this, 'action_add_meta_boxes' ) );
		} else {
			add_action( 'admin_init', array( $this, 'action_add_meta_boxes' ) );
		}

		add_action( 'init', array( $this, 'action_init_check_version' ) );
//		add_action( 'init', array( $this, 'action_init_register_post_types' ) );
	}

	/**
	 * Version Check
	 *
	 * @since 1.0.0
	 */
	function action_init_check_version() {
		// Check if the version has changed and if so perform the necessary actions
		if ( ! isset( $this->version ) || $this->version <  self::VERSION ) {
			// Do version upgrade tasks here
			update_option( self::VERSION_OPTION, self::VERSION );
		}
	}

	/**
	 * Add the Generic Feature metabox
	 *
	 * @uses add_meta_box
	 * @since 1.0.0
	 * @return null
	 */
	function action_add_meta_boxes() {
		add_meta_box( 'video-player-metabox', __( 'Video Embed' ), array( $this, 'render_metabox' ), 'presentations', 'side', 'default' );
	}

	/**
	 * Remder the Generic Feature metabox
	 *
	 * @since 1.0.0
	 * @return null
	 */
	function render_metabox() {
		echo '<p>Embed video a video link in your slides to allow your audience to view your video presentation from within the slidedecks.</p>';
	}

} // Class
WP_Present_Video_Player::get_instance();