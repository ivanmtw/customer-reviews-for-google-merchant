<?php
/**
 * Customer Reviews for Google Merchant
 *
 * @class 		Customer_Reviews_For_Google_Merchant
 * @version		1.0.0
 * @since		1.0.0
 * @package		WooCommerce/Classes/Payment
 * @author 		Ivan Matveev
 */
if ( !class_exists( 'Customer_Reviews_For_Google_Merchant' ) ) :

class Customer_Reviews_For_Google_Merchant {

	/**
	 * Initialize class
	 * 
	 * @since	1.0.0
	 * @return	void
	 */
	public function __construct() {

		// Init localization
		add_action( 'init', array( $this, 'real_load_plugin_textdomain' ) );


		// Checks if WooCommerce is installed

		// Make sure WooCommerce is active
		if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

			// Deactivate the plugin
			deactivate_plugins( CUSTOMER_REVIEWS_FOR_GOOGLE_MERCHANT_PLUGIN_NAME . '/' . CUSTOMER_REVIEWS_FOR_GOOGLE_MERCHANT_PLUGIN_NAME . '.php');
			add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );

		}


		if ( class_exists( 'WC_Integration' ) && defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.9.0',
'>=' ) ) {

			// Initialize integration into Woocommerce
			$this->init_integration();

			// Initialize Badge Widget
			$this->init_badge_widget();

		} else {

			add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );

		}

	}

	/**
	 * Register localization path
	 * 
	 * @since	1.0.0
	 * @return	void
	 */
	public function real_load_plugin_textdomain() {

		if( !load_plugin_textdomain( CUSTOMER_REVIEWS_FOR_GOOGLE_MERCHANT_PLUGIN_NAME, false, dirname( plugin_basename( __FILE__ ) ) . '/../../languages' ) ) {

		}

	}

	/**
	 * WooCommerce fallback notice.
	 *
	 * @return string
	 */
	public function woocommerce_missing_notice() {
		echo '<div class="error"><p>' . sprintf( __( 'Please install the latest version of the "WooCommerce" plugin before using "Customer Reviews for Google Merchant" plugin.', CUSTOMER_REVIEWS_FOR_GOOGLE_MERCHANT_PLUGIN_NAME ) ) . '</p></div>';
	}

	/**
	 * Initialize integration into Woocommerce
	 * 
	 * @since	1.0.0
	 * @return	void
	 */
	public function init_integration() {

		// Include our integration class
		include_once 'Customer_Reviews_For_Google_Merchant_Integration.php';

		// Register the integration
		add_filter( 'woocommerce_integrations', array( $this, 'add_integration' ) );

	}

	/**
	 * Add a new integration to WooCommerce Integrations tab
	 * 
	 * @since	1.0.0
	 * @param	array	$integrations: all items in Integrations tab
	 * @return	array	$integrations: all items in Integrations tab + our custom item
	 */
	public function add_integration( $integrations ) {

		$integrations[] = 'Customer_Reviews_For_Google_Merchant_Integration';

		return $integrations;

	}

	/**
	 * Initialize Badge Widget
	 * 
	 * @since	1.0.0
	 * @return	void
	 */
	public function init_badge_widget() {

		// Include our widget class
		include_once 'Customer_Reviews_For_Google_Merchant_Badge_Widget.php';

		// Register the widget
		add_action( 'widgets_init', function() {
			register_widget( 'Customer_Reviews_For_Google_Merchant_Badge_Widget' );
		});

	}

}

endif;

