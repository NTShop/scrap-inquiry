<?php
/**
 * Email class file, loads email classes
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
class EMails {

	/**
	 * Add hooks
	 *
	 * @return void
	 */
	public static function init() {
		add_filter( 'woocommerce_email_classes', array( __CLASS__, 'add_emails' ) );
	}

	/**
	 * Add email classes into WooCommerce
	 *
	 * @param array $email_classes Array of email class names.
	 * @return array
	 */
	public static function add_emails( $email_classes ) {

		require dirname( __FILE__ ) . '/emails/class-email-scrap-enquiry-new.php';
		require dirname( __FILE__ ) . '/emails/class-email-scrap-enquiry-accepted.php';
		require dirname( __FILE__ ) . '/emails/class-email-scrap-enquiry-rejected.php';
		require dirname( __FILE__ ) . '/emails/class-email-scrap-enquiry-quote-provided.php';
		require dirname( __FILE__ ) . '/emails/class-email-scrap-enquiry-quote-paid.php';

		$email_classes['Email_Scrap_Enquiry_New']            = new Email_Scrap_Enquiry_New();
		$email_classes['Email_Scrap_Enquiry_Accepted']       = new Email_Scrap_Enquiry_Accepted();
		$email_classes['Email_Scrap_Enquiry_Rejected']       = new Email_Scrap_Enquiry_Rejected();
		$email_classes['Email_Scrap_Enquiry_Quote_Provided'] = new Email_Scrap_Enquiry_Quote_Provided();
		$email_classes['Email_Scrap_Enquiry_Quote_Paid']     = new Email_Scrap_Enquiry_Quote_Paid();

		return $email_classes;
	}
}

\MJE\ScrapEnquiry\EMails::init();
