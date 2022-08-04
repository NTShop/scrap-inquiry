<?php
/**
 * Post statuses class file
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
class Post_Statuses {

	/**
	 * Array of custom post statuses
	 *
	 * @var array
	 */
	public static $statuses = array();

	/**
	 * Add hook
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_status' ), 9 );
	}

	/**
	 * Register post statuses
	 *
	 * @return void
	 */
	public static function register_post_status() {

		self::$statuses = array(
			'si-new'      => array(
				'label'                     => _x( 'New enquiry', 'Scrap status', 'scrap-enquiry' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				/* translators: %s: number of orders */
				'label_count'               => _n_noop( 'New enquiry <span class="count">(%s)</span>', 'New enquiry <span class="count">(%s)</span>', 'scrap-enquiry' ),
			),
			'si-provided' => array(
				'label'                     => _x( 'Quote provided', 'Scrap status', 'scrap-enquiry' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				/* translators: %s: number of orders */
				'label_count'               => _n_noop( 'Quote provided <span class="count">(%s)</span>', 'Quote provided <span class="count">(%s)</span>', 'scrap-enquiry' ),
			),
			'si-accepted' => array(
				'label'                     => _x( 'Quote accepted', 'Scrap status', 'scrap-enquiry' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				/* translators: %s: number of orders */
				'label_count'               => _n_noop( 'Quote accepted <span class="count">(%s)</span>', 'Quote accepted <span class="count">(%s)</span>', 'scrap-enquiry' ),
			),
			'si-rejected' => array(
				'label'                     => _x( 'Quote rejected', 'Scrap status', 'scrap-enquiry' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				/* translators: %s: number of orders */
				'label_count'               => _n_noop( 'Quote rejected <span class="count">(%s)</span>', 'Quote rejected <span class="count">(%s)</span>', 'scrap-enquiry' ),
			),
			'si-paid'     => array(
				'label'                     => _x( 'Quote paid', 'Scrap status', 'scrap-enquiry' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				/* translators: %s: number of orders */
				'label_count'               => _n_noop( 'Quote paid <span class="count">(%s)</span>', 'Quote paid <span class="count">(%s)</span>', 'scrap-enquiry' ),
			),
		);

		foreach ( self::$statuses as $status => $values ) {
			register_post_status( $status, $values );
		}
	}
}

\MJE\ScrapEnquiry\Post_Statuses::init();
