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
	private $holaplex_org_credits = 0;

	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->add_wc_products_drop_id_filter();
		$this->login_to_holaplex();
		$this->add_holaplex_menu();
		$this->init_ajax_sync_product_with_drop();
		$this->init_ajax_sync_drop_id_with_product();
		$this->init_ajax_remove_product_from_drop();
		$this->init_add_holaplex_customer_id_field();
		$this->init_save_holaplex_customer_id_field();
		$this->init_ajax_holaplex_disconnect();
		$this->init_ajax_holaplex_connect();
		$this->add_holaplex_menu_to_product_data_tabs();
		$this->init_shortcode_handler();
		$this->init_add_post_meta_options();
		$this->init_add_post_content_gate_meta_box();
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

	public function init_ajax_sync_drop_id_with_product()
	{

		function add_drop_id_to_product_callback()
		{
			// check nonce
			$nonce = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : '';
			if (!wp_verify_nonce($nonce, 'holaplex_sync_product_with_item')) {
				wp_send_json_error(null, 500);
			}
			$project_id = isset($_POST['project_id']) ? sanitize_text_field($_POST['project_id']) : null;
			$drop_id = isset($_POST['drop_id']) ? sanitize_text_field($_POST['drop_id']) : null;
			$post_id = isset($_POST['post_id']) ? sanitize_text_field($_POST['post_id']) : null;

			if (!$project_id || !$drop_id || !$post_id) {
				die('Missing project_id, post_id or drop_id');
				wp_send_json_error('Missing required params: project_id, post_id or drop_id', 500);
			}

			update_post_meta($post_id, 'holaplex_drop_id', $drop_id);
			update_post_meta($post_id, 'holaplex_project_id', $project_id);
		}

		add_action('wp_ajax_add_drop_id_to_product', 'add_drop_id_to_product_callback');
		add_action('wp_ajax_nopriv_add_drop_id_to_product', 'add_drop_id_to_product_callback');
	}

	public function add_holaplex_menu_to_product_data_tabs()
	{
		$holaplex_projects = $this->holaplex_projects;
		$holaplex_status = $this->holaplex_status;

		$project_drops = [];
		foreach ($holaplex_projects as $project) {
			if (!isset($project['drops']) || empty($project['drops'])) {
				continue;
			}
			foreach ($project['drops'] as $drop) {
				$drop['project_id'] = $project['id'];
				$project_drops[$drop['id']] = $drop;
			}
		}

		function woo_new_product_tab($tabs)
		{
			$tabs['holaplex_tab'] = array(
				'label' 	=> __('Holaplex Hub', 'holaplex-wp'),
				'target'  =>  'holaplex_menu_product_tab_content',
				'priority' => 60,
				'class'   => array()
			);

			return $tabs;
		}

		add_filter('woocommerce_product_data_tabs', 'woo_new_product_tab', 10, 1);
		add_action('woocommerce_product_data_panels', function () use ($project_drops, $holaplex_status, $holaplex_projects) {

			$current_drop_id = get_post_meta(get_the_ID(), 'holaplex_drop_id', true);
			$current_project_id = get_post_meta(get_the_ID(), 'holaplex_project_id', true);
			$nonce = wp_create_nonce('holaplex_sync_product_with_item');

?>

			<div id="holaplex_menu_product_tab_content" class="panel woocommerce_options_panel">
				<p class="form-field holaplex-status">
					<label for="holaplex_status">Holaplex Status</label>
					<span class="woocommerce-help-tip"></span>
					<input type="text" class="short" name="holaplex_status" id="holaplex_status" value="<?php echo esc_html($holaplex_status); ?>" readonly>
				</p>
				<!-- show a drop down list of holaplex drops -->
				<?php
				if ($current_drop_id && $current_drop_id !== '') {

					$project_ids = array_map(function ($project) {
						return $project['id'];
					}, $holaplex_projects);

					// check if holaplex_project_id is in project_ids
					if (!in_array($current_project_id, $project_ids)) {
						?>
						<p style="color: darkorange;" class="holaplex_tab_submit_data form-field">
							Organization / API Token mismatch. This drop does not belong to the organization associated with this API Token
						</p>
						<?php
					} else {
				?>
						<p class="holaplex_drop_id_field form-field">
							<label for="_stock_status">Drops</label>
							<span class="woocommerce-help-tip"></span>
							<select style="" id="_holaplex_drop_project_ids" name="_holaplex_drop_project_id" class="select short">
								<option value="">Select a drop</option>
								<?php foreach ($project_drops as $drop) {
									$drop_id = $drop['id'];
									$drop_name = $drop['collection']['metadataJson']['name'];
									$collection_supply = $drop['collection']['supply'] - $drop['collection']['totalMints'];
									$drop_status = $drop['status'];
									$drop_project_id = $drop['project_id'];

									// get all project ids 
									$project_ids = array_map(function ($project) {
										return $project['id'];
									}, $holaplex_projects);

									// check if holaplex_project_id is in project_ids
									if (!in_array($holaplex_project_id, $project_ids)) {
										continue;
									}
								?>

									<option <?php echo esc_attr($current_drop_id) === $drop_id ? 'selected' : null; ?> value="<?php echo esc_attr("$drop_id|$drop_project_id"); ?>"><?php echo esc_html("🖼️$drop_name -Supply: $collection_supply -Status: $drop_status"); ?></option>
								<?php } ?>
							</select>
						</p>
						<p class="holaplex_tab_submit_data form-field">
							<button data-wp-nonce="<?php echo esc_attr($nonce); ?>" type="button" class="button button-primary button-large" id="holaplex_tab_submit_data">Submit</button>
						</p>
				<?php

					}
				}
				?>
			</div>


		<?php

		});
	}

	public function init_ajax_remove_product_from_drop()
	{
		function remove_product_with_id_callback()
		{
			// handle nonce
			$nonce = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : '';
			if (!wp_verify_nonce($nonce, HOLAPLEX_NONCE)) {
				wp_send_json_error(null, 500);
			}
			$product_id = isset($_POST['product_id']) ? sanitize_text_field($_POST['product_id']) : null;

			// delete or move product to trash 
			wp_trash_post($product_id);


			wp_send_json_success();
		}
		add_action('wp_ajax_remove_product_with_product_id', 'remove_product_with_id_callback');
		add_action('wp_ajax_nopriv_remove_product_with_product_id', 'remove_product_with_id_callback');
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


	public function init_ajax_sync_product_with_drop()
	{

		function imageTypeBasedOnHeaders($url)
		{

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
		function getFileExtensionByMimeType($mimeType)
		{
			$extensions = [
				'image/jpeg' => 'jpg',
				'image/png' => 'png',
				'image/gif' => 'gif',
				'image/bmp' => 'bmp',
				'image/webp' => 'webp'
				// Add more common image formats and their corresponding extensions here
			];

			// Check if the MIME type exists in the array
			if (isset($extensions[$mimeType])) {
				return $extensions[$mimeType];
			}

			return null; // Return null if no matching extension found
		}


		function add_product_with_drop_id_callback()
		{
			$nonce = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : '';
			if (!wp_verify_nonce($nonce, HOLAPLEX_NONCE)) {
				wp_send_json_error(null, 500);
			}
			// sanitize the input
			$drop_id =  isset($_POST['drop_id']) ? sanitize_text_field($_POST['drop_id']) : '';
			$drop_name =  isset($_POST['drop_name']) ? sanitize_text_field($_POST['drop_name']) : '';
			$drop_image =  isset($_POST['drop_image']) ? sanitize_text_field($_POST['drop_image']) : '';
			$drop_description = isset($_POST['drop_desc']) ? sanitize_text_field($_POST['drop_desc']) : '';
			$drop_project_id = isset($_POST['project_id']) ? sanitize_text_field($_POST['project_id']) : '';
			// create a product with price

			$product = new WC_Product_Simple();

			$product->set_name($drop_name); // product title

			$product->set_regular_price(0.01); // in current shop currency

			$product->set_short_description($drop_description);
			// you can also add a full product description
			$product->set_description($drop_description);

			// $product->set_image_id( 90 );

			$product->save();

			// insert the product
			$product_id = $product->get_id();

			// add drop_id to product meta
			update_post_meta($product_id, 'holaplex_drop_id', $drop_id);
			update_post_meta($product_id, 'holaplex_project_id', $drop_project_id);
			update_post_meta($product_id, '_regular_price', "0.01");


			// add drop_image to product thumbnail	
			$upload_dir = wp_get_upload_dir();
			$image_data = imageTypeBasedOnHeaders($drop_image);
			// get file extension from image data
			$file_ext = getFileExtensionByMimeType($image_data['contentType']);
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
			);

			$attach_id = wp_insert_attachment($attachment, $file, $product_id);
			$attach_data = wp_generate_attachment_metadata($attach_id, $file);
			// Assign metadata to attachment
			wp_update_attachment_metadata($attach_id, $attach_data);
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

			$custom_text_field = isset($_POST['holaplex_custom_text']) ? wp_kses_post($_POST['holaplex_custom_text']) : '';
			$excerpt_length_field = isset($_POST['holaplex_excerpt_length']) ? intval($_POST['holaplex_excerpt_length']) : '';
			$fading_excerpt_info_field = isset($_POST['holaplex_fading_excerpt_info']) ? sanitize_key($_POST['holaplex_fading_excerpt_info']) : '';

			update_option('holaplex_custom_text', $custom_text_field);
			update_option('holaplex_excerpt_length', $excerpt_length_field);
			update_option('holaplex_fading_excerpt_info', $fading_excerpt_info_field);

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
				credits {
					id
					balance
				}
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
			$this->holaplex_org_credits = $response['data']['organization']['credits']['balance'];
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
			$holaplex_credits = $this->holaplex_org_credits;

			$core = new Holaplex_Core();
			$holaplex_display_custom_text = $core->holaplex_display_custom_text();
			$holaplex_excerpt_length = $core->holaplex_excerpt_length();

			$project_drops = [];
			foreach ($holaplex_projects as $project) {
				if (!isset($project['drops']) || empty($project['drops'])) {
					continue;
				}
				foreach ($project['drops'] as $drop) {
					$project_drops[$drop['id']] = $drop;
				}
			}

			// get all products with a drop_id
			$holaplex_products =  wc_get_products(array(
				'holaplex_drop_id' => 'EXISTS'
			));


			holaplex_woo_settings_page(
				$holaplex_products,
				$holaplex_projects,
				$holaplex_status,
				$project_drops,
				$holaplex_display_custom_text,
				$holaplex_excerpt_length,
				$holaplex_credits
			);
		});

		add_action('woocommerce_update_options_holaplex_settings', 'holaplex_woo_save_settings');


		function holaplex_woo_settings_page(
			$holaplex_products,
			$holaplex_projects,
			$holaplex_status,
			$project_drops,
			$holaplex_display_custom_text,
			$holaplex_excerpt_length,
			$holaplex_credits
		) {

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
					return '<button class="import-btn" data-project-id="' . esc_attr($project_id) . '" data-drop-image="' . esc_attr($drop_image) . '" data-drop-name="' . esc_attr($drop_name) . '" data-drop-desc="' . esc_attr($drop_description) . '"  data-wp-nonce="' . esc_attr($nonce) . '" data-drop-id="' . esc_attr($drop_id) . '">Import</button>';
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

				$nonce = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : '';
				if (!wp_verify_nonce($nonce, 'holaplex_sync_product_with_item')) {
					wp_send_json_error(null, 500);
				}

				$api_key = isset($_POST['holaplex_api_key']) ? sanitize_text_field($_POST['holaplex_api_key']) : '';
				$org_id = isset($_POST['holaplex_org_id']) ? sanitize_text_field($_POST['holaplex_org_id']) : '';
				$project = isset($_POST['holaplex_project']) ? sanitize_text_field($_POST['holaplex_project']) : '';

				$custom_text_field = isset($_REQUEST['holaplex_custom_text']) ? wp_kses_post($_REQUEST['holaplex_custom_text']) : '';
				$excerpt_length_field = isset($_REQUEST['holaplex_excerpt_length']) ? intval($_REQUEST['holaplex_excerpt_length']) : '';
				$fading_excerpt_info_field = isset($_REQUEST['holaplex_fading_excerpt_info']) ? sanitize_key($_REQUEST['holaplex_fading_excerpt_info']) : '';


				update_option('holaplex_custom_text', $custom_text_field);
				update_option('holaplex_excerpt_length', $excerpt_length_field);
				update_option('holaplex_fading_excerpt_info', $fading_excerpt_info_field);

				update_option('holaplex_api_key', $api_key);
				update_option('holaplex_project', $project);
				update_option('holaplex_org_id', $org_id);

				header("Refresh:0");
			}
		}

		public function init_shortcode_handler()
		{
			/**
			 * shortcode to display content only if the user has purchased the product with required ID
			 */
			function holaplex_show_content($atts = [], $content = null)
			{
				global $post;
				$atts = array_change_key_case((array) $atts, CASE_LOWER);
				$core = new Holaplex_Core();

				$output = '';
				$output .= '<div class="holaplex-box">';

				$current_user = wp_get_current_user();

				if (current_user_can('administrator') || wc_customer_bought_product($current_user->email, $current_user->ID, $atts['id'])) {

					// // if the selected product id for the snippet option is different from the product id in the shortcode
					if (!current_user_can('administrator') && $post->holaplex_product_select && $post->holaplex_meta_info && 'hide_excerpt_meta' === $post->holaplex_meta_info && $atts['id'] !== $post->holaplex_product_select) {
						$custom_text = $core->holaplex_display_custom_text();
						$output .= $custom_text;
					} else {
						if (!is_null($content)) {
							// protects output via content variable
							$output .= apply_filters('the_content', $content);
						}
					}
				} else {
					// if user has not purchased product & is not admin
					$custom_text = $core->holaplex_display_custom_text();
					$output .= $custom_text;
				}

				$output .= '</div>';

				return $output;
			}


			function holaplex_shortcodes_init()
			{
				add_shortcode('holaplexcode', 'holaplex_show_content');
			}

			add_action('init', 'holaplex_shortcodes_init');
		}

		public function init_add_post_content_gate_meta_box()
		{
			// add_action('add_meta_boxes', 'holaplex_post_options_metabox');
			add_action( 'load-post.php', 'holaplex_post_options_metabox' );
			add_action( 'load-post-new.php', 'holaplex_post_options_metabox' );

			// add_action('admin_init', 'holaplex_post_options_metabox', 1);
			/**
			 *  adding our custom fields
			 *
			 */
			function holaplex_post_options_metabox()
			{
				add_meta_box('post_options', __('Holaplex Content Gate options', 'holaplex-wp'), 'holaplex_post_options_code', array('post', 'page'), 'normal', 'high');
			}

			/**
			 *  Display field options
			 */
			function holaplex_post_options_code($post)
			{
				include_once(HOLAPLEX_PLUGIN_PATH . 'admin/partials/holaplex-wp-admin-post-meta.php');
			}


			/**
			 * dropdown list with selection of products available
			 */
			function holaplex_products_dropdown($post, $holaplex_selected_product_id)
			{

				$holaplex_products =  wc_get_products(array(
					'holaplex_drop_id' => 'EXISTS'
				));

				if (!empty($holaplex_products)) {
					echo '<select name="holaplex_product_select" class="select short">';

					foreach ($holaplex_products as $product) {
				?>
						<option <?php echo $product->get_id() == $holaplex_selected_product_id ? "selected" : 'selected="false"'  ?> value="<?php echo esc_attr($product->get_id()); ?>"><?php echo esc_html($product->get_name() . $product->get_id() . '-' . $holaplex_selected_product_id ); ?></option>
	<?php

					}
					echo '</select>';
				} else {
					echo '<option value="">No drops</option>';
				}
			}
		}

		public function init_add_post_meta_options()
		{
			add_action('save_post', 'holaplex_save_post_options', 20, 1);

			function holaplex_save_post_options($post_id)
			{
				if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
					return;
				}

				$post_type = isset($_POST['post_type']) ? sanitize_text_field($_POST['post_type']) : '';
				$noncename = $post_type . '_noncename';
				$nonce = isset($_POST[$noncename]) ? sanitize_text_field($_POST[$noncename]) : '';

				// verify it's coming from the right place
				// if (!wp_verify_nonce($nonce, HOLAPLEX_NONCE)) {
				// 	return;
				// }

				// // check if they have permissions
				if (!current_user_can('edit_post', $post_id)) {
					return;
				}
				// if authorized, finding and saving the data
				if ('post' === $_POST['post_type'] || 'page' === $_POST['post_type']) {
					if (!current_user_can('edit_post', $post_id)) {
						return;
					} else {
						
						$meta_info_field = isset($_POST['holaplex_meta_info']) ? sanitize_key($_POST['holaplex_meta_info']) : '';
						$product_select_field = isset($_POST['holaplex_product_select']) ? sanitize_text_field($_POST['holaplex_product_select']) : '';
						$selected_page_id_field = isset($_POST['holaplex_selected_page_id']) ? sanitize_text_field($_POST['holaplex_selected_page_id']) : '';
						
						// hookbug( $product_select_field);
						// hookbug($meta_info_field);
						// hookbug($selected_page_id_field);	
						// hookbug(wp_json_encode($_POST));

						update_post_meta($post_id, 'holaplex_meta_info', $meta_info_field);
						update_post_meta($post_id, 'holaplex_product_select', $product_select_field);
						update_post_meta($post_id, 'holaplex_selected_page_id', $selected_page_id_field);
					}
				}
			}
		}
	}
