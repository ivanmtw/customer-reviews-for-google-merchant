<?php
/**
 * Customer Reviews for Google Merchant Integration Class
 *
 * @class 		Customer_Reviews_For_Google_Merchant_Integration
 * @version		1.0.0
 * @since		1.0.0
 * @category	Integration
 * @package		Customer_Reviews_For_Google_Merchant/Classes/Integration
 * @author 		Ivan Matveev
 */
if ( !class_exists( 'Customer_Reviews_For_Google_Merchant_Integration' ) ) :
class Customer_Reviews_For_Google_Merchant_Integration extends WC_Integration {

	public $id;
	public $method_title;
	public $method_description;
	public $opt_in_style_vars;
	public $countries;
	public $current_country;
	public $enabled;
	public $merchant_id;
	public $delivery_country;
	public $estimated_delivery_day;
	public $estimated_delivery_date;
	public $opt_in_style;
	public $send_gtin_data;
	public $badge_position;
	public $order_id;

	/**
	 * Initialize class
	 * 
	 * @since	1.0.0
	 * @return	void
	 */
	public function __construct() {

		$this->id                 = CUSTOMER_REVIEWS_FOR_GOOGLE_MERCHANT_PLUGIN_NAME;
		$this->method_title       = __( 'Customer Reviews for Google Merchant', CUSTOMER_REVIEWS_FOR_GOOGLE_MERCHANT_PLUGIN_NAME );
		$this->method_description = __( 'Embeds Google Customer Reviews form in your website to collect customer feedback. You need to activate Customer Reviews Program in your <a href="https://merchants.google.com/mc/programs">Merchant Center account</a> before start using this plugin.<br /><br />You can customize widget in <a href="/wp-admin/widgets.php">Appearance > Widgets</a> to show Badge image with user ratings like this:<br /><img src="/wp-content/plugins/' . CUSTOMER_REVIEWS_FOR_GOOGLE_MERCHANT_PLUGIN_NAME . '/assets/images/' . CUSTOMER_REVIEWS_FOR_GOOGLE_MERCHANT_PLUGIN_SLUG . '_badge.png" />', CUSTOMER_REVIEWS_FOR_GOOGLE_MERCHANT_PLUGIN_NAME );

		// Get all countries pairs ['cityname'=>'citycode']
		if ( is_admin() and class_exists( 'WC_Countries' ) ) {

			$this->opt_in_style_vars = array(

				'CENTER_DIALOG'			=> __( 'Displayed as a dialog box in the center of the view', CUSTOMER_REVIEWS_FOR_GOOGLE_MERCHANT_PLUGIN_NAME ),
				'BOTTOM_RIGHT_DIALOG'	=> __( 'Displayed as a dialog box at the bottom right of the view', CUSTOMER_REVIEWS_FOR_GOOGLE_MERCHANT_PLUGIN_NAME ),
				'BOTTOM_LEFT_DIALOG'	=> __( 'Displayed as a dialog box at the bottom left of the view', CUSTOMER_REVIEWS_FOR_GOOGLE_MERCHANT_PLUGIN_NAME ),
				'TOP_RIGHT_DIALOG'		=> __( 'Displayed as a dialog box at the top right of the view', CUSTOMER_REVIEWS_FOR_GOOGLE_MERCHANT_PLUGIN_NAME ),
				'TOP_LEFT_DIALOG'		=> __( 'Displayed as a dialog box at the top left of the view', CUSTOMER_REVIEWS_FOR_GOOGLE_MERCHANT_PLUGIN_NAME ),

			);

			$this->countries = new WC_Countries();
			$this->countries = $this->countries->__get( 'countries' ); 

		}

		// Get current country code from Woocommerce Shop settings
		$this->current_country = wc_get_base_location()['country'];

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user variables
		$this->enabled					= $this->get_option( 'enabled' );
		$this->merchant_id				= $this->get_option( 'merchant_id' );
		$this->delivery_country			= $this->get_option( 'delivery_country' );
		$this->estimated_delivery_day	= $this->get_option( 'estimated_delivery_day' );
		$this->opt_in_style				= $this->get_option( 'opt_in_style' );
		$this->send_gtin_data			= $this->get_option( 'send_gtin_data' );
		$this->badge_position			= $this->get_option( 'badge_position' );

		// Set estimated delivery date in format YYYY-MM-DD
		$date = new DateTime();
		if ( strlen( $this->estimated_delivery_day ) and $this->estimated_delivery_day >= 0 ) {
			$date->add(new DateInterval('P' . $this->estimated_delivery_day . 'D'));
		}
		$this->estimated_delivery_date = $date->format('Y-m-d');

		// Processes and saves options
		add_action( 'woocommerce_update_options_integration_' .  $this->id, array( $this, 'process_admin_options' ) );

		// Set Order ID
		add_action( 'woocommerce_thankyou', array( $this, 'set_order_id' ) );

		/** 
		 * ! Standard way to enqueue script disabled while Google Merchants needs to place scripts very close
		 * 
		 * Enqueue Google Merchant Center js on WooCommerce 'Thank You Page'
		 * add_action('wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ), 9998 );
		 * 
		 * Add defer and async attributes to frontend script
		 * add_filter('script_loader_tag', array( $this, 'add_defer_async_attribute' ), 10, 2);
		 */

		// Filter params
		add_filter( 'woocommerce_settings_api_sanitized_fields_' . $this->id, array( $this, 'sanitize_settings' ) );

		// Show Customer Feedback code and form on WooCommerce 'Thank You Page'
		add_action( 'wp_footer', array( $this, 'add_content_thankyou' ), 9999 );

	}

	/**
	 * Initialize integration settings form fields.
	 * 
	 * @since	1.0.0
	 * @return	void
	 */
	public function init_form_fields() {

		if ( !is_admin() ) {
			return;
		}

		$default_for_countries = array( '' => __( 'Select Country', CUSTOMER_REVIEWS_FOR_GOOGLE_MERCHANT_PLUGIN_NAME ));

		if ( count( $this->countries ) ) {

			// Prepare countries select list 
			$this->countries = array_merge ( $default_for_countries, $this->countries );

		}

		// Initialize all fields for plugin settings form
		$this->form_fields = array(

			'enabled' => array(
				'title'				=> __( 'Enable/Disable', CUSTOMER_REVIEWS_FOR_GOOGLE_MERCHANT_PLUGIN_NAME ),
				'label'				=> __( 'Enable Google Customer Feedback Form to show after ordering', CUSTOMER_REVIEWS_FOR_GOOGLE_MERCHANT_PLUGIN_NAME ),
				'description'		=> __( 'Keep in mind that the form will not be displayed if the customer has not specified an email', CUSTOMER_REVIEWS_FOR_GOOGLE_MERCHANT_PLUGIN_NAME ),
				'type'				=> 'checkbox',
				'default'			=> 'no',
			),

			'merchant_id' => array(
				'title'             => __( 'Merchant ID', CUSTOMER_REVIEWS_FOR_GOOGLE_MERCHANT_PLUGIN_NAME ),
				'type'              => 'text',
				'description'       => __( 'Your Merchant Center ID. You can get this value from Your account in the <a href="https://merchants.google.com/mc/overview">Google Merchant Center</a>. Usually the code is located at the top right of this page.', CUSTOMER_REVIEWS_FOR_GOOGLE_MERCHANT_PLUGIN_NAME ),
				'desc_tip'          => false,
				'default'           => '',
			),

			'delivery_country' => array(
				'title'             => __( 'Country', CUSTOMER_REVIEWS_FOR_GOOGLE_MERCHANT_PLUGIN_NAME ),
				'description'       => __( 'Identifies where the customer\'s order will be delivered.', CUSTOMER_REVIEWS_FOR_GOOGLE_MERCHANT_PLUGIN_NAME ),
				'type'              => 'select',
				'default'           => $this->current_country,
				'options'			=> $this->countries,
			),

			'estimated_delivery_day' => array(
				'title'             => __( 'Estimated delivery day', CUSTOMER_REVIEWS_FOR_GOOGLE_MERCHANT_PLUGIN_NAME ),
				'type'              => 'text',
				'description'       => __( 'Estimated delivery day relative to today. Set 0 for delivery on the day of order, 1 – for next day to delivery, 2 – for 2 days to delivery etc.', CUSTOMER_REVIEWS_FOR_GOOGLE_MERCHANT_PLUGIN_NAME ),
				'desc_tip'          => false,
				'default'           => 0,
			),

			'opt_in_style' => array(
				'title'             => __( 'Opt-in style', CUSTOMER_REVIEWS_FOR_GOOGLE_MERCHANT_PLUGIN_NAME ),
				'description'       => __( 'Specifies how the opt-in module\'s dialog box is displayed.', CUSTOMER_REVIEWS_FOR_GOOGLE_MERCHANT_PLUGIN_NAME ),
				'type'              => 'select',
				'default'           => array_keys( $this->opt_in_style_vars )[0],
				'options'			=> $this->opt_in_style_vars,
			),

			'send_gtin_data' => array(
				'title'				=> __( 'Send GTINs', CUSTOMER_REVIEWS_FOR_GOOGLE_MERCHANT_PLUGIN_NAME ),
				'label'				=> __( 'Send GTIN products attribute to Google Merchant Center', CUSTOMER_REVIEWS_FOR_GOOGLE_MERCHANT_PLUGIN_NAME ),
				'description'		=> __( 'Send GTIN attribute (12/13/14-digit Bar code) for each product to Google Merchant Center after completing each order. You should specify GTIN on the product edit page in its advanced attributes block.', CUSTOMER_REVIEWS_FOR_GOOGLE_MERCHANT_PLUGIN_NAME ),
				'type'				=> 'checkbox',
				'default'			=> 'yes',
			),

		);
	}

	/**
	 * Sanitize our settings params
	 * 
	 * @since	1.0.0
	 * @param	array	$settings: all settings from our form
	 * @return	array	$settings: all sanitized settings from our form
	 */
	public function sanitize_settings( $settings ) {

		if ( isset( $settings ) ) {

			// Set merchant_id to safe value for web
			if ( isset ( $settings['merchant_id'] ) ) {
				$settings['merchant_id'] = sanitize_text_field ( $settings['merchant_id'] );
			}

			// Check if delivery_country in allowed variants
			if ( isset ( $settings['delivery_country'] ) ) {

				$settings['delivery_country'] = strval ( $settings['delivery_country'] );

				foreach ( $this->countries as $key => $value ) {

					if ( $settings['delivery_country'] == $key ) {

						goto done;

					}
				}

				// If delivery_country not in allowed variants, set to default value
				$settings['delivery_country'] = $this->current_country;

			}

			// Override estimated_delivery_day to numeric value
			if ( isset ( $settings['estimated_delivery_day'] ) ) {

				$settings['estimated_delivery_day'] = intval ( $settings['estimated_delivery_day'] );

			}

			// Check if opt_in_style in allowed variants
			if ( isset( $settings['opt_in_style'] ) ) {

				foreach ( $this->opt_in_style_vars as $key => $value ) {

					if ( $settings['opt_in_style'] == $key ) {

						goto done;

					}
				}

				// If opt_in_style not in allowed variants, set to default value
				$settings['opt_in_style'] = array_keys( $this->opt_in_style_vars )[0];
			}
		}

		done:
		return $settings;

	}

	/**
	 * Validate our settings params and show admin notices if necessary
	 * 
	 * @since	1.0.0
	 * @param	string	$key: option field name
	 * @param	string	$value: option field value
	 * @return	string	$value: option field value
	 */
	public function validate_merchant_id_field( $key, $value ) {

		if ( isset( $value ) and 3 > strlen( $value ) ) {

			WC_Admin_Settings::add_error( esc_html__( 'Looks like your Merchant ID is wrong. Check your ID on Google Merchant Center, https://merchants.google.com/mc/overview', CUSTOMER_REVIEWS_FOR_GOOGLE_MERCHANT_PLUGIN_NAME ) );

		}

		return $value;
	}

	/**
	 * Get Order ID and set inside object on WooCommerce 'Thank You Page'
	 * 
	 * @since	1.0.0
	 * @param	string	$order_id: Order ID
	 * @return	void
	 */
	public function set_order_id( $order_id ) {

		$this->order_id = $order_id;

	}

	/**
	 * Call Customer Feedback code and form on WooCommerce 'Thank You Page'
	 * 
	 * @since	1.0.0
	 * @return	void
	 */
	public function add_content_thankyou( ) {

		// Call Customer Feedback form on WC Thank You page if form showing is enabled
		if ( 
			'yes' == $this->enabled and 
			$this->merchant_id and 
			$this->order_id and
			is_checkout() and 
			!empty( is_wc_endpoint_url('order-received') ) 
		) {

			$order = wc_get_order( $this->order_id );
			if ( !$order ) {

				return;

			}

			// Don't show the code if the user did not leave an email
			$customer_email = $order->get_billing_email();
			if ( !strlen( $customer_email ) ) {

				return;

			}

			// Add GTINs list to tracking code if enabled
			if ( $this->send_gtin_data == 'yes' ) {
				
				$gtins = array();
				$gtins_list = "";

				// Search GTINs in product attributes (WP DB - wp_options)
				foreach ( $order->get_items() as $item_id => $item ) {

				    $item_data = $item->get_data();

				    if ( $item['product_id'] ) {

				        $product = wc_get_product( $item['product_id'] );

						$attrs = get_post_meta($item['product_id'], '_product_attributes', true);

						foreach ( $attrs as $attr_key => $attr_val ) {

							if ( strpos( strtolower( $attr_key ), 'gtin' ) !== false ) {

								if ( in_array( strlen ( $attr_val['value'] ), array( 12, 13, 14 ) ) ) {
						
									$gtins[] = array( 'gtin' => $attr_val['value'] );
								}

							}

						}

				    }

				}	

				if ( count( $gtins ) ) {

					$gtins_list = '"products": ' . json_encode( $gtins );

				}
			}

?>
<script src="https://apis.google.com/js/platform.js?onload=renderOptIn" async defer></script>
<script>
window.renderOptIn = function() {
	window.gapi.load('surveyoptin', function() {
		window.gapi.surveyoptin.render({
			"merchant_id": "<?php echo $this->merchant_id; ?>",
			"order_id": "<?php echo $this->order_id; ?>",
			"email": "<?php echo $customer_email; ?>",
			"delivery_country": "<?php echo $this->delivery_country; ?>",
			"estimated_delivery_date": "<?php echo $this->estimated_delivery_date; ?>",
			"opt_in_style": "<?php echo $this->opt_in_style; ?>"<?php if ( strlen( $gtins_list ) ) { echo ",\n\t\t\t" . $gtins_list . "\n"; } ?>
		});
	});
}
</script>
<?php

		}

	}

	/**
	 * Enque scrips on frontend if Customer Feedback form is enabled
	 * 
	 * @since	1.0.0
	 * @return	void
	 */
	public function enqueue_frontend_scripts() {   

		if ( 
			'yes' == $this->enabled and 
			$this->merchant_id and 
			$this->order_id and 
			is_checkout() and 
			!empty( is_wc_endpoint_url('order-received') ) 
		) {

			$order = wc_get_order( $this->order_id );
			if ( !$order ) {

				return;

			}

			// Don't show the code if the user did not leave an email
			$customer_email = $order->get_billing_email();
			if ( !strlen( $customer_email ) ) {

				return;

			}

			wp_enqueue_script(CUSTOMER_REVIEWS_FOR_GOOGLE_MERCHANT_PLUGIN_SLUG, 
				'https://apis.google.com/js/platform.js?onload=renderBadge', array(), '1', 'in_footer');
		}

	}

	/**
	 * Add defer and async attributes to frontend script
	 * 
	 * @since	1.0.0
	 * @param	string	$tag: html script tag
	 * @param	string	$handle: enqueue handler name
	 * @return	string	$tag: html script tag with defer and async
	 */
	public function add_defer_async_attribute( $tag, $handle ) {

	   $scripts = array( CUSTOMER_REVIEWS_FOR_GOOGLE_MERCHANT_PLUGIN_SLUG );

	   foreach( $scripts as $script ) {

	      if ( $script === $handle ) {

	         return str_replace(array( 'async defer', ' src' ), array ( '', ' async defer src' ), $tag);

	      }

	   }

	   return $tag;

	}

}
endif;

