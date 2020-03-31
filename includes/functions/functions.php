<?php
/**
 * Basic non-OOP functions
 *
 * @author		Ivan Matveev	ivanmtw@gmail.com
 * @copyright	Copyright (c) 2020 Ivan Matveev (ivanmtw@gmail.com) and WooCommerce
 * @version		1.0.0
 * @since		1.0.0
 */

/**
 * Exit if accessed directly.
 */
defined( 'ABSPATH' ) or exit;

/**
 * Add plugin page links into Wordpress Plugins Page
 * 
 * @since	1.0.0
 * @param	array	$links: all plugins links
 * @return	array	$links: all plugins links + our Settings link
 */
function customer_reviews_for_google_merchant_plugin_links( $links ) {

	$plugin_links = array(
		'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=integration&section=' . 
			CUSTOMER_REVIEWS_FOR_GOOGLE_MERCHANT_PLUGIN_NAME ) . '">' . 
			__( 'Settings', CUSTOMER_REVIEWS_FOR_GOOGLE_MERCHANT_PLUGIN_NAME ) . '</a>'
	);

	return array_merge( $plugin_links, $links );
}

add_filter( 
	sprintf( 'plugin_action_links_%s/%s.php', 
		CUSTOMER_REVIEWS_FOR_GOOGLE_MERCHANT_PLUGIN_NAME, 
		CUSTOMER_REVIEWS_FOR_GOOGLE_MERCHANT_PLUGIN_NAME
	), 
	CUSTOMER_REVIEWS_FOR_GOOGLE_MERCHANT_PLUGIN_SLUG . '_plugin_links' );

