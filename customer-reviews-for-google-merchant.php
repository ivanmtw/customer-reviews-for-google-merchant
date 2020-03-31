<?php
/**
 * @wordpress-plugin
 * Plugin Name:				Customer Reviews for Google Merchant
 * Plugin URI:				https://github.com/ivanmtw/customer-reviews-for-google-merchant
 * Description:				Embeds Google Customer Reviews code in your website to collect customer feedback
 *
 * Version:					1.0.0
 * Stable tag:				1.0.0
 * Requires PHP:			5.3
 * Requires at least:		4.6
 * Tested up to:			5.3.2
 * WC requires at least:	3.9.0
 * WC tested up to:			4.0.0
 *
 * Author:					Ivan Matveev
 * Author URI:				https://github.com/ivanmtw
 * Developer:				Ivan Matveev
 * Developer URI:			https://github.com/ivanmtw
 *
 * License:					GNU General Public License v3.0
 * License URI:				http://www.gnu.org/licenses/gpl-3.0.html
 *
 * Text Domain:				customer-reviews-for-google-merchant
 * Domain Path:				/languages
 *
 * @link					https://github.com/ivanmtw/customer-reviews-for-google-merchant
 * @since					1.0.0
 * @package					Customer_Reviews_For_Google_Merchant
 * @author					Ivan Matveev	<ivanmtw@gmail.com>
 * @category				Admin
 * @copyright				Copyright (c) 2020 Ivan Matveev (ivanmtw@gmail.com) and WooCommerce
 * @version					1.0.0
 * @license					http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 */
define( 'CUSTOMER_REVIEWS_FOR_GOOGLE_MERCHANT_CURRENT_VERSION', '1.0.0' );
define( 'CUSTOMER_REVIEWS_FOR_GOOGLE_MERCHANT_PLUGIN_NAME', 'customer-reviews-for-google-merchant' );
define( 'CUSTOMER_REVIEWS_FOR_GOOGLE_MERCHANT_PLUGIN_SLUG', 'customer_reviews_for_google_merchant' );

include_once( __DIR__ . '/includes/functions/functions.php' );

/**
 * Load main plugin class
 * 
 * @since	1.0.0
 * @return	void
 */
function customer_reviews_for_google_merchant_init() {

	include_once( __DIR__ . '/includes/classes/Customer_Reviews_For_Google_Merchant.php' );

	$Customer_Reviews_For_Google_Merchant = new Customer_Reviews_For_Google_Merchant();

}

add_action( 'plugins_loaded', 'customer_reviews_for_google_merchant_init' );

