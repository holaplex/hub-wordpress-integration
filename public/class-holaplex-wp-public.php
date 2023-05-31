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

		$this->init_display_holaplex_customer_details_on_profile();
		$this->init_create_customer_wallet_callback();
		$this->init_create_new_wallet_callback();
		$this->init_remove_customer_wallet_callback();
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


	public function init_display_holaplex_customer_details_on_profile()
	{
		function holaplex_customer_details_shortcode($atts)
		{
			$current_user = wp_get_current_user();
			$holaplex_customer_id = get_user_meta($current_user->ID, 'holaplex_customer_id', true);

			if (empty($holaplex_customer_id) || $holaplex_customer_id == '' || !$holaplex_customer_id) {
		?>
				<div class="holaplex-app">
					<div class="holaplex-app__header">
						<div class="holaplex-app__header__title">
							<h4><?php echo esc_html(__('Holaplex Customer Details', 'holaplex-wp')); ?></h4>
							<p>Create a new wallet linked to your account. This will make it easy for you to mint drops.</p>
						</div>
					</div>
					<div class="holaplex-app__body">
						<div class="holaplex-app__body__content">
							<div class="holaplex-app__body__content__section">
								<div class="holaplex-app__body__content__section__content">
									<p><button id="create-customer-button" class="btn add-btn btn-lg btn-block"><?php echo esc_html(__('Create Customer and Wallet', 'holaplex-wp')); ?></button></p>
								</div>
							</div>
						</div>
					</div>
			<?php
				return;
			}

			$project_id = get_option('holaplex_project');
			$holaplex_api_key = get_option('holaplex_api_key');

			$query = <<<'EOT'
			query getCustomerWallet($project_id: UUID!, $customer_id: UUID!) {
				project(id: $project_id) {
					name
					customer(id: $customer_id) {
						addresses
					}
				}
			}
			EOT;

			$variables = [
				'customer_id' =>  $holaplex_customer_id,
				'project_id' => $project_id
			];


			$core = new Holaplex_Core();
			$wallet_response = $core->send_graphql_request($query, $variables, $holaplex_api_key);

			if (!$wallet_response) {
				return;
			}

			if (isset($wallet_response['data']['project']['customer']['addresses']) && !empty($wallet_response['data']['project']['customer']['addresses'])) {
				$customer_wallet = $wallet_response['data']['project']['customer']['addresses'][0];
			} else {
				$customer_wallet = '';
			}



			if (!empty($holaplex_customer_id)) {
				?>

					<div class="holaplex-app">
						<div class="holaplex-app__header">
							<div class="holaplex-app__header__title">
								<h4><?php echo esc_html(__('Holaplex Customer Details', 'holaplex-wp')); ?></h3>
									<p>Your wallet is connected to your account. This will make it easy for you to mint drops.</p>
							</div>
						</div>
						<div class="holaplex-app__body">
							<div class="holaplex-app__body__content">
								<ul class="responsive-table">
									<li class="table-row">
										<div class="col-2"><?php echo esc_html(__('Customer ID', 'holaplex-wp')); ?></div>
										<div class="col-3">...<?php echo esc_html(substr($holaplex_customer_id, -8)); ?></div>
										<div class="col-1">
											<button id="remove-customer-button" class="btn remove-btn">Delete</button>
										</div>
									</li>
									<li class="table-row">
										<div class="col-2"><?php echo esc_html(__('Wallet Address', 'holaplex-wp')); ?></div>
										<div class="col-3">...<?php echo esc_html(substr($customer_wallet, -8)); ?></div>
										<div class="col-1">
											<?php
											if ($customer_wallet === '') {  ?>
												<button id="create-wallet-button" class="btn add-btn">Create Wallet</button>
											<?php }  ?>
										</div>
									</li>
								</ul>

							</div>
						</div>
					</div>

				<?php

			} else {

				?>

	<?php

			}
		}

		add_action('woocommerce_edit_account_form', 'holaplex_customer_details_shortcode');
	}

	public function init_create_customer_wallet_callback()
	{
		function create_customer_wallet_callback()
		{
			// Call your create_customer_wallet function here
			$create_customer_query = <<<'EOT'
				mutation CreateCustomer($input: CreateCustomerInput!) {
					createCustomer(input: $input) {
						customer {
							id
						}
					}
				}
				EOT;

			$create_customer_variables = [
				'input' => [
					'project' => get_option('holaplex_project'),
				],
			];
			$core = new Holaplex_Core();
			$response = $core->send_graphql_request($create_customer_query, $create_customer_variables, get_option('holaplex_api_key'));

			// save customer_id to user meta
			$current_user = get_current_user_id();
			$customer_id = $response['data']['createCustomer']['customer']['id'];

			update_user_meta($current_user, 'holaplex_customer_id', $customer_id);

			$create_wallet_query = <<<'EOT'
				mutation CreateCustomerWallet($input: CreateCustomerWalletInput!) {
					createCustomerWallet(input: $input) {
						wallet {
							address
						}
					}
				}
				EOT;

			$create_wallet_variables = [
				'input' => [
					'customer' => $customer_id,
					"assetType" => "SOL"
				],
			];

			$core = new Holaplex_Core();
			$response = $core->send_graphql_request($create_wallet_query, $create_wallet_variables, get_option('holaplex_api_key'));


			// Example response
			$response = array('success' => true);

			wp_send_json($response);
		}
		add_action('wp_ajax_create_customer_wallet', 'create_customer_wallet_callback');
		add_action('wp_ajax_nopriv_create_customer_wallet', 'create_customer_wallet_callback');
	}

	public function init_create_new_wallet_callback()
	{
		function create_new_wallet_callback()
		{


			$current_user = wp_get_current_user();
			$holaplex_customer_id = get_user_meta($current_user->ID, 'holaplex_customer_id', true);


			$create_wallet_query = <<<'EOT'
				mutation CreateCustomerWallet($input: CreateCustomerWalletInput!) {
					createCustomerWallet(input: $input) {
						wallet {
							address
						}
					}
				}
				EOT;

			$create_wallet_variables = [
				'input' => [
					'customer' => $holaplex_customer_id,
					"assetType" => "SOL"
				],
			];

			$core = new Holaplex_Core();
			$response = $core->send_graphql_request($create_wallet_query, $create_wallet_variables, get_option('holaplex_api_key'));

			// Example response
			$response = array('success' => true);

			wp_send_json($response);
		}
		add_action('wp_ajax_create_new_wallet', 'create_new_wallet_callback');
		add_action('wp_ajax_nopriv_create_new_wallet', 'create_new_wallet_callback');
	}

	public function init_remove_customer_wallet_callback()
	{
		function remove_customer_wallet_callback()
		{

			// save customer_id to user meta
			$current_user = get_current_user_id();

			update_user_meta($current_user, 'holaplex_customer_id', '');

			// Example response
			$response = array('success' => true);

			wp_send_json($response);
		}
		add_action('wp_ajax_remove_customer_wallet', 'remove_customer_wallet_callback');
		add_action('wp_ajax_nopriv_remove_customer_wallet', 'remove_customer_wallet_callback');
	}
}
