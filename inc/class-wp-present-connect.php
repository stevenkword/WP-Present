<?php
/**
 ** WP Connect Feature
 ** Since 0.9.7
 **/

define ( 'CLIENT_ID', 33697 ); //TODO
define ( 'CLIENT_SECRET', 'h3NkTG1GkfYZq48b1BAUNEzCJ51PfJkqjkuBbtyJVgMFSnP7M1rob6elbUfmkhDi' ); //TODO
define ( 'LOGIN_URL', 'http://wppresent.com/test/' ); //TODO
define ( 'REDIRECT_URL', 'http://wppresent.com/test/connected.php' ); //TODO
define ( 'REQUEST_TOKEN_URL', 'https://public-api.wordpress.com/oauth2/token' );
define ( 'AUTHENTICATE_URL', 'https://public-api.wordpress.com/oauth2/authenticate' );

class WP_Present_Connect {

	// Version
	const VERSION            = '1.0.0';
	const VERSION_OPTION     = 'wp_present_connect_version';
	const REVISION           = '20140314';

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

		add_action( 'init', array( $this, 'action_init_check_version' ) );
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

} // Class
WP_Present_Connect::get_instance();