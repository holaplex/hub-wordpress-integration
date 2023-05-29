<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Holaplex_Wp
 * @subpackage Holaplex_Wp/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Holaplex_Wp
 * @subpackage Holaplex_Wp/public
 * @author     Your Name <email@example.com>
 */
class Holaplex_Wp_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		$this->init_display_holaplex_customer_details_on_profile();

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Holaplex_Wp_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Holaplex_Wp_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/holaplex-wp-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Holaplex_Wp_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Holaplex_Wp_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/holaplex-wp-public.js', array( 'jquery' ), $this->version, false );

	}


	public function init_display_holaplex_customer_details_on_profile () {
		function holaplex_customer_details_shortcode($atts) {
			$current_user = wp_get_current_user();
			$holaplex_customer_id = get_user_meta($current_user->ID, 'holaplex_customer_id', true);
			
			if (!empty($holaplex_customer_id)) {
		?>

			<div class="holaplex-app">
				<div class="holaplex-app__header">
					<div class="holaplex-app__header__title">
						<h3><?php echo esc_html(__('Holaplex Customer Details', 'holaplex-wp')); ?></h3>
					</div>
				</div>
				<div class="holaplex-app__body">
					<div class="holaplex-app__body__content">
						<div class="holaplex-app__body__content__section">
							<div class="holaplex-app__body__content__section__title">
								<h4><?php echo esc_html(__('Customer ID', 'holaplex-wp')); ?></h4>
							</div>
							<div class="holaplex-app__body__content__section__content">
								<p><?php echo esc_html(substr($holaplex_customer_id, -6)); ?></p>
							</div>
						</div>
						<div class="holaplex-app__body__content__section">
							<div class="holaplex-app__body__content__section__title">
								<h4><?php echo esc_html(__('Wallet Address', 'holaplex-wp')); ?></h4>
							</div>
							<div class="holaplex-app__body__content__section__content">
								<p><?php echo esc_html(get_user_meta($current_user->ID, 'holaplex_wallet_address', true)); ?></p>
							</div>
						</div>
					</div>
				</div>
			</div>

		<?php

			} else {
				
		?>
			<div class="holaplex-app">
				<div class="holaplex-app__header">
					<div class="holaplex-app__header__title">
						<h3><?php echo esc_html(__('Holaplex Customer Details', 'holaplex-wp')); ?></h3>
					</div>
				</div>
				<div class="holaplex-app__body">
					<div class="holaplex-app__body__content">
						<div class="holaplex-app__body__content__section">
							<div class="holaplex-app__body__content__section__content">
								<p><button id="create-customer-button"><?php echo esc_html(__('Create Customer and Wallet', 'holaplex-wp')); ?></button></p>
							</div>
						</div>
					</div>
			</div>
		<?php

			}
			
	}

	add_action('woocommerce_edit_account_form', 'holaplex_customer_details_shortcode');	
}

}
