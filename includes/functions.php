<?php
/**
 * Functions file
 *
 * @package Scrap_Enquiry
 */

namespace MJE\ScrapEnquiry;

/**
 * Creates a scrap enquiry post and sends email to the admin
 *
 * @param array  $args Array of data.
 * @param int    $user_id User ID.
 * @param string $attachments URL of image uploaded to wp-content/uploads.
 * @return int|false
 */
function create_scrap_enquiry( $args = array(), $user_id = false, $attachments = false ) {

	/**
	 * Define args. Note that we use "publish" as the post status, which in turns makes the "Publish" button
	 * have a label of "Update", which is what we want.
	 */

	if ( empty( $user_id ) ) {
		$user_id = 1;
	}

	$new_post_args = array(
		'post_type'    => 'scrap_inquiry',
		'post_title'   => sanitize_text_field( $args['first_name'] ) . ' ' . sanitize_text_field( $args['last_name'] ),
		'post_content' => '',
		'post_status'  => 'si-new',
		'post_author'  => $user_id,
	);

	$id = wp_insert_post( $new_post_args, true, false );

	if ( is_wp_error( $id ) ) {
		return false;
	} else {
		// Add meta data. Note that $key will match Scrap_Enquiry::$expected_fields array
		// because the remote form $_POST data is built that way.
		foreach ( $args as $key => $value ) {
			if ( 'items' === $key ) {
				continue;
			}
			// WordPress uses sanitize_meta() to handle key and value cleaning.
			add_post_meta( $id, $key, $value );
		}

		/**
		 * The $args['sitems'] index should match the indexes for $args['sweights'] and $args['svalues'].
		 */
		foreach ( $args['sitems'] as $index => $value ) {
			$items[] = array(
				'name'   => $value,
				'weight' => sanitize_text_field( $args['sweights'][ $index ] ),
				'value'  => sanitize_text_field( $args['svalues'][ $index ] ),
			);
		}

		add_post_meta( $id, 'items', $items );

		// Maybe insert image URL into the meta.
		if ( ! empty( $attachments ) ) {
			add_post_meta( $id, 'image', $attachments[0] );
		}

		// Add a unique key that can be used in email links for "Accept" or "Reject".
		add_post_meta( $id, 'scrap_enquiry_key', uniqid() );

		// Send new scrap enquiry email.
		do_action( 'new_scrap_enquiry', $id );

		return $id;
	}
}

/**
 * Get enquiries for user
 *
 * @param int $user_id User ID.
 * @return array
 */
function get_enquiries( $user_id ) {
	$args = array(
		'numberposts' => -1, // -1 is for all
		'post_type'   => 'scrap_inquiry',
		'orderby'     => 'date',
		'order'       => 'DESC',
		'post_status' => 'ANY',
		'author'      => $user_id,
	);

	$posts = get_posts( $args );

	return $posts;
}

/**
 * Get the label for the post status
 *
 * @param string $status The status slug.
 * @return string
 */
function get_status_label( $status ) {
	return \MJE\ScrapEnquiry\Post_Statuses::$statuses[ $status ]['label'];
}

/**
 * Get scrap enquiry details
 *
 * @param int $id Scrap post ID.
 * @return string
 */
function get_scrap_details( $id ) {
	$details = '<br/>';

	$meta = get_post_meta( $id );

	unset( $meta['_edit_lock'] );

	foreach ( $meta as $key => $value ) {
		if ( 'items' === $key || 'image' === $key || 'cheque_or_bacs' === $key ) {
			continue;
		}

		if ( ! in_array( $key, Scrap_Enquiry::$expected_fields, true ) ) {
			continue;
		}

		// Omit empty fields, do not show.
		if ( empty( $value[0] ) ) {
			continue;
		}

		if ( 'user_message_details' === $key ) {
			$values[0] = wpautop( $values[0] );
		}

		$details .= '<b>' . esc_html( ucwords( str_replace( '_', ' ', $key ) ) ) . ':</b> ';
		$details .= esc_html( $value[0] ) . '<br/>';
	}

	$meta['items'] = maybe_unserialize( $meta['items'][0] );

	$sum = 0;

	$details .= '<b>' . __( 'Scrap', 'scrap-enquiry' ) . '</b>:<br/>';

	if ( ! empty( $meta['items'] ) ) {
		foreach ( $meta['items'] as $key => $data ) {
			$sum     += floatval( $data['value'] );
			$details .= __( ' Weight/Quantity:', 'scrap-enquiry' ) . ' ' . esc_html( $data['weight'] ) . ', '
			. __( 'Metal:', 'scrap-enquiry' ) . ' ' . esc_html( $data['name'] ) . ', '
			. __( 'Value:', 'scrap-enquiry' ) . ' ' . esc_html( $data['value'] ) . '<br/>';
		}
	}

	$details .= '<b>' . __( 'Total value:', 'scrap-enquiry' ) . '</b> ' . round( $sum, 2 ) . '<br/>';

	return $details;
}
