<?php
class WP_Present_Customizer {

	/* Define and register singleton */
	private static $instance = false;
	public static function instance() {
		if( ! self::$instance ) {
			self::$instance = new WP_Present_Customizer;
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
		global $pagenow;

		if( ! is_admin() && $pagenow != 'customize.php' ) {
			return;
		}

		add_action( 'plugins_loaded', array( $this, 'action_plugins_loaded' ) );
		add_action( 'admin_init', array( $this, 'action_admin_init' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'action_admin_enqueue_scripts' ) );
		add_action( 'admin_head', array( $this, 'action_admin_head' ), 20 );
		add_action( 'admin_footer', array( $this, 'action_admin_footer' ), 20 );

		// Core Customizer
		add_action( 'customize_controls_print_scripts', 'print_head_scripts', 20 );
		add_action( 'customize_controls_print_footer_scripts', '_wp_footer_scripts' );
		add_action( 'customize_controls_print_styles', 'print_admin_styles', 20 );

	}

	public function action_plugins_loaded() {
		require( ABSPATH . WPINC . '/class-wp-customize-manager.php' );
		// Init Customize class
		$GLOBALS['wp_customize'] = new WP_Customize_Manager;
	}

	public function action_admin_init() {

		global $wp_scripts, $wp_customize;

		$registered = $wp_scripts->registered;
		$wp_scripts = new WP_Scripts;
		$wp_scripts->registered = $registered;

		do_action( 'customize_controls_init' );
	}

	public function action_admin_enqueue_scripts() {
		wp_enqueue_script( 'customize-controls' );
		wp_enqueue_style( 'customize-controls' );
		wp_enqueue_script( 'accordion' );
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );

		do_action( 'customize_controls_enqueue_scripts' );
	}

	public function action_admin_head() {
		do_action( 'customize_controls_print_styles' );
		do_action( 'customize_controls_print_scripts' );
	}

	public function render() {
		?>
		<div id="customize-controls" class="wrap wp-full-overlay-sidebar">
			<div id="customize-header-actions" class="wp-full-overlay-header">
				<!--
				<input type="submit" name="save" id="save" class="button button-primary save" value="Saved" disabled="">
				<span class="spinner"></span>
				<a class="back button" href="http://www.stevenword.com/">Close</a>
				-->
			</div>
			<div class="wp-full-overlay-sidebar-content accordion-container" tabindex="-1">
				<div id="customize-info" class="accordion-section ">
					<div class="accordion-section-title" aria-label="Theme Customizer Options" tabindex="0">
						<span class="preview-notice">You are editing<strong class="theme-name"></strong></span>
					</div>
					<div class="accordion-section-content">
						<p>Title</p>
						<input id="slide-title" name="slide-title" style="width:95%;"/>
					</div>
				</div>
				<div id="customize-theme-controls"><ul>
				<?php
					global $wp_customize;
					foreach ( $wp_customize->sections() as $section )
						$section->maybe_render();
				?>
				</ul></div>
			</div>
		</div>
		<div id="customize-footer-actions" class="wp-full-overlay-footer">
			<a href="#" class="collapse-sidebar button-secondary" title="<?php esc_attr_e('Collapse Sidebar'); ?>">
				<span class="collapse-sidebar-arrow"></span>
				<span class="collapse-sidebar-label"><?php _e('Collapse'); ?></span>
			</a>
		</div>
		<?php
	}

	public function action_admin_footer() {
		do_action( 'customize_controls_print_footer_scripts' );
	}

} // Class
WP_Present_Customizer::instance();
