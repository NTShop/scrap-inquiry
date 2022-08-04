<?php
/**
 * Email click handler class file
 *
 * This class handles clicks on links inserted into HTML email messages sent by this plugin.
 *
 * @package Scrap_Enquiry
 */

namespace MJE\ScrapEnquiry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class file
 */
class Email_Click_Handler {

	/**
	 * Add hooks
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'add_endpoints' ), 10 );
		add_action( 'wp', array( __CLASS__, 'maybe_handle_endpoint' ), 11 );
	}

	/**
	 * Add new endpoints
	 *
	 * @return void
	 */
	public static function add_endpoints() {
		add_rewrite_endpoint( 'scrap-enquiry-accepted', EP_ROOT | EP_PERMALINK | EP_PAGES );
		add_rewrite_endpoint( 'scrap-enquiry-rejected', EP_ROOT | EP_PERMALINK | EP_PAGES );
	}

	/**
	 * Check if one of the custom endpoints has been clicked and if so try to process it
	 *
	 * @return void
	 */
	public static function maybe_handle_endpoint() {
		$accepted = get_query_var( 'scrap-enquiry-accepted' );

		// If there is no 'scrap-enquiry-accepted' var the check for the 'scrap-enquiry-rejected' var.
		if ( empty( $accepted ) ) {
			$rejected = get_query_var( 'scrap-enquiry-rejected' );
		}

		if ( empty( $accepted ) && empty( $rejected ) ) {
			return;
		}

		if ( ! empty( $accepted ) ) {
			// handle offer acceptance email.
			self::handle_offer_acceptance( $accepted );
			wp_safe_redirect( get_permalink( get_page_by_path( 'offer-invalid' ) ) );
			exit;
		} elseif ( ! empty( $rejected ) ) {
			// handle offer rejection email.
			self::handle_offer_rejection( $rejected );
			wp_safe_redirect( get_permalink( get_page_by_path( 'offer-invalid' ) ) );
			exit;
		}
	}

	/**
	 * Process offer acceptance
	 *
	 * Updates the post status and sends email if conditions are correct.
	 *
	 * @param string $accepted Query var from the URL.
	 * @return void
	 */
	public static function handle_offer_acceptance( $accepted ) {
		$offer_id = self::parse_query_var( $accepted );

		if ( empty( $offer_id ) ) {
			// Redirect to "Offer invalid" page.
			wp_safe_redirect( get_permalink( get_page_by_path( 'offer-invalid' ) ) );
			exit;
		}

		$status = get_post_status( $offer_id );

		// Return if link click already processed.
		if ( 'si-provided' !== $status ) {
			return;
		}

		// Update post status.
		$args = array(
			'ID'          => $offer_id,
			'post_status' => 'si-accepted',
		);

		wp_update_post( $args );

		// Make sure WC emails are loaded and send related email.
		WC()->mailer();
		do_action( 'scrap_enquiry_accepted', $offer_id );

		// Redirect to Offer Accepted page.
		wp_safe_redirect( get_permalink( get_page_by_path( 'offer-accepted' ) ) . '?offer_id=' . $accepted );
		exit;
	}

	/**
	 * Process offer rejection
	 *
	 * Updates the post status and sends email if conditions are correct.
	 *
	 * @param string $rejected Query var from the URL.
	 * @return void
	 */
	public static function handle_offer_rejection( $rejected ) {
		$offer_id = self::parse_query_var( $rejected );

		if ( empty( $offer_id ) ) {
			// Redirect to "Offer invalid" page.
			wp_safe_redirect( get_permalink( get_page_by_path( 'offer-invalid' ) ) );
		}

		$status = get_post_status( $offer_id );

		// Return if link click already processed.
		if ( 'si-provided' !== $status ) {
			return;
		}

		$args = array(
			'ID'          => $offer_id,
			'post_status' => 'si-rejected',
		);

		wp_update_post( $args );

		// Make sure WC emails are loaded and send related email.
		WC()->mailer();
		do_action( 'scrap_enquiry_rejected', $offer_id );

		// Redirect to Offer Rejected page.
		wp_safe_redirect( get_permalink( get_page_by_path( 'offer-rejected' ) ) . '?offer_id=' . $rejected );
		exit;
	}

	/**
	 * Parse the query var parts and return the related post ID
	 *
	 * The query var should be formated like this: 1234567-888
	 * where 1234567 is the unique key for the enquiry located in postmeta "scrap_enquiry_key"
	 * and 888 is the post ID number of scrap enquiry.
	 *
	 * @param string $var The query variable taken from the URL.
	 * @return false|int
	 */
	public static function parse_query_var( $var ) {
		$parts = explode( '-', $var );

		if ( empty( $parts ) || empty( $parts[0] ) || empty( $parts[1] ) ) {
			return false;
		}

		$post_id = absint( $parts[1] );

		// Ensure that we have a number.
		if ( empty( $post_id ) ) {
			return false;
		}

		// Check for the correct meta key data in the post.
		$key = get_post_meta( $post_id, 'scrap_enquiry_key', true );

		if ( empty( $key ) || $key !== $parts[0] ) {
			return false;
		}

		return $post_id;
	}
}

\MJE\ScrapEnquiry\Email_Click_Handler::init();
