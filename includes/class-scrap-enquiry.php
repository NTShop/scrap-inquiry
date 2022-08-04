<?php
/**
 * Main class file
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
class Scrap_Enquiry {

	/**
	 * Array of fields used to store and display postmeta data.
	 *
	 * @var array
	 */
	public static $expected_fields = array(
		'first_name',
		'last_name',
		'phone',
		'phone2',
		'billing_address_1',
		'billing_address_2',
		'billing_city',
		'billing_postcode',
		'email',
		'company_name',
		'customer_acct_number',
		'vat_number',
		'make_payable_to',
		'account_name',
		'bank_name',
		'account_number',
		'sort_code',
		'user_message_details',
		'items',
		'image',
	);

	/**
	 * Adds hook and loads class files required for the plugin operation
	 *
	 * @return void
	 */
	public static function init() {
		// Add language translation hook.
		add_action( 'init', array( __CLASS__, 'load_plugin_textdomain' ) );

		// Load class files.
		require_once dirname( __FILE__ ) . '/class-post-type.php';
		require_once dirname( __FILE__ ) . '/class-post-statuses.php';
		require_once dirname( __FILE__ ) . '/class-emails.php';
		require_once dirname( __FILE__ ) . '/class-email-click-handler.php';
		require_once dirname( __FILE__ ) . '/class-shortcodes.php';
		require_once dirname( __FILE__ ) . '/class-acf-handler.php';

		// Load functions.
		require_once dirname( __FILE__ ) . '/functions.php';

		// Load my account page class if not in the WP admin area.
		if ( ! is_admin() ) {
			require_once dirname( __FILE__ ) . '/class-my-account.php';
		}
	}

	/**
	 * Load language translation
	 *
	 * The first language file found is loaded.
	 *
	 * @return void
	 */
	public static function load_plugin_textdomain() {

		$locale = apply_filters( 'plugin_locale', get_locale(), 'scrap-enquiry' );
		load_textdomain( 'scrap-enquiry', WP_LANG_DIR . '/woocommerce/scrap-enquiry-' . $locale . '.mo' );

		$plugin_rel_path = apply_filters( 'scrap_enquiry_translation_file_rel_path', dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		load_plugin_textdomain( 'scrap-enquiry', false, $plugin_rel_path );
	}
}
