<?php
header( 'Content-type: text/css' );

function wpppresent_associate_mce_background_image( $post_id ) {

	global $post;

	var_dump( $post_id );

//	$post = get_post( (int) $post_id );
//	setup_postdata( $post );

	var_dump( $post );
echo 'test';
	$thumbnail_id = get_post_thumbnail_id( (int) $post_id );

	var_dump( $thumbnail_id );


	echo $background_image_url = wp_get_attachment_url( $thumbnail_id );

	//if( ! isset( $background_image_url ) && empty( $background_image_url ) )
	//	return false;
	//echo '.mceContentBody.reveal { background: url("' . esc_url( $background_image_url ) . '"); background-size: cover; }';


	die();
}
wpppresent_associate_mce_background_image( $_REQUEST[ 'post' ] );
