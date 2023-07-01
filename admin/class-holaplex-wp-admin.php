<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Holaplex_Wp
 * @subpackage Holaplex_Wp/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Holaplex_Wp
 * @subpackage Holaplex_Wp/admin
 * @author     Your Name <email@example.com>
 */
class Holaplex_Wp_Admin
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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */

	private $holaplex_status = '⛔ disconnected';
	private $holaplex_projects = [];

	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->add_wc_products_drop_id_filter();
		$this->login_to_holaplex();
		$this->add_holaplex_menu();
		$this->init_ajax_sync_product_with_item();
		$this->init_add_holaplex_customer_id_field();
		$this->init_save_holaplex_customer_id_field();
		$this->init_ajax_holaplex_disconnect();
		$this->init_ajax_holaplex_connect();
	}

	/**
	 * Register the stylesheets for the admin area.
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

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/holaplex-wp-admin.css', array(), $this->version, 'all');
		wp_enqueue_style('minimal-grid', plugin_dir_url(__FILE__) . 'css/grid.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
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

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/holaplex-wp-admin.js', array('jquery'), $this->version, false);
		wp_enqueue_script('holaplex-ajax-admin', plugin_dir_url(__FILE__) . 'js/holaplex-ajax-admin.js', array('jquery'), $this->version, false);

		wp_localize_script('holaplex-ajax-admin', 'holaplex_wp_ajax', array('ajax_url' => admin_url('admin-ajax.php')));
	}

	public function add_wc_products_drop_id_filter()
	{
		function handle_custom_query_var($query, $query_vars)
		{
			if (!empty($query_vars['holaplex_drop_id'])) {

				$query['meta_query'] = [
					'relation' => 'OR',
					[
						'key' => 'holaplex_drop_id',
						'value'   => array(''),
						'compare' => 'NOT IN'
					],
					[
						'key' => 'holaplex_drop_id',
						'compare' => 'EXISTS'
					],
					[
						'key' => 'holaplex_drop_id',
						'value'   => '',
						'compare' => '!='
					],
				];
			}

			return $query;
		}
		add_filter('woocommerce_product_data_store_cpt_get_products_query', 'handle_custom_query_var', 10, 2);
	}


	public function init_save_holaplex_customer_id_field()
	{
		function save_holaplex_customer_id_field($user_id)
		{
			if (current_user_can('edit_user', $user_id) && isset($_POST['holaplex_customer_id'])) {
				$holaplex_customer_id = sanitize_text_field($_POST['holaplex_customer_id']);
				update_user_meta($user_id, 'holaplex_customer_id', $holaplex_customer_id);
			}
		}
		add_action('personal_options_update', 'save_holaplex_customer_id_field');
		add_action('edit_user_profile_update', 'save_holaplex_customer_id_field');
	}

	public function init_add_holaplex_customer_id_field()
	{
		function add_holaplex_customer_id_field($user)
		{
			$holaplex_customer_id = get_user_meta($user->ID, 'holaplex_customer_id', true);
?>
			<h3><?php _e('Holaplex Customer ID', 'holaplex-wp'); ?></h3>
			<table class="form-table">
				<tr>
					<th>
						<label for="holaplex_customer_id"><?php _e('Holaplex Customer ID', 'holaplex-wp'); ?></label>
					</th>
					<td>
						<input readonly type="text" name="holaplex_customer_id" id="holaplex_customer_id" value="<?php echo esc_attr($holaplex_customer_id); ?>" class="regular-text" />
					</td>
				</tr>
			</table>
	<?php
		}
		add_action('show_user_profile', 'add_holaplex_customer_id_field');
		add_action('edit_user_profile', 'add_holaplex_customer_id_field');
	}


	public function init_ajax_sync_product_with_item()
	{

		function imageTypeBasedOnHeaders($url) {
			$minimumBytes = 1024; // Assuming a minimum of 1KB
		
			// Perform the HTTP request
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$response = curl_exec($ch);
			$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
			curl_close($ch);
				
			// Get the CID from the response headers
			$headers = get_headers($url);
		
			$res = [
				'contentType' => $contentType,
				'response' => $response,
				'headers' => $headers
			];
		
			return $res;
		}
		

		function add_product_with_drop_id_callback()
		{
			$nonce = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : '';
			if (!wp_verify_nonce($nonce, HOLAPLEX_NONCE)) {
				die('Invalid nonce');
			}
			// sanitize the input
			$drop_id =  isset($_POST['drop_id']) ? sanitize_text_field($_POST['drop_id']) : '';
			$drop_name =  isset($_POST['drop_name']) ? sanitize_text_field($_POST['drop_name']) : '';
			$drop_image =  isset($_POST['drop_image']) ? sanitize_text_field($_POST['drop_image']) : '';
			$drop_description = isset($_POST['drop_desc']) ? sanitize_text_field($_POST['drop_desc']) : '';
			$drop_project_id = isset($_POST['project_id']) ? sanitize_text_field($_POST['project_id']) : '';
			// create a product with price

			$product = new WC_Product_Simple();

			$product->set_name( $drop_name ); // product title

			$product->set_regular_price( 0.01 ); // in current shop currency

			$product->set_short_description( $drop_description );
			// you can also add a full product description
			$product->set_description( $drop_description );

			// $product->set_image_id( 90 );

			$product->save();

			// insert the product
			$product_id = $product->get_id();

			// add drop_id to product meta
			update_post_meta($product_id, 'holaplex_drop_id', $drop_id);
			update_post_meta($product_id, 'holaplex_project_id', $drop_project_id);
			update_post_meta( $product_id, '_regular_price', "0.01" );


			// add drop_image to product thumbnail	
			$upload_dir = wp_get_upload_dir();
			$image_data = imageTypeBasedOnHeaders($drop_image);
			// get file extension from image data
			$file_ext = explode('/', $image_data['contentType'])[1];
			// random file name 
			$filename = uniqid() . '.' . $file_ext;

			if (wp_mkdir_p($upload_dir['path'])) {
				$file = $upload_dir['path'] . '/' . $filename;
			} else {
				$file = $upload_dir['basedir'] . '/' . $filename;
			}

			file_put_contents($file, file_get_contents($drop_image));

			// // save image to product thumbail
			$attachment = array(
				'post_mime_type' => $image_data['contentType'],
				'post_title' => sanitize_file_name($filename),
				'post_content' => '',
				'post_status' => 'inherit',
				'post_parent' => $product_id
			);

			// // save attachement
			$attach_id = wp_insert_attachment($attachment, $file, $product_id);
			// // set product thumbnail
			set_post_thumbnail($product_id, $attach_id);


			// Example response
			$response = array('success' => true, 'product' => $product_id);

			wp_send_json($response);
		}
		add_action('wp_ajax_add_product_with_drop_id', 'add_product_with_drop_id_callback');
		add_action('wp_ajax_nopriv_add_product_with_drop_id', 'add_product_with_drop_id_callback');
	}


	public function init_ajax_holaplex_connect()
	{
		function holaplex_connect_callback()
		{

			$api_key = isset($_POST['holaplex_api_key']) ? sanitize_text_field($_POST['holaplex_api_key']) : '';
			$org_id = isset($_POST['holaplex_org_id']) ? sanitize_text_field($_POST['holaplex_org_id']) : '';
			$project = isset($_POST['holaplex_project']) ? sanitize_text_field($_POST['holaplex_project']) : '';

			update_option('holaplex_api_key', $api_key);
			update_option('holaplex_project', $project);
			update_option('holaplex_org_id', $org_id);
			// Example response
			$response = array('success' => true);

			wp_send_json($response);
		}
		add_action('wp_ajax_holaplex_connect', 'holaplex_connect_callback');
		add_action('wp_ajax_nopriv_holaplex_connect', 'holaplex_connect_callback');
	}

	public function init_ajax_holaplex_disconnect()
	{
		function holaplex_disconnect_callback()
		{

			update_option('holaplex_api_key', '');
			update_option('holaplex_project', '');
			update_option('holaplex_org_id', '');
			// Example response
			$response = array('success' => true);

			wp_send_json($response);
		}
		add_action('wp_ajax_holaplex_disconnect', 'holaplex_disconnect_callback');
		add_action('wp_ajax_nopriv_holaplex_disconnect', 'holaplex_disconnect_callback');
	}




	public function register_ajax_route()
	{
	}

	private function login_to_holaplex()
	{
		$id = get_option('holaplex_org_id');
		$holaplex_api_key = get_option('holaplex_api_key');

		if (!$id || !$holaplex_api_key || empty($id) || empty($holaplex_api_key)) {
			return false;
		}

		$query = <<<'EOT'
		query getOrg($id: UUID!) {
			organization(id: $id) {
				projects {
					id
					name
					drops {
						id
						projectId
						creationStatus
						startTime
						endTime
						price
						createdAt
						shutdownAt
						collection {
							id
							supply
							totalMints
							metadataJson {
								id
								name
								image
								description
								symbol
							}
						}
						status
					}
				}
			}
		}
		EOT;

		$variables = [
			'id' => $id,
		];

		$core = new Holaplex_Core();
		$response = $core->send_graphql_request($query, $variables, $holaplex_api_key);

		if ($response) {
			$this->holaplex_status = '✅ connected';
			$this->holaplex_projects =  $response['data']['organization']['projects'];
		} else {
			$this->holaplex_status = '⛔ disconnected';
			$this->holaplex_projects = [];
		}
	}

	public function add_holaplex_menu()
	{

		add_filter('woocommerce_settings_tabs_array', 'holaplex_add_settings_tab', 50);

		function holaplex_add_settings_tab($tabs)
		{
			$tabs['holaplex_settings'] = __('Holaplex Hub', 'holaplex-wp');
			return $tabs;
		}

		add_action('woocommerce_settings_holaplex_settings', function () {
			$holaplex_projects = $this->holaplex_projects;
			$holaplex_status = $this->holaplex_status;

			$project_drops = [];
			foreach ($holaplex_projects as $project) {
				foreach ($project['drops'] as $drop) {
					$project_drops[$drop['id']] = $drop;
				}
			}

			// get all products with a drop_id
			$holaplex_products =  wc_get_products(array(
				'holaplex_drop_id' => 'EXISTS'
			));


			holaplex_woo_settings_page($holaplex_products, $holaplex_projects, $holaplex_status, $project_drops);
		});

		add_action('woocommerce_update_options_holaplex_settings', 'holaplex_woo_save_settings');


		function holaplex_woo_settings_page($holaplex_products, $holaplex_projects, $holaplex_status, $project_drops)
		{

			// check if there's an existing product with this dropid.
			// show a button to sync if there is, else show a button to import
			function showSyncActions($drop, $project_id)
			{
				$drop_id = $drop['id'];
				$drop_name = $drop['collection']['metadataJson']['name'];
				$drop_description = $drop['collection']['metadataJson']['description'];
				$drop_image = $drop['collection']['metadataJson']['image'];
				// check if a woocommerce product exist with a metakey "drop_id" and meta-value $drop_id
				$products = get_posts(array(
					'post_type' => 'product',
					'meta_key' => 'drop_id',
					'meta_value' => $drop_id,
					'numberposts' => 1
				));

				$nonce = wp_create_nonce(HOLAPLEX_NONCE);
				//if products exist, show "synced", else show import button
				if (count($products) > 0) {
					return '<span class="synced">Synced</span><button class="" id="remove-sync-btn">Remove</button>';
				} else {
					return '<button class="import-btn" data-project-id="'.esc_attr($project_id).'" data-drop-image="' . esc_attr($drop_image) . '" data-drop-name="' . esc_attr($drop_name) . '" data-drop-desc="' . esc_attr($drop_description) . '"  data-wp-nonce="' . esc_attr($nonce) . '" data-drop-id="' . esc_attr($drop_id) . '">Import</button>';
				}
			}

		?>
			<div class="bootstrap-wrapper">
				<?php
				include_once(HOLAPLEX_PLUGIN_PATH . 'admin/partials/holaplex-wp-admin-container.php');
				?>
				<div class="clear"></div>
	<?php
		}

		function holaplex_woo_save_settings()
		{
			$api_key = isset($_POST['holaplex_api_key']) ? sanitize_text_field($_POST['holaplex_api_key']) : '';
			$org_id = isset($_POST['holaplex_org_id']) ? sanitize_text_field($_POST['holaplex_org_id']) : '';
			$project = isset($_POST['holaplex_project']) ? sanitize_text_field($_POST['holaplex_project']) : '';

			update_option('holaplex_api_key', $api_key);
			update_option('holaplex_project', $project);
			update_option('holaplex_org_id', $org_id);

			header("Refresh:0");
		}
	}
}
