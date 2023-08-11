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

	public $core;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version, $core)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		$this->core = $core;

		$this->mint_drop_on_order_complete();
		$this->init_display_nft_tab_on_my_account();
		$this->init_replace_post_content();
		$this->init_content_gate_redirect();
		$this->show_drop_after_product_meta();
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

	public function show_drop_after_product_meta()
	{
		$core = $this->core;
		$holaplex_projects = $core->holaplex_projects;

		function show_drop_after_product_meta($holaplex_projects, $core)
		{
			global $post;
			$drop_id = get_post_meta($post->ID, 'holaplex_drop_id', true);
			$project_id = get_post_meta($post->ID, 'holaplex_project_id', true);
			// check if project_id in $holaplex_projects
			$project_exists = false;

			foreach ($holaplex_projects as $project) {
				if ($project['id'] === $project_id) {
					$project_exists = true;
				}
			}

			if (!$project_exists) {
				echo '<div class="holaplex-drop-warning">⚠️ Minting Drop might not work</div>';
				return;
			}

			if ($drop_id && $drop_id !== '') {
				// show message if drop supply is 0 
				$drop =  $core->get_drop($project_id, $drop_id);
				if ($drop && !empty($drop)) {

					if ((int)$drop['collection']['supply'] - (int)$drop['collection']['totalMints'] < 1) {
						echo '<div class="holaplex-drop-warning">🪫 Drop supply is low</div>';
					}
				}
			}
		}

		add_action('woocommerce_product_meta_end', function () use ($holaplex_projects, $core) {
			show_drop_after_product_meta($holaplex_projects, $core);
		});
	}

	public function mint_drop_on_order_complete()
	{

		$core = $this->core;
		$holaplex_projects = $core->holaplex_projects;

		function on_order_complete($order_status, $order_id, $holaplex_projects, $core)
		{
			$order = wc_get_order($order_id);
			$items = $order->get_items();
			$mint_status_message = '';

			foreach ($items as $item) {
				$item_data = $item->get_data();
				$quantity = $item_data['quantity'];

				$product_id = $item->get_product_id();
				$holaplex_drop_id = get_post_meta($product_id, 'holaplex_drop_id', true);
				$holaplex_project_id = get_post_meta($product_id, 'holaplex_project_id', true);

				$drop =  $core->get_drop($holaplex_project_id, $holaplex_drop_id);
				$blockchain = $drop['collection']['blockchain'];
				$asset_list = [
					'ETHEREUM' => 'ETH',
					'POLYGON' => 'MATIC',
					'SOLANA' => 'SOL',
				];
				$asset_type = $asset_list[$blockchain];

				$project_ids = array_map(function ($project) {
					return $project['id'];
				}, $holaplex_projects);

				// check if holaplex_project_id is in project_ids
				if (!in_array($holaplex_project_id, $project_ids)) {
					$mint_status_message .= "Skipped minting for this drop. Please check Holaplex Hub Settings. Project with ID $holaplex_project_id not found\n";
					continue;
				}				

				// get current logged in user meta key holaplex_customer_id
				$holaplex_customer_data = get_user_meta(get_current_user_id(), 'holaplex_customer_id', true);

				if ($holaplex_customer_data != '') {
					$project_id_array = json_decode($holaplex_customer_data, true);
				} else {
					$project_id_array = [];
				}

				if (count($project_id_array) == 0) {
					// create new customer and wallet
					$created_wallet = $core->create_customer_wallet($holaplex_project_id, '', $asset_type);

					$new_holaplex_customer_data = [];
					$new_holaplex_customer_data[$holaplex_project_id] = $created_wallet;
					// update user meta key holaplex_customer_id
					update_user_meta(get_current_user_id(), 'holaplex_customer_id', wp_json_encode($new_holaplex_customer_data));

					$project_id_array = $new_holaplex_customer_data;
				}

				if (!array_key_exists($holaplex_project_id, $project_id_array)) {
					$created_wallet = $core->create_customer_wallet($holaplex_project_id, '', $asset_type);

					$project_id_array[$holaplex_project_id] = $created_wallet;
					// update user meta key holaplex_customer_id
					update_user_meta(get_current_user_id(), 'holaplex_customer_id', wp_json_encode($project_id_array));
				}



				$holaplex_project_customer_wallet = $core->ensure_wallet_or_create_recursively($project_id_array, $holaplex_project_id, $asset_type)['wallet_address'];
				$mint_cart_id = "$holaplex_project_customer_wallet$holaplex_drop_id$quantity";
				// hookbug("Previeous Mint Cart ID: $core->$mint_cart_id");
				// hookbug("New Mint Cart ID: $mint_cart_id");
				if ($holaplex_project_customer_wallet != '' && $holaplex_project_customer_wallet != null && $core->$mint_cart_id !== $mint_cart_id) {
					hookbug("Calling Mint");
					$drop_is_minted = $core->mint_drop($holaplex_project_customer_wallet, $holaplex_drop_id, $quantity);
					if ($drop_is_minted) {
						$core->$mint_cart_id = $mint_cart_id;	
						$order->update_meta_data( 'holaplex_mint_drop_status', !empty($mint_status_message) ? $mint_status_message : 'Drop(s) minted successfully' );
						$order->save();
					} else {
						$order->update_meta_data( 'holaplex_mint_drop_status', 'Error minting drop(s)' );
						$order->save();
					}
				} else {
					sleep(1);
					hookbug($mint_cart_id);
				}
			}
		}

		add_action('woocommerce_payment_complete_order_status', function ($order_status, $order_id) use ($holaplex_projects, $core) {
			on_order_complete($order_status, $order_id, $holaplex_projects, $core);
		}, 10, 2);
	}

	public function init_display_nft_tab_on_my_account()
	{

		$core = $this->core;


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


		add_action('woocommerce_account_' . HOLAPLEX_MY_ACCOUNT_ENDPOINT . '_endpoint', function () use ($core) {


			include_once HOLAPLEX_PLUGIN_PATH . 'public/partials/holaplex-wp-public-my-account.php';
		});

	}

	public function init_replace_post_content()	
	{
		$core = $this->core;
		function holaplex_replace_post_content($content, $core)
		{
			global $post;
			$product_id = get_post_meta($post->ID, 'holaplex_product_select', true);
			$current_user = wp_get_current_user();
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

		add_filter('the_content', function ($content) use ($core) {
			return holaplex_replace_post_content($content, $core);
		});
	}

	public function init_content_gate_redirect()
	{
		/**
		 * for redirect feature
		 */
		function holaplex_redirect()
		{
			global $post;
			if (!$post) {
				return;
			}
			$product_id = $post->holaplex_product_select;
			$selected_page = $post->holaplex_selected_page_id;
			$current_user = wp_get_current_user();

			if (!current_user_can('administrator')) {
				if (!$product_id || !wc_customer_bought_product($current_user->email, $current_user->ID, $product_id)) {
					if (!is_home() && $selected_page && 'redirect_meta' === $post->holaplex_meta_info) {
						$url = get_permalink($selected_page);
						wp_redirect($url);
						exit;
					}
				}
			}
		}
		add_action('template_redirect', 'holaplex_redirect');
	}

	public function init_disable_add_to_cart_button_on_low_supply()
	{
		$core = $this->core;

		add_filter('woocommerce_loop_add_to_cart_link', function ($add_to_cart_html, $product) use ($core) {
			return remove_add_to_cart_specific_products($add_to_cart_html, $product, $core);
		}, 25, 2);

		function remove_add_to_cart_specific_products($add_to_cart_html, $product, $core)
		{
			$product_id = $product->get_id();
			
			$drop_id = get_post_meta($product_id, 'holaplex_drop_id', true);
			$project_id = get_post_meta($product_id, 'holaplex_project_id', true);
			$holaplex_product_add_to_cart_on_low = get_post_meta($product_id, 'holaplex_product_add_to_cart_on_low', true);
			
			// get the drop 
			if ($drop_id && $drop_id !== '') {
				// show message if drop supply is 0 
				$drop =  $core->get_drop($project_id, $drop_id);
				if ($drop) {
					if ($drop['collection']['totalMints'] - $drop['collection']['supply'] < 1) {
						if ($holaplex_product_add_to_cart_on_low) {
							return '<button disable class="disbaled_button button alt">Drop Supply Low</button>';
						}
					}
				} else {
					return "Hello";
				}
			}


			return $add_to_cart_html;
		}
	}
}
