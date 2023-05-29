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

	private $holaplex_status = null;
	private $holaplex_projects = [];

	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->add_holaplex_menu();
		$this->login_to_holaplex();
		$this->handle_ajax_sync_product_with_item();
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
		wp_enqueue_script( 'holaplex-ajax-admin' , plugin_dir_url(__FILE__) . 'js/holaplex-ajax-admin.js', array('jquery'), $this->version, false);

    wp_localize_script('holaplex-ajax-admin', 'holaplex_wp_ajax', array('ajax_url' => admin_url('admin-ajax.php')));
	}


	public function handle_ajax_sync_product_with_item () {
		function add_product_with_drop_id_callback() {
				$drop_id = $_POST['drop_id'];
				
				// Call your add_product_with_drop_id function here with the $drop_id
				
				// Example response
				$response = array('success' => true);
				
				wp_send_json($response);
		}
		add_action('wp_ajax_add_product_with_drop_id', 'add_product_with_drop_id_callback');
		add_action('wp_ajax_nopriv_add_product_with_drop_id', 'add_product_with_drop_id_callback');
	
	}


	private function send_graphql_request($query, $variables = [])
	{
		$api_url = 'https://api.holaplex.com/graphql';  // API endpoint URL

		$headers = [
			'Content-Type: application/json',
			'Accept: application/json',
			'Authorization: '. get_option('holaplex_api_key'),
			'Accept-Encoding: gzip, deflate, br',
			'Connection: keep-alive',
			'DNT: 1',
			'Origin: file://'
		];

		$data = [
			'query' => $query,
			'variables' => $variables,
		];

		$curl = curl_init($api_url);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

		$response = curl_exec($curl);
		$status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		if (curl_errno($curl)) {
			$error_message = curl_error($curl);
			// Handle the error here
			return null;
		}

		curl_close($curl);
		
		$decoded_response = gzdecode($response);
		$json_response = json_decode($decoded_response, true);

		if ($status_code === 200) {
			$decoded_response = gzdecode($response);
			$json_response = json_decode($decoded_response, true);
			// Handle the successful response here
			return $json_response;
		} elseif ($status_code === 401) {
			// Handle unauthorized access here
			return null;
		} else {
			// Handle other status codes here
			$decoded_response = gzdecode($response);
			return json_decode($decoded_response, true);
		}
	}

	public function register_ajax_route () {

	}

	private function login_to_holaplex()
	{
		$id = get_option('holaplex_org_id');
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
							supply
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

		// var_dump('response');
		$response = $this->send_graphql_request($query, $variables);

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
			holaplex_woo_settings_page($holaplex_projects, $holaplex_status);
		});

		add_action('woocommerce_update_options_holaplex_settings', 'holaplex_woo_save_settings');
		

		function holaplex_woo_settings_page($holaplex_projects, $holaplex_status)
		{

			function showSyncActions($drop_id) {
				// check if a woocommerce product exist with a metakey "drop_id" and meta-value $drop_id
				$products = get_posts( array(
					'post_type' => 'product',
					'meta_key' => 'drop_id',
					'meta_value' => $drop_id,
					'numberposts' => 1
				) );
				//if products exist, show "synced", else show sync button
				if (count($products) > 0) {
					return '<span class="synced">Synced</span>';
				} else {
					return '<button id="sync-btn" data-drop-id="' . esc_attr($drop_id) . '">Sync</button>';
				}

			}

?>
			<div class="container holaplex-app">
				<section>
					<h2><?php _e('Holaplex Settings', 'holaplex-wp'); ?></h2>
					<table class="form-table">
						<tbody>
							<tr valign="top">
								<th scope="row"><?php _e('Connection Status', 'holaplex-wp'); ?></th>
								<td>
									<input readonly type="text" name="holaplex_connection_status" value="<?php echo esc_attr($holaplex_status); ?>">
									<p class="description"><?php _e('Enter the connection status', 'holaplex-wp'); ?></p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e('Organization Id', 'holaplex-wp'); ?></th>
								<td>
									<input type="text" name="holaplex_org_id" value="<?php echo esc_attr(get_option('holaplex_org_id')); ?>">
									<p class="description"><?php _e('Enter Organization Id', 'holaplex-wp'); ?></p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e('API Token', 'holaplex-wp'); ?></th>
								<td>
									<input type="text" name="holaplex_api_key" value="<?php echo esc_attr(get_option('holaplex_api_key')); ?>">
									<p class="description"><?php _e('Enter the API Token', 'holaplex-wp'); ?></p>
								</td>
							</tr>
						</tbody>
					</table>
				</section>
				<section>
					<?php
						// if no projects, show a message and return
						if (empty($holaplex_projects)) {
							echo "<h2 class='help-title'>".esc_html(__('Setup Help', 'holaplex-wp'))."</h2>";
							echo "<p class='description help-mesg'>
							To connect to Holaplex Hub, enter an API token and associated Organization ID below. <br/>
							An API token can be generated on the Credentials tab of your Organization's page on Hub: <a target='_blank' href='https://hub.holaplex.com/credentials'>https://hub.holaplex.com/credentials</a>. <br/>
							You can find your Organization ID by clicking the menu button in the upper left corner, next to your organization's name. <br/>
							For more info, please see <a  target='_blank' href='https://docs.holaplex.com/category/guides/woocommerce-plugin' > https://docs.holaplex.com/category/guides/woocommerce-plugin</a><br/>
							If you do not already have a Holaplex Hub account, you can create one at <a target='_blank' href='https://hub.holaplex.com/'>https://hub.holaplex.com/</a><br/>
							
							</p>";
							return;
						}
					?>	
					<h2><?php _e('Project Drops', 'holaplex-wp'); ?></h2>
					<p class="description">
						Creates a product for each drop in your Holaplex projects.
					</p>

					<ul class="responsive-table">
						<li class="table-header">
							<div class="col-1">Project</div>
							<div class="col-2">Drop name</div>
							<div class="col-1">Supply</div>
							<div class="col-1">Status</div>
							<div class="col-1">Sync</div>
						</li>
						<!-- loop through project drops -->
						<?php foreach ($holaplex_projects as $project) {
								foreach ($project['drops'] as $drop) {
									$project_name = $project['name'];
									$drop_id = substr($drop['id'], -6);
									$collection_supply = $drop['collection']['supply'];
									$drop_status = $drop['status'];
									
									echo '<li class="table-row">';
									echo '<div class="col-1">' . esc_html($project_name) . '</div>';
									echo '<div class="col-2">' . esc_html($drop_id) . '</div>';
									echo '<div class="col-1">' . esc_html($collection_supply) . '</div>';
									echo '<div class="col-1">' . esc_html($drop_status) . '</div>';
									// echo '<div class="col-1">' . esc_html($drop_status) . '</div>';
									echo '<div class="col-1">'. showSyncActions($drop['id']) .'</div>';
									echo '</li>';
								}
							}
							?>
					</ul>
				</section>
			</div>
			<div class="clear"></div>
<?php
		}

		function holaplex_woo_save_settings()
		{
			$connection_status = isset($_POST['holaplex_connection_status']) ? sanitize_text_field($_POST['holaplex_connection_status']) : '';
			$api_key = isset($_POST['holaplex_api_key']) ? sanitize_text_field($_POST['holaplex_api_key']) : '';
			$org_id = isset($_POST['holaplex_org_id']) ? sanitize_text_field($_POST['holaplex_org_id']) : '';
			$project = isset($_POST['holaplex_project']) ? sanitize_text_field($_POST['holaplex_project']) : '';

			update_option('holaplex_connection_status', $connection_status);
			update_option('holaplex_api_key', $api_key);
			update_option('holaplex_project', $project);
			update_option('holaplex_org_id', $org_id);
		}
	}
}
