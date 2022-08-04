<?php
/**
 * Plugin Name: Scrap Enquiry
 * Plugin URI: https://github.com/NTShop
 * Description: Creates scrap metal enquiries when a shopper submits the form on the public web site. Note that the form is located in the site's theme.
 * Version: 1.0
 * Author: Mark Edwards
 * Author URI: https://github.com/NTShop
 * Text Domain: scrap-enquiry
 * Domain Path: languages/
 * License: GPLv3
 * WC requires at least: 4.0
 * WC tested up to: 6.5
 *
 * Copyright (c) 2022 - Mark Edwards - All Rights Reserved
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package Scrap_Enquiry
 */

namespace MJE\ScrapEnquiry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SCRAP_INQUIRY_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SCRAP_INQUIRY_PLUGIN_DIR', trailingslashit( __DIR__ ) );
define( 'SCRAP_INQUIRY_PLUGIN_VERSION', '1.0' );

/**
 * Add hooks and loads class file if WooCommerce is active on the site.
 *
 * @return void
 */
function loader() {
	if ( ! class_exists( 'woocommerce' ) ) {
		return;
	}

	require_once dirname( __FILE__ ) . '/includes/class-scrap-enquiry.php';
	Scrap_Enquiry::init();
}
add_action( 'plugins_loaded', 'MJE\ScrapEnquiry\loader', 0 );

/**
 * This activation hook function is used to flush rewrite rules for endpoints
 *
 * @return void
 */
function plugin_activate() {
	// Load class files that define endpoints.
	require_once dirname( __FILE__ ) . '/includes/class-email-click-handler.php';
	require_once dirname( __FILE__ ) . '/includes/class-my-account.php';
	// Add endpoints.
	\MJE\ScrapEnquiry\Email_Click_Handler::add_endpoints();
	\MJE\ScrapEnquiry\My_Account::add_endpoint();
	// Flush WP rewrite rules.
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'MJE\ScrapEnquiry\plugin_activate' );

/**
 * This deactivation hook function is used to flush rewrite rules to remove endpoint rules
 *
 * @return void
 */
function plugin_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'MJE\ScrapEnquiry\plugin_deactivate' );
