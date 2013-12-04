<?php
/**
 ** WP Present Admin
 **
 ** @since 0.9.4
 **/
class WP_Present_Admin {

	const SCRIPTS_VERSION = 20131204;

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

	}

} // Class
WP_Present_Admin::instance();
