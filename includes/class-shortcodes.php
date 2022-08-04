<?php
/**
 * Shortcodes class file
 *
 * @package Scrap_Enquiry
 */

namespace MJE\ScrapEnquiry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main class
 */
class Shortcodes {

	/**
	 * Add shortcodes
	 *
	 * @return void
	 */
	public static function init() {
		add_shortcode( 'offer_accepted', array( __CLASS__, 'offer_accepted' ) );
		add_shortcode( 'offer_rejected', array( __CLASS__, 'offer_rejected' ) );
	}

	/**
	 * Offer accepted shortcode function
	 *
	 * @param array $args Array of parameters, should always be empty.
	 * @return string The amount being offered to the customer.
	 */
	public static function offer_accepted( $args ) {

		if ( empty( $_GET['offer_id'] ) ) { // phpcs:ignore
			return __( '[THE LINK YOU USED IS INVALID]', 'scrap-enquiry' );
		}

		$offer_param = wp_unslash( $_GET['offer_id'] ); // phpcs:ignore

		// Parse the parts.
		$parts = explode( '-', $offer_param );

		if ( empty( $parts[0] ) || empty( $parts[1] ) ) {
			return __( '[THE LINK YOU USED IS INVALID]', 'scrap-enquiry' );
		}

		$offer_id  = intval( $parts[1] );
		$offer_key = trim( $parts[0] );

		if ( empty( $offer_id ) || empty( $offer_key ) ) {
			return __( '[THE LINK YOU USED IS INVALID]', 'scrap-enquiry' );
		}

		// Get the key postmeta and compare to what is received in the GET request var.
		$key = get_post_meta( $offer_id, 'scrap_enquiry_key', true );

		if ( $key !== $offer_key ) {
			return __( '[THE LINK YOU USED IS INVALID]', 'scrap-enquiry' );
		}

		// Get offer total and present a message.
		$offer_total = get_post_meta( $offer_id, 'scrap_value_total', true );

		return $offer_total;
	}

	/**
	 * Offer rejected shortcode function
	 *
	 * @param array $args Array of parameters, should always be empty.
	 * @return string The amount being offered to the customer.
	 */
	public static function offer_rejected( $args ) {
		if ( empty( $_GET['offer_id'] ) ) { // phpcs:ignore
			return __( '[THE LINK YOU USED IS INVALID]', 'scrap-enquiry' );
		}

		$offer_param = wp_unslash( $_GET['offer_id'] ); // phpcs:ignore

		// Parse the parts.
		$parts = explode( '-', $offer_param );

		if ( empty( $parts[0] ) || empty( $parts[1] ) ) {
			return __( '[THE LINK YOU USED IS INVALID]', 'scrap-enquiry' );
		}

		$offer_id  = intval( $parts[1] );
		$offer_key = trim( $parts[0] );

		if ( empty( $offer_id ) || empty( $offer_key ) ) {
			return __( '[THE LINK YOU USED IS INVALID]', 'scrap-enquiry' );
		}

		// Get the key postmeta and compare to what is received in the GET request var.
		$key = get_post_meta( $offer_id, 'scrap_enquiry_key', true );

		if ( $key !== $offer_key ) {
			return __( '[THE LINK YOU USED IS INVALID]', 'scrap-enquiry' );
		}

		// Get offer total and present a message.
		$offer_total = get_post_meta( $offer_id, 'scrap_value_total', true );

		return $offer_total;
	}
}

\MJE\ScrapEnquiry\Shortcodes::init();
