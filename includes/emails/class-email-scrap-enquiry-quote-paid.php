<?php
/**
 * Email class file for when customer clicks Accept link
 *
 * @package Scrap_Enquiry
 */

namespace MJE\ScrapEnquiry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class
 */
class Email_Scrap_Enquiry_Quote_Paid extends \WC_Email {

	/**
	 * Constructor
	 */
	public function __construct() {

		$this->id          = 'scrap_enquiry_paid';
		$this->title       = __( 'Scrap Enquiry - Quote Paid', 'scrap-enquiry' );
		$this->description = __( 'Notification sent to the customer when the quoted price has been paid.', 'scrap-enquiry' );
		$this->heading     = __( 'Scrap Enquiry Quote Paid', 'scrap-enquiry' );
		$this->subject     = __( 'Scrap Enquiry Quote Paid', 'scrap-enquiry' );
		$this->manual      = false;
		$this->email_type  = 'html';

		// Call parent constructor to load any other defaults not explicity defined here.
		parent::__construct();

		$this->settings['email_type'] = 'html';

		if ( '' === $this->settings['subject'] ) {
			$this->settings['subject'] = $this->subject;
		}

		if ( '' === $this->settings['enabled'] ) {
			$this->settings['enabled'] = 'yes';
		}

		$this->customer_email = true;

		add_action( 'scrap_enquiry_quote_paid', array( $this, 'trigger' ), 10, 2 );
	}

	/**
	 * Settings form field definitions
	 *
	 * @return void
	 */
	public function init_form_fields() {

		$this->form_fields = array(
			'enabled'    => array(
				'title'   => __( 'Enable', 'scrap-enquiry' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable this email notification', 'scrap-enquiry' ),
				'default' => 'yes',
			),
			'subject'    => array(
				'title'       => __( 'Email Subject', 'scrap-enquiry' ),
				'type'        => 'text',
				'description' => sprintf( 'This controls the email subject line. Use <code>{enquiry_id}</code> to insert the enquiry ID number. Leave blank to use the default subject: <code>%s</code>.', $this->subject ),
				'placeholder' => __( 'Scrap enquiry quote paid: {enquiry_id}', 'scrap-enquiry' ),
				'default'     => $this->subject,
			),
			'heading'    => array(
				'title'       => __( 'Email Heading', 'scrap-enquiry' ),
				'type'        => 'text',
				/* translators: %s: Message heading */
				'description' => sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.', 'scrap-enquiry' ), $this->heading ),
				'placeholder' => __( 'Scrap enquiry quote paid', 'scrap-enquiry' ),
				'default'     => $this->heading,
			),
			'message'    => array(
				'title'       => __( 'Email Message', 'scrap-enquiry' ),
				'type'        => 'textarea',
				'description' => __( 'The message body. Use <code>{scrap_details}</code> to insert the details.', 'scrap-enquiry' ),
				'placeholder' => '',
				'default'     => __( 'Quote paid.', 'scrap-enquiry' ) . "\n\n" . '{scrap_details}' . "\n",
			),
			// This is here to force the type to HTML, it's a hidden setting field.
			'email_type' => array(
				'title'       => '',
				'type'        => 'hidden',
				'description' => '',
				'default'     => 'html',
				'class'       => 'email_type',
			),
		);
	}

	/**
	 * Get the enquiry details and send the email
	 *
	 * @param int    $scrap_id Post ID for the scrap enquiry.
	 * @param string $customer_email Customer email address.
	 * @return void
	 */
	public function trigger( $scrap_id, $customer_email ) {
		if ( ! $this->is_enabled() ) {
			return;
		}

		// Get scrap details.
		$scrap_details = get_scrap_details( $scrap_id );

		// Replace any placeholder in the message body.
		$message = $this->get_message();
		$message = str_replace( '{scrap_details}', $scrap_details, $message );
		$message = str_replace( "\n", '<br/>', $message );

		$subject = $this->get_subject();
		$subject = str_replace( '{enquiry_id}', $scrap_id, $subject );

		// Send email.
		$this->setup_and_send( $customer_email, $message, $subject );
	}

	/**
	 * Send the message
	 *
	 * @param string $to Send to this address.
	 * @param string $message Message body.
	 * @param string $subject Subject text.
	 * @return void
	 */
	public function setup_and_send( $to, $message, $subject ) {
		$message = WC()->mailer->wrap_message( $this->get_heading(), $message );
		$this->send( $to, $subject, $message, $this->get_headers(), $this->get_attachments() );
	}

	/**
	 * Get email headers.
	 *
	 * @return string
	 */
	public function get_headers() {
		$header      = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$url         = get_site_url();
		$domain_name = wp_parse_url( $url, PHP_URL_HOST );
		$header     .= 'Reply-to: not-monitored@' . $domain_name . "\r\n";
		return $header;
	}

	/**
	 * Get message
	 *
	 * @return string
	 */
	public function get_message() {
		return $this->settings['message'];
	}

	/**
	 * Get message subject
	 *
	 * @return string
	 */
	public function get_subject() {
		if ( ! empty( $this->settings['subject'] ) ) {
			return $this->settings['subject'];
		} else {
			return $this->get_default_subject();
		}
	}

	/**
	 * Get message heading
	 *
	 * @return string
	 */
	public function get_heading() {
		if ( ! empty( $this->settings['heading'] ) ) {
			return $this->settings['heading'];
		} else {
			return $this->get_default_heading();
		}
	}

	/**
	 * Get default subject
	 *
	 * @return string
	 */
	public function get_default_subject() {
		return $this->subject;
	}

	/**
	 * Get default message heading
	 *
	 * @return string
	 */
	public function get_default_heading() {
		return $this->heading;
	}
}
