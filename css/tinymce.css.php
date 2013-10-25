<?php
header( 'Content-type: text/css' );

function wpppresent_associate_mce_background_image( $post_id ) {
	global $post;

	//echo get_post_thumbnail_id( $post_id );
	//echo $background_image_url = wp_get_attachment_url( get_post_thumbnail_id( $post_id ) );

	//if( ! isset( $background_image_url ) && empty( $background_image_url ) )
	//	return false;
	//echo '.mceContentBody.reveal { background: url("' . esc_url( $background_image_url ) . '"); background-size: cover; }';
}
wpppresent_associate_mce_background_image( $_REQUEST[ 'post' ] );
