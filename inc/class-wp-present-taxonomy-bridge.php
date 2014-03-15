<?php
/**
 ** WP Present Taxonomy Meta Hack
 **
 ** @since 0.9.6
 **/
class WP_Present_Taxonomy_Bridge {

	const REVISION = 20140201;

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

		add_action( 'edited_' . WP_Present_Core::TAXONOMY_SLUG, array( $this, 'action_edited_taxonomy' ), 10, 2 );

		// Edit Link
		add_filter( 'get_edit_post_link', array( $this, 'filter_get_edit_post_link' ), 10, 3 );

		// Insert Post
		add_action( 'save_post', array( $this, 'action_save_post' ) );
	}

	/**
	 * Updated the related post to each presentation taxonomy
	 *
	 * @todo object cache this method
	 * @return null
	 */
	public function action_edited_taxonomy( $term_id, $tt_id ) {
		// @TODO: See if this is actually ever defined here
		if( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ) {
			return;
		}

		$term = get_term( $term_id, WP_Present_Core::TAXONOMY_SLUG );
		if( ! is_object( $term ) || is_wp_error( $term ) ) {
			return;
		}

		$tax_query = new WP_Query( array(
			'post_type' => WP_Present_Core::POST_TYPE_TAXONOMY,
			'tax_query' => array( array(
				'taxonomy' => WP_Present_Core::TAXONOMY_SLUG,
				'field' => 'id',
				'terms' => $term_id
			) )
		) );

		if( $tax_query->have_posts() ) {
			while ( $tax_query->have_posts() ) {
				$tax_query->the_post();
				$post_id = get_the_ID();
			}
		} else {
			// Insert the post into the database
			$post_id = wp_insert_post( array(
				'post_title'     => $term->name,
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => WP_Present_Core::POST_TYPE_TAXONOMY,
				'comment_status' => 'open'
			) );
		}
		$terms = wp_set_object_terms( $post_id, $term_id, WP_Present_Core::TAXONOMY_SLUG, false );
		if( is_array( $terms ) && ! is_wp_error( $terms ) ) {
			// Backup the term desicription
			add_post_meta( $post_id, WP_Present_Core::METAKEY_PREFIX . 'slide_order', $term->description, true );
		}
	}

	/**
	 * Redirect the post type edit links to the edit term links
	 *
	 * @uses get_the_terms, get_edit_term_link
	 * @since 0.9.6
	 * @todo object cache this method
	 * @return string term link
	 */
	public function filter_get_edit_post_link( $link, $post_id, $context ) {

		global $pagenow;
		if( 'edit-tags.php' == $pagenow && ( isset( $_GET['taxonomy'] ) && WP_Present_Core::TAXONOMY_SLUG == $_GET['taxonomy'] ) ) {
			return $link;
		};

		$terms = get_the_terms( $post_id, WP_Present_Core::TAXONOMY_SLUG );
		if( ! $terms || is_wp_error( $terms ) || count( $terms ) < 1 ) {
			return $link;
		}
		$terms = array_values( $terms );
		$term = $terms[0];
		$term_link = get_edit_term_link( $term, WP_Present_Core::TAXONOMY_SLUG );

		return $term_link;
	}

	/**
	 * Associate a taxonomy with new presentation post types
	 *
	 * @uses get_the_terms, wp_insert_term
	 * @since 0.9.6
	 * @return null
	 */
	function action_save_post( $post_id ) {
		global $post;
		$post = get_post( $post_id );

		// Reasons to bail
		if( $post->post_status != 'publish' ) {
			flush_rewrite_rules();
			wp_reset_postdata();
			return;
		}
		if( WP_Present_Core::POST_TYPE_TAXONOMY != $post->post_type || wp_is_post_revision( $post_id ) ) {
			flush_rewrite_rules();
			wp_reset_postdata();
			return;
		}

		$terms = get_the_terms( $post_id, WP_Present_Core::TAXONOMY_SLUG );
		if( ! $terms || is_wp_error( $terms ) || (int) count( $terms ) < 1 ) {
			//die('no tax found');
			$term_id = wp_insert_term(
				$post->post_title, // the term
				WP_Present_Core::TAXONOMY_SLUG, // the taxonomy
				array(
					'description'=> '',
					'slug' => $post->post_name,
				)
			);
			$terms = wp_set_object_terms( $post_id, $term_id, WP_Present_Core::TAXONOMY_SLUG, false );
		}
		flush_rewrite_rules();
		wp_reset_postdata();
	}


} // Class
WP_Present_Taxonomy_Bridge::instance();
