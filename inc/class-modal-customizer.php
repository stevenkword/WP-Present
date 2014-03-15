<?php
/**
 *  WP Present Modal Customizer
 */
class WP_Present_Modal_Customizer {

	/* Define and register singleton */
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

		// Only if the taxonomy is set (not a listing)
		if( ! is_admin() || ! isset( $_REQUEST['tag_ID'] ) )
			return;

		add_action( 'plugins_loaded', array( $this, 'action_plugins_loaded' ) );

		// Let's roll.
		add_action( 'wp_loaded', array( $this, 'action_wp_loaded' ) );

		// Remove and define new Theme Customizer sections, settings, and controls
	    add_action( 'customize_register', array( $this, 'action_customize_register' ), 99 );

		add_action( 'admin_init', array( $this, 'action_admin_init' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'action_admin_enqueue_scripts' ) );
		add_action( 'admin_head', array( $this, 'action_admin_head' ), 20 );
		add_action( 'admin_footer', array( $this, 'action_admin_footer' ), 20 );

		// Core Customizer
		add_action( 'customize_controls_print_scripts', 'print_head_scripts', 20 );
		add_action( 'customize_controls_print_footer_scripts', '_wp_footer_scripts' );
		add_action( 'customize_controls_print_styles', 'print_admin_styles', 20 );

	}

	/**
	 * Register styles/scripts and initialize the preview of each setting
	 *
	 * @since 3.4.0
	 */
	public function action_wp_loaded() {
		$this->customize_preview_init();
	}

	/**
	 * Print javascript settings.
	 *
	 * @since 0.9.0
	 */
	public function customize_preview_init() {
		global $wp_customize;

		$wp_customize->prepare_controls();

		wp_enqueue_script( 'customize-preview' );
		add_action( 'wp_head', array( $wp_customize, 'customize_preview_base' ) );
		add_action( 'wp_head', array( $wp_customize, 'customize_preview_html5' ) );
		add_action( 'wp_footer', array( $wp_customize, 'customize_preview_settings' ), 20 );
		add_action( 'shutdown', array( $wp_customize, 'customize_preview_signature' ), 1000 );
		add_filter( 'wp_die_handler', array( $wp_customize, 'remove_preview_signature' ) );

		foreach ( $wp_customize->settings() as $setting ) {
			$setting->preview();
		}

		do_action( 'customize_preview_init', $wp_customize );
	}

	public function action_plugins_loaded() {

		require( ABSPATH . WPINC . '/class-wp-customize-manager.php' );

		// Init Customize class
		$GLOBALS['wp_customize'] = new WP_Customize_Manager;

		require( plugin_dir_path( __FILE__ ) . '/class-customizer-background-image-control.php' );
	}

	public function action_admin_init() {

		global $wp_scripts, $wp_customize;

		$registered = $wp_scripts->registered;
		$wp_scripts = new WP_Scripts;
		$wp_scripts->registered = $registered;

		do_action( 'customize_controls_init' );
	}

	public function action_admin_enqueue_scripts() {
		wp_enqueue_style( 'customize-controls' );
		wp_enqueue_script( 'customize-controls' );
		wp_enqueue_script( 'accordion' );
		wp_enqueue_script( 'wp-present-customize-controls', WP_Present_Core::instance()->plugins_url . '/js/customize-controls.js', array( 'jquery' ), WP_Present_Core::REVISION, true );
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
						<span class="preview-notice"><span class="preview-notice-text">You are editing</span><strong class="theme-name"></strong></span>
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

		<div id="customize-preview" class="wp-full-overlay-main"></div>


		<div id="customize-footer-actions" class="wp-full-overlay-footer">
			<a href="#" class="collapse-sidebar button-secondary" title="<?php esc_attr_e('Collapse Sidebar'); ?>">
				<span class="collapse-sidebar-arrow"></span>
				<span class="collapse-sidebar-label"><?php _e('Collapse'); ?></span>
			</a>
		</div>
		<?php
	}

	public function action_admin_footer() {
		global $wp_customize;

		do_action( 'customize_controls_print_footer_scripts' );

		$url = $is_ios = false;

		// If the frontend and the admin are served from the same domain, load the
		// preview over ssl if the customizer is being loaded over ssl. This avoids
		// insecure content warnings. This is not attempted if the admin and frontend
		// are on different domains to avoid the case where the frontend doesn't have
		// ssl certs. Domain mapping plugins can allow other urls in these conditions
		// using the customize_allowed_urls filter.

		$allowed_urls = array( home_url('/') );
		$admin_origin = parse_url( admin_url() );
		$home_origin  = parse_url( home_url() );
		$cross_domain = ( strtolower( $admin_origin['host'] ) != strtolower( $home_origin['host'] ) );

		if ( is_ssl() && ! $cross_domain )
			$allowed_urls[] = home_url( '/', 'https' );

		$allowed_urls = array_unique( apply_filters( 'customize_allowed_urls', $allowed_urls ) );

		$fallback_url = add_query_arg( array(
			'preview'        => 1,
			'template'       => $wp_customize->get_template(),
			'stylesheet'     => $wp_customize->get_stylesheet(),
			'preview_iframe' => true,
			'TB_iframe'      => 'true'
		), home_url( '/' ) );

		$login_url = add_query_arg( array(
			'interim-login' => 1,
			'customize-login' => 1
		), wp_login_url() );

		$settings = array(
			'theme'    => array(
				'stylesheet' => $wp_customize->get_stylesheet(),
				'active'     => $wp_customize->is_theme_active(),
			),
			'url'      => array(
				'preview'       => esc_url( $url ? $url : home_url( '/' ) ),
				'parent'        => esc_url( admin_url() ),
				'activated'     => admin_url( 'themes.php?activated=true&previewed' ),
				'ajax'          => esc_url( admin_url( 'admin-ajax.php', 'relative' ) ),
				'allowed'       => array_map( 'esc_url', $allowed_urls ),
				'isCrossDomain' => $cross_domain,
				'fallback'      => $fallback_url,
				'home'          => esc_url( home_url( '/' ) ),
				'login'         => $login_url,
			),
			'browser'  => array(
				'mobile' => wp_is_mobile(),
				'ios'    => $is_ios,
			),
			'settings' => array(),
			'controls' => array(),
			'nonce'    => array(
				'save'    => wp_create_nonce( 'save-customize_' . $wp_customize->get_stylesheet() ),
				'preview' => wp_create_nonce( 'preview-customize_' . $wp_customize->get_stylesheet() )
			),
		);

		foreach ( $wp_customize->settings() as $id => $setting ) {
			$settings['settings'][ $id ] = array(
				'value'     => $setting->js_value(),
				'transport' => $setting->transport,
			);
		}

		foreach ( $wp_customize->controls() as $id => $control ) {
			$control->to_json();
			$settings['controls'][ $id ] = $control->json;
		}

		?>
		<script type="text/javascript">
			var _wpCustomizeSettings = <?php echo json_encode( $settings ); ?>;
		</script>
		<?php
	}

	/**
	 * Remove and define new Theme Customizer sections, settings, and controls
	 */
	public function action_customize_register( $wp_customize ) {

		// Remove all existing sections
		foreach( $wp_customize->sections() as $id => $section ) {
			$wp_customize->remove_section( $id );
		}

		// Remove all existing controls
		foreach( $wp_customize->controls() as $id => $control ) {
			$wp_customize->remove_control( $id );
		}

		// Remove all existing settings
		foreach( $wp_customize->settings() as $id => $setting ) {
			$wp_customize->remove_setting( $id );
		}

		/** COLORS **/
		$wp_customize->add_section( 'wp_present_colors', array(
			'title'   => __( 'Colors', 'wp-present' ),
			'priority'  => 10,
			'capability' => 'read',
		) );

		// Background color
		$wp_customize->add_setting( 'wp_present_background_color', array(
			'default' => '',
		  	'sanitize_callback' => 'sanitize_hex_color',
		  	'transport' => 'postMessage',
		) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'wp_present_background_color', array(
			'label'   => __( 'Background Color', 'wp-present' ),
			'section' => 'wp_present_colors',
			'settings'  => 'wp_present_background_color',
		) ) );

		// Text Color
		$wp_customize->add_setting( 'wp_present_text_color', array(
			'default' => '',
			'sanitize_callback' => 'sanitize_hex_color',
			'transport' => 'postMessage',
		) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'wp_present_text_color', array(
			'label'   => __( 'Text Color', 'wp-present' ),
			'section' => 'wp_present_colors',
			'settings'  => 'wp_present_text_color',
		) ) );

		// Accent Text Color
		$wp_customize->add_setting( 'wp_present_link_color', array(
			'default' => '',
			'sanitize_callback' => 'sanitize_hex_color',
			'transport' => 'postMessage',
		) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'wp_present_link_color', array(
			'label'   => __( 'Link Color', 'wp-present' ),
			'section' => 'wp_present_colors',
			'settings'  => 'wp_present_link_color',
		) ) );

		/** BACKGROUND **/
		$wp_customize->add_section( 'wp_present_background', array(
			'title'   => __( 'Background', 'wp-present' ),
			'priority'  => 20,
			'capability' => 'read',
		) );

		// Background Image
		$wp_customize->add_setting( 'wp_present_background_image' , array(
			'transport' => 'postMessage',
			'default' => '',
		) );

		$wp_customize->add_control( new WP_Present_Background_Image_Control( $wp_customize, 'wp_present_background_image', array(
			'label'   => __( 'Background Image', 'wp-present' ),
			'section' => 'wp_present_background',
			'settings'  => 'wp_present_background_image',
			'context'   => 'wp-present_' . $_REQUEST['tag_ID']
		) ) );

		/** EFFECTS **/
		/*
		$wp_customize->add_section( 'wp_present_effects', array(
			'title'   => __( 'Effects', 'wp-present' ),
			'priority'  => 30,
			'capability' => 'read',
		) );

		// Transition
		$wp_customize->add_setting( 'wp_present_transition' , array(
			'transport' => 'postMessage',
			'default' => ''
		) );

		$wp_customize->add_control( 'wp_present_transition', array(
			'label'   => 'Transition',
			'section' => 'wp_present_effects',
			'type'    => 'select',
			'choices'    => array(
				'default' => 'Default',
				'cube' => 'Cube',
				'page' => 'Page',
				'concave' => 'Concave',
				'zoom' => 'Zoom',
				'linear' => 'Linear',
				'fade' => 'Fade',
				'none' => '-none-',
			),
		) );
		*/

		/** SPEAKER NOTES **/
		/*
		$wp_customize->add_section( 'wp_present_speaker_notes', array(
			'title'   => __( 'Speaker Notes', 'wp-present' ),
			'priority'  => 40,
			'capability' => 'read',
		) );

		// Aside
		$wp_customize->add_setting( 'wp_present_aside' , array(
			'transport' => 'postMessage',
			'default' => ''
		) );

		$wp_customize->add_control( 'wp_present_aside', array(
			'label'   => 'Aside',
			'section' => 'wp_present_speaker_notes',
			'type'    => 'text',
		) );
		*/

	}

} // Class
WP_Present_Modal_Customizer::instance();
