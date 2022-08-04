<?php
/**
 * My Account class file
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
class My_Account {

	/**
	 * Add hooks
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'add_endpoint' ) );
		add_action( 'woocommerce_account_menu_items', array( __CLASS__, 'menu_items' ) );
		add_action( 'woocommerce_account_scrap-enquiries_endpoint', array( __CLASS__, 'list_enquiries' ) );
	}

	/**
	 * Add new endpoint to My Account page
	 *
	 * @return void
	 */
	public static function add_endpoint() {
		add_rewrite_endpoint( 'scrap-enquiries', EP_ROOT | EP_PAGES );
	}

	/**
	 * Add menu item
	 *
	 * @param array $items Menu items.
	 * @return array
	 */
	public static function menu_items( $items ) {
		// Save logout menu item.
		$logout = $items['customer-logout'];
		// Remove logout menu item.
		unset( $items['customer-logout'] );
		// Add the new menu item to the end of the menu list.
		$items['scrap-enquiries'] = __( 'Scrap enquiries', 'scrap-enquiry' );
		// Add logout URL back to the end of the menu list.
		$items['customer-logout'] = $logout;
		return $items;
	}

	/**
	 * Show a list of enquiries for the logged in user
	 *
	 * URL example: http://localhost.example/my-account/scrap-enquiries/
	 * URL exaplne: http://localhost.example/my-account/scrap-enquiries/123/
	 * In the second example "123" is the scrap post ID, which populates into a query var named "scrap-enquiries".
	 *
	 * @return void
	 */
	public static function list_enquiries() {
		if ( ! is_user_logged_in() ) {
			esc_html_e( 'You must be logged to do this.', 'scrap-enquiry' );
			return;
		}

		$id = get_query_var( 'scrap-enquiries' );

		// If there is no ID then show a list of all enquiries for the logged in user.
		if ( empty( $id ) ) {
			// Get current user ID.
			$id = get_current_user_id();

			// List each enquiry with a link to details.
			$enquiries = \MJE\ScrapEnquiry\get_enquiries( $id );

			wc_get_template(
				'scrap-enquiry-list.php',
				array(
					'enquiries' => $enquiries,
				),
				'',
				SCRAP_INQUIRY_PLUGIN_DIR . 'templates/'
			);
		} else {
			// Show an individual enquiry's details.
			echo '<h3> ' . esc_html( __( 'Enquiry #', 'scrap-enquiry' ) ) . $id . '</h3>'; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$status = get_post_status( $id );
			echo '<b>' . esc_html( __( 'Status:', 'scrap-enquiry' ) ) . '</b> ';
			echo esc_html( get_status_label( $status ) );
			// The following output already escaped.
			echo get_scrap_details( $id ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}
}

\MJE\ScrapEnquiry\My_Account::init();
