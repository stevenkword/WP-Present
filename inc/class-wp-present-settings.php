<?php
/**
 ** WP Present Settings
 **
 ** @since 0.9.6
 **/
class WP_Present_Settings {

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
	 * @since 0.9.6
	 */
	private function __clone() { }

	/**
	 * Constructor
     *
	 * @since 0.9.6
	 */
	private function __construct() {

		// Setup
		$this->plugins_url = plugins_url( '/wp-present' );
		$this->nonce_fail_message = __( 'Cheatin&#8217; huh?' );

		// Admin
		add_action( 'admin_enqueue_scripts', array( $this, 'action_admin_enqueue_scripts' ) );

	}

	/**
	 * Are we looking at the settings page?
	 *
	 * @since 0.9.6
	 * @return bool
	 */
	function is_settings_page() {
		global $pagenow;

		if( is_admin() && 'edit.php' == $pagenow && isset( $_GET['page'] ) && 'presentation-options' == $_GET['page'] ) {
			return true;
		}
		return false;
	}

	/**
	 * Enqueue necessary admin scripts
	 *
	 * @uses wp_enqueue_script
	 * @return null
	 */
	public function action_admin_enqueue_scripts() {

		// Only add this variable on the settings page
		if( ! self::is_settings_page() ) {
			return;
		}

		// Settings Styles
		wp_enqueue_style( 'wp-present-genericons', $this->plugins_url . '/fonts/genericons/genericons.css', '', self::REVISION );
		wp_enqueue_style( 'wp-present-admin', $this->plugins_url . '/css/settings.css', '', self::REVISION );
	}

	/**
	 * Markup for the Options page
	 *
	 * @return null
	 */
	public function settings_page(){

		// Get active tab
		$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'general';

		?>
		<div id="wpbody">
			<div id="wpbody-content" aria-label="Main content" tabindex="0">
				<div class="wrap">
					<?php //screen_icon(); ?>
					<h2><?php _e( 'Presentation Options', WP_Present_Core::TEXT_DOMAIN );?></h2>
					<?php settings_errors(); ?>
					<div id="poststuff" class="metabox-holder has-right-sidebar">
						<div class="inner-sidebar" id="side-info-column">
							<div id="side-sortables" class="meta-box-sortables ui-sortable">
								<div id="wppresent_display_option" class="postbox ">
									<h3 class="hndle"><span><?php _e( 'Help Improve WP Present', WP_Present_Core::TEXT_DOMAIN );?></span></h3>
									<div class="inside">
										<p><?php _e( 'We would really appreciate your input to help us continue to improve the product.', WP_Present_Core::TEXT_DOMAIN );?></p>
										<p>
										<?php printf( __( 'Find us on %1$s or donate to the project using the button below.', WP_Present_Core::TEXT_DOMAIN ), '<a href="https://github.com/stevenkword/WP-Present" target="_blank">GitHub</a>' ); ?>
										</p>
										<div style="width: 100%; text-align: center;">
											<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
												<input type="hidden" name="cmd" value="_s-xclick">
												<input type="hidden" name="hosted_button_id" value="6T4UQQXTXLKVW">
												<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
												<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
											</form>
										</div>
									</div>
								</div>
								<div id="wppresent_display_contact" class="postbox ">
									<h3 class="hndle"><span><?php _e( 'Contact WP Present', WP_Present_Core::TEXT_DOMAIN );?></span></h3>
									<div class="inside">
										<ul class="wppresent-contact-links">
											<li><a class="link-wppresent-forum" href="http://wordpress.org/support/plugin/wp-present" target="_blank"><?php _e( 'Support Forums', WP_Present_Core::TEXT_DOMAIN );?></a></li>
											<li><a class="link-wppresent-web" href="http://stevenword.com/plugins/wp-present/" target="_blank"><?php _e( 'WP Present on the Web', WP_Present_Core::TEXT_DOMAIN );?></a></li>
											<li><a class="link-wppresent-github" href="https://github.com/stevenkword/WP-Present" target="_blank"><?php _e( 'GitHub Project', WP_Present_Core::TEXT_DOMAIN );?></a></li>
											<li><a class="link-wppresent-review" href="http://wordpress.org/support/view/plugin-reviews/wp-present" target="_blank"><?php _e( 'Review on WordPress.org', WP_Present_Core::TEXT_DOMAIN );?></a></li>
										</ul>
									</div>
								</div>
							</div>
						</div>
						<div id="post-body-content">
							<h2 class="nav-tab-wrapper" style="padding: 0;">
								<a href="?post_type=<?php echo WP_Present_Core::POST_TYPE_TAXONOMY;?>&page=presentation-options&tab=general" class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>">General</a>

								<a href="?post_type=<?php echo WP_Present_Core::POST_TYPE_TAXONOMY;?>&page=presentation-options&tab=about" class="nav-tab <?php echo $active_tab == 'about' ? 'nav-tab-active' : ''; ?>">About</a>
							</h2>
							<form method="post" action="options.php">
								<?php
								if( $active_tab == 'about' ) {
									self::display_about_page();
								} else {
									self::display_general_options();
									submit_button();
								} // end if/else
								?>
							</form>
						</div>
					</div>
				</div><!--/.wrap-->
				<div class="clear"></div>
			</div><!-- wpbody-content -->
			<div class="clear"></div>
		</div>
		<?php
	}

	function display_general_options(){
		?>
			<h3>Select a Theme</h3>
				<p>Current Theme: <?php echo WP_Present_Core::DEFAULT_THEME; ?></p>
			<h3>Resolution</h3>
				<p>1024x640</p>
			<h3>Branding</h3>
				<p><textarea>Branding HTML textarea goes here</textarea></p>
			<h3>Misc</h3>
				<p>Filetime: <?php echo filemtime( __FILE__ ); ?>
		<?php
	}

	function display_about_page(){
		//Get plugin path
		$plugin_path = dirname( dirname( __FILE__ ) );
		$master_plan_file = fopen( $plugin_path . '/readme.txt', 'r' );
		while ( ! feof( $master_plan_file ) )
			echo fgets( $master_plan_file ) . '<br />';
		fclose( $master_plan_file );
	}

} // Class
WP_Present_Settings::instance();