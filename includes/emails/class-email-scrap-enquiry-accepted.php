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
class Email_Scrap_Enquiry_Accepted extends \WC_Email {

	/**
	 * Constructor
	 */
	public function __construct() {

		$this->id          = 'scrap_enquiry_accepted';
		$this->title       = __( 'Scrap Enquiry - Accepted', 'scrap-enquiry' );
		$this->description = __( 'Notification sent to admin is scrap quote is accepted by the customer', 'scrap-enquiry' );
		$this->heading     = __( 'Scrap Enquiry Accepted', 'scrap-enquiry' );
		$this->subject     = __( 'Scrap Enquiry Accepted', 'scrap-enquiry' );
		$this->manual      = false;
		$this->email_type  = 'html';
		$this->scrap_id    = false;

		// Call parent constructor to load any other defaults not explicity defined here.
		parent::__construct();

		$this->settings['email_type'] = 'html';

		if ( '' === $this->settings['subject'] ) {
			$this->settings['subject'] = $this->subject;
		}

		if ( '' === $this->settings['enabled'] ) {
			$this->settings['enabled'] = 'yes';
		}

		$this->recipient = get_option( 'admin_email' );

		add_action( 'scrap_enquiry_accepted', array( $this, 'trigger' ), 10, 1 );
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
			'recipient'  => array(
				'title'       => __( 'Recipient(s)', 'scrap-enquiry' ),
				'type'        => 'text',
				/* translators: %s: WP admin email */
				'description' => sprintf( __( 'Enter recipients (comma separated) for this email. Defaults to %s.', 'scrap-enquiry' ), '<code>' . esc_attr( get_option( 'admin_email' ) ) . '</code>' ),
				'placeholder' => '',
				'default'     => get_option( 'admin_email' ),
				'desc_tip'    => true,
			),
			'subject'    => array(
				'title'       => __( 'Email Subject', 'scrap-enquiry' ),
				'type'        => 'text',
				'description' => sprintf( 'This controls the email subject line. Use <code>{enquiry_id}</code> to insert the enquiry ID number. Leave blank to use the default subject: <code>%s</code>.', $this->subject ),
				'placeholder' => __( 'Scrap enquiry accepted: {enquiry_id}', 'scrap-enquiry' ),
				'default'     => $this->subject,
			),
			'heading'    => array(
				'title'       => __( 'Email Heading', 'scrap-enquiry' ),
				'type'        => 'text',
				/* translators: %s: Message heading */
				'description' => sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.', 'scrap-enquiry' ), $this->heading ),
				'placeholder' => __( 'Scrap enquiry accepted', 'scrap-enquiry' ),
				'default'     => $this->heading,
			),
			'message'    => array(
				'title'       => __( 'Email Message', 'scrap-enquiry' ),
				'type'        => 'textarea',
				'description' => __( 'The message body. Use <code>{scrap_details}</code> to insert the details.', 'scrap-enquiry' ),
				'placeholder' => '',
				'default'     => __( 'Offer accepted.', 'scrap-enquiry' ) . "\n\n" . '{url}' . "\n" . '{scrap_details}' . "\n",
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
	 * @param int $scrap_id Post ID for the scrap enquiry.
	 * @return void
	 */
	public function trigger( $scrap_id ) {
		if ( ! $this->is_enabled() ) {
			return;
		}

		$this->scrap_id = $scrap_id;

		// Get scrap details.
		$scrap_details = get_scrap_details( $scrap_id );

		// Replace any placeholder in the message body.
		$message = $this->get_message();
		$message = str_replace( '{scrap_details}', $scrap_details, $message );

		$url     = '<a href="' . admin_url( 'post.php?post=' . $scrap_id . '&action=edit' ) . '">' . __( 'View enquiry', 'scrap-enquiry' ) . '</a>';
		$message = str_replace( '{url}', $url, $message );

		$message = str_replace( "\n", '<br/>', $message );

		$subject = $this->get_subject();
		$subject = str_replace( '{enquiry_id}', $scrap_id, $subject );

		// Send email.
		$this->setup_and_send( $this->get_recipient(), $message, $subject );
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
		$email = get_post_meta( $this->scrap_id, 'mail', true );
		$fname = get_post_meta( $this->scrap_id, 'first_name', true );
		$lname = get_post_meta( $this->scrap_id, 'last_name', true );

		$header  = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header .= 'Reply-to: ' . $email . "<{$fname} {$lname}>\r\n";
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

	/**
	 * Get message recipient
	 *
	 * @return string
	 */
	public function get_recipient() {
		return $this->recipient;
	}
}
