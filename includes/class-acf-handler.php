<?php
/**
 * ACF processor class file
 *
 * This class handles intercepting updates to an Advance Custom Fields setting "date_today"
 * to determine if enquiries must be updated, which must happen when the "date_today" setting changes.
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
class ACF_Handler {

	/**
	 * Private flag
	 *
	 * @var boolean
	 */
	private static $process_enquiries = false;

	/**
	 * Loads class files required for the plugin operation
	 *
	 * @return void
	 */
	public static function init() {
		add_filter( 'acf/update_value/name=date_today', array( __CLASS__, 'maybe_intercept_acf_update_value' ), 5, 4 );
		add_filter( 'acf/save_post', array( __CLASS__, 'process_enquiries' ), 10 );
	}

	/**
	 * Check if the ACF field being updated is "date_today" and compare current setting to new settings
	 *
	 * This function runs when the field value is being updated, before it is updated into the database.
	 *
	 * @param string $value Value being set.
	 * @param int    $post_id Post ID.
	 * @param array  $field Array of field settings.
	 * @param array  $original Original value.
	 * @return string $value New value being set.
	 */
	public static function maybe_intercept_acf_update_value( $value, $post_id, $field, $original ) {
		if ( empty( $value ) ) {
			return $value;
		}

		// Date before being updated.
		$date = get_field( 'date_today', 'option' );
		$date = gmdate( 'Ymd', strtotime( $date ) );

		// $value is new date being set
		if ( $date !== $value ) {
			self::$process_enquiries = true;
		}

		return $value;
	}

	/**
	 * After ACF saves the options then process enquiries if the flag is set.
	 *
	 * @return void
	 */
	public static function process_enquiries() {
		if ( ! self::$process_enquiries ) {
			return;
		}

		// Get all enquiries with status of 'si-provided'.
		$args = array(
			'numberposts' => -1, // -1 is for all posts.
			'post_type'   => 'scrap_inquiry', // The custom post type slug.
			'orderby'     => 'date',
			'order'       => 'DESC',
			'post_status' => array( 'si-provided' ), // Get posts with this status.
			'post_author' => $user_id,
		);

		$enquiries = get_posts( $args );

		if ( empty( $enquiries ) ) {
			return;
		}

		// Update quote values.
		foreach ( $enquiries as $enquiry ) {

			$email_to = get_post_meta( $enquiry->ID, 'email', true );

			if ( empty( $email_to ) ) {
				continue;
			}

			$items = get_post_meta( $enquiry->ID, 'items', true );

			if ( empty( $items ) ) {
				continue;
			}

			$new_items   = array();
			$total_value = 0;

			foreach ( $items as $item ) {

				if ( empty( $item['name'] ) || empty( $item['weight'] ) ) {
					continue;
				}

				// Find the correct spot price.
				if ( false !== strpos( $item['name'], 'Gold' ) ) {
					$spot_price = scrap_calculator( $item['name'] );
				} elseif ( false !== strpos( $item['name'], 'Silver' ) ) {
					$spot_price = scrap_calculator( $item['name'] );
				} elseif ( false !== strpos( $item['name'], 'Platinum' ) ) {
					$spot_price = scrap_calculator( $item['name'] );
				} elseif ( false !== strpos( $item['name'], 'Palladium' ) ) {
					$spot_price = scrap_calculator( $item['name'] );
				} elseif ( false !== strpos( $item['name'], 'Krugerrand' ) ) {
					$spot_price = krugerrand_calculator( $item['name'] );
				} elseif ( false !== strpos( $item['name'], 'Sovereigns' ) ) {
					$spot_price = sovereigns_calculator( $item['name'] );
				} elseif ( false !== strpos( $item['name'], 'Gold Bar' ) ) {
					$spot_price = gold_bar_24ct( $item['name'] );
				}

				if ( empty( $spot_price ) || floatval( $spot_price ) <= 0 ) {
					continue;
				}

				// Calculate value.
				$item['value'] = round( $item['weight'] * $spot_price, 2 );
				$total_value  += $item['value'];
				$new_items[]   = $item;
			}

			if ( ! empty( $new_items ) ) {
				update_post_meta( $enquiry->ID, 'items', $new_items );
				update_post_meta( $enquiry->ID, 'scrap_value_total', round( $total_value, 2 ) );
			}

			// Trigger new email to customer.
			do_action( 'scrap_enquiry_quote_provided', $enquiry->ID, $email_to );
		}

	}
}

\MJE\ScrapEnquiry\ACF_Handler::init();
