<?php
/**
 * Customer Reviews for Google Merchant Badge Widget Class
 *
 * @class 		Customer_Reviews_For_Google_Merchant_Badge_Widget
 * @version		1.0.0
 * @since		1.0.0
 * @category	Widget
 * @package		Customer_Reviews_For_Google_Merchant/Classes/Badge_Widget
 * @author 		Ivan Matveev
 */
if ( !class_exists( 'Customer_Reviews_For_Google_Merchant_Badge_Widget' ) ) :

class Customer_Reviews_For_Google_Merchant_Badge_Widget extends WP_Widget {

	public $integration;

	/**
	 * Initialize class
	 * 
	 * @since	1.0.0
	 * @return	void
	 */
	public function __construct() {

		$options = array(

			'classname' => 'Customer_Reviews_For_Google_Merchant_Badge_Widget',
			'description' => __( 'Badge Widget for Google Merchant Customer Reviews Program', CUSTOMER_REVIEWS_FOR_GOOGLE_MERCHANT_PLUGIN_NAME ),

		);

		parent::__construct( 'Customer_Reviews_For_Google_Merchant_Badge_Widget', 'Customer Reviews Badge', $options );

		$this->integration = WC()->integrations->get_integration( CUSTOMER_REVIEWS_FOR_GOOGLE_MERCHANT_PLUGIN_NAME );

		/*
		if ( 
			isset($this->integration->settings['enabled']) and
			isset($this->integration->settings['merchant_id']) and
			'yes' == $this->integration->settings['enabled'] and 
			$this->integration->settings['merchant_id'] 
		) {

			// Enqueue Google Merchant Center js on WooCommerce 'Thank You Page'
			add_action('wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ), 998 );

		}
		*/
	}

	/**
	 * Output the widget content on the front-end
	 * 
	 * @since	1.0.0
	 * @param	array	$args: Display arguments including before_title/after_title/before_widget/after_widget
	 * @param	array	$instance: The settings for the particular instance of the widget
	 * @return	void
	 */
	public function widget( $args, $instance ) {

		if ( 
			isset($this->integration->settings['enabled']) and
			isset($this->integration->settings['merchant_id']) and
			'yes' == $this->integration->settings['enabled'] and 
			$this->integration->settings['merchant_id'] 
		) {
?>

<script src="https://apis.google.com/js/platform.js?onload=renderOptIn" async defer></script>
<g:ratingbadge merchant_id=<?php echo $this->integration->merchant_id; ?>></g:ratingbadge>​

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

		wp_enqueue_script(CUSTOMER_REVIEWS_FOR_GOOGLE_MERCHANT_PLUGIN_SLUG, 
			'https://apis.google.com/js/platform.js?onload=renderBadge', array(), '1', 'in_footer');

	}


	/**
	 * Output the option form field in admin Widgets screen
	 * 
	 * @since	1.0.0
	 * @param	array	$instance: The widget options
	 * @return	void
	 */
	public function form( $instance ) {
		
	}

	/**
	 * Updates a particular instance of a widget
	 * 
	 * @since	1.0.0
	 * @param	array	$new_instance: New settings for this instance as input by the user via WP_Widget::form()
	 * @param	array	$old_instance: Old settings for this instance
	 * @return	array	$new_instance: Settings to save or bool false to cancel saving
	 */
	public function update( $new_instance, $old_instance ) {
		
	}

}

endif;

