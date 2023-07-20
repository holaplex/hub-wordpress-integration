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
class Holaplex_Wp_Public
{

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
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->mint_drop_on_order_complete();
		$this->init_display_nft_tab_on_my_account();
		$this->init_replace_post_content();
		$this->init_content_gate_redirect();
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

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

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/holaplex-wp-public.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

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

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/holaplex-wp-public.js', array('jquery'), $this->version, false);
		wp_enqueue_script('holaplex_ajax_public', plugin_dir_url(__FILE__) . 'js/holaplex-ajax-public.js', array('jquery'), $this->version, true);
		wp_localize_script('holaplex_ajax_public', 'holaplex_ajax', array('ajax_url' => admin_url('admin-ajax.php')));
	}



	public function mint_drop_on_order_complete()
	{

		// get project_id and drop_id from product. Check if concated string is in current logged in user meta key holaplex_customer_id. if not found, create new customer and waller. if found, mint drop for that customer and wallet.
		function customer_data_str_to_array($holaplex_customer_data)
		{
			$project_id_array = [];
			if ($holaplex_customer_data == '' || $holaplex_customer_data == null) {
				return $project_id_array;
			}
			$holaplex_customer_project_array = explode('|', $holaplex_customer_data);
			foreach ($holaplex_customer_project_array as $holaplex_customer_project) {

				$holaplex_customer_project_id = explode(':', $holaplex_customer_project)[0];
				$holaplex_project_customer_wallet = explode(':', $holaplex_customer_project)[1];

				$project_id_array[$holaplex_customer_project_id] = [
					'customer_id' => explode('&', $holaplex_project_customer_wallet)[0],
					'wallet_address' => explode('&', $holaplex_project_customer_wallet)[1]
				];
			}
			return $project_id_array;
		}

		function on_order_complete($order_status, $order_id)
		{
			$order = wc_get_order($order_id);
			$items = $order->get_items();
			$holaplex_api = new Holaplex_Core();

			foreach ($items as $item) {
				$product_id = $item->get_product_id();
				$holaplex_drop_id = get_post_meta($product_id, 'holaplex_drop_id', true);
				$holaplex_project_id = get_post_meta($product_id, 'holaplex_project_id', true);

				// get current logged in user meta key holaplex_customer_id
				$holaplex_customer_data = get_user_meta(get_current_user_id(), 'holaplex_customer_id', true);
				// split holaplex_customer_id into array

				$project_id_array = json_decode($holaplex_customer_data, true);

				if (count($project_id_array) == 0) {
					// create new customer and wallet
					$created_wallet = $holaplex_api->create_customer_wallet($holaplex_project_id);
					
					$new_customer_data = isset($project_id_array[$holaplex_project_id]) && !empty($project_id_array) ? $project_id_array[$holaplex_project_id] : [];

					$new_customer_data = array_push($new_customer_data, $created_wallet);
					hookbug('new but mpty');
					hookbug($new_customer_data);
					
					$holaplex_customer_data[$holaplex_project_id] = $new_customer_data;
					// update user meta key holaplex_customer_id
					update_user_meta(get_current_user_id(), 'holaplex_customer_id', json_encode($holaplex_customer_data));
					
					$project_id_array = json_decode($holaplex_customer_data, true);
				}
				
				if (!array_key_exists($holaplex_project_id, $project_id_array)) {
					$created_wallet = $holaplex_api->create_customer_wallet($holaplex_project_id);
					
					$new_customer_data = isset($project_id_array[$holaplex_project_id]) && !empty($project_id_array) ? $project_id_array[$holaplex_project_id] : [];
					
					$new_customer_data = array_push($new_customer_data, $created_wallet);
					hookbug('new but not empty');
					hookbug($new_customer_data);
					$holaplex_customer_data[$holaplex_project_id] = $new_customer_data;
					// update user meta key holaplex_customer_id
					update_user_meta(get_current_user_id(), 'holaplex_customer_id', json_encode($holaplex_customer_data));

					$project_id_array = json_decode($holaplex_customer_data, true);

				}

				$holaplex_project_customer_wallet = $project_id_array[$holaplex_project_id]['wallet_address'];

				if ($holaplex_project_customer_wallet != '' && $holaplex_project_customer_wallet != null) {
					$drop_is_minted = $holaplex_api->mint_drop($holaplex_project_customer_wallet, $holaplex_drop_id);

					add_filter('woocommerce_thankyou_order_received_text', function ($str, $order) use ($drop_is_minted, $holaplex_project_customer_wallet) {
						$new_str = $str;
						if ($drop_is_minted) {
							$new_str = $str . ' <br/> <p>Drop minted and sent to receiver wallet: ' . $holaplex_project_customer_wallet . '.</p>';
						}
						return esc_html($new_str);
					}, 10, 2);
				}
			}
		}

		// add_action('woocommerce_thankyou', 'lala');
		// add_action('woocommerce_order_status_completed', function() {
		// 	lala('woocommerce_order_status_completed');
		// });
		// // add_action('woocommerce_payment_complete', 'lala');
		// add_action('woocommerce_payment_complete_order_status', function() {
		// 	lala('woocommerce_payment_complete_order_status');
		// });

		// add_action('woocommerce_payment_complete_order_status_completed', function() {
		// 	lala('woocommerce_payment_complete_order_status_completed');
		// });
		add_action('woocommerce_payment_complete_order_status', 'on_order_complete', 10, 2);
	}

	public function init_display_nft_tab_on_my_account()
	{
		add_action('init', 'register_new_item_endpoint');

		/**
		 * Register New Endpoint.
		 *
		 * @return void.
		 */
		function register_new_item_endpoint()
		{
			add_rewrite_endpoint(HOLAPLEX_MY_ACCOUNT_ENDPOINT, EP_ROOT | EP_PAGES);
		}

		add_filter('query_vars', 'new_item_query_vars');

		/**
		 * Add new query var.
		 *
		 * @param array $vars vars.
		 *
		 * @return array An array of items.
		 */
		function new_item_query_vars($vars)
		{

			$vars[] = HOLAPLEX_MY_ACCOUNT_ENDPOINT;
			return $vars;
		}

		add_filter('woocommerce_account_menu_items', 'holaplex_add_new_item_tab');

		/**
		 * Add New tab in my account page.
		 *
		 * @param array $items myaccount Items.
		 *
		 * @return array Items including New tab.
		 */
		function holaplex_add_new_item_tab($items)
		{

			$items[HOLAPLEX_MY_ACCOUNT_ENDPOINT] = 'NFTs';
			return $items;
		}


		add_action('woocommerce_account_' . HOLAPLEX_MY_ACCOUNT_ENDPOINT . '_endpoint', function ()
		{

			
			include_once HOLAPLEX_PLUGIN_PATH . 'public/partials/holaplex-wp-public-my-account.php';
		});

		/**
		 * Add content to the new tab.
		 *
		 * @return  string.
		 */
		function holaplex_add_new_item_content()
		{


			include_once HOLAPLEX_PLUGIN_PATH . 'public/partials/holaplex-wp-public-my-account.php';
		}
	}

	public function init_replace_post_content()
	{
		/**
		 * swap content of the entry
		 */
		function holaplex_replace_post_content($content)
		{
			global $post;
			$product_id = $post->holaplex_product_select;
			$current_user = wp_get_current_user();
			$core = new Holaplex_Core();
			$custom_text = $core->holaplex_display_custom_text();

			// fading excerpt preview
			if (get_option("holaplex_fading_excerpt_info")) {
				if ('show_fading_excerpt' === get_option("holaplex_fading_excerpt_info")) {
					$fading_excerpt = " fading-excerpt";
				} else {
					$fading_excerpt = "";
				}
			} else {
				$fading_excerpt = " fading-excerpt";
			}

			// length of content preview
			$excerpt_length = $core->holaplex_excerpt_length();

			if (!current_user_can('administrator')) {
				if (!$product_id || !wc_customer_bought_product($current_user->email, $current_user->ID, $product_id)) {
					if ($post->holaplex_meta_info === 'hide_default_meta') {
						$content = $custom_text;
					} elseif ($post->holaplex_meta_info === 'hide_excerpt_meta') {
						if ($post->post_excerpt) {
							$content = $post->post_excerpt;
							$content = '<div class="holaplex_hidden_excerpt' . $fading_excerpt . '">' . $content . '</div>' . $custom_text;
						} else {
							$content = wp_trim_words($post->post_content, $excerpt_length, '...');
							$content = '<div class="holaplex_hidden_excerpt' . $fading_excerpt . '">' . $content . '</div>' . $custom_text;
						}
					}
				}
			}
			return $content;
		}

		add_filter('the_content', 'holaplex_replace_post_content');
	}

	public function init_content_gate_redirect() {
		/**
		 * for redirect feature
		 */
		function holaplex_redirect() {
			global $post;
			if (!$post) {
				return;
			}
			$product_id = $post->holaplex_product_select;
			$selected_page = $post->holaplex_selected_page_id;
			$current_user = wp_get_current_user();

			if (!current_user_can('administrator')) {
				if ( !$product_id || !wc_customer_bought_product($current_user->email, $current_user->ID, $product_id)) {
					if (!is_home() && $selected_page && 'redirect_meta' === $post->holaplex_meta_info) {
						$url = get_permalink($selected_page);
						wp_redirect( $url );
						exit;
					}
				}
			}
		}
		add_action( 'template_redirect', 'holaplex_redirect' );		
	}
}
