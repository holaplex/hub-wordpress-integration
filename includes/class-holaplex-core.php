<?php



class Holaplex_Core
{

  public function holaplex_display_custom_text()
  {
    if (get_option("holaplex_custom_text")) {
      $custom_text = get_option("holaplex_custom_text");
    } else {
      $hidden_content_text = __('Purchase this item to view this hidden content', 'holaplex-wp');
      $custom_text = '<div class="holaplex_custom_text"><p>' . $hidden_content_text . '</p></div>';
    }
    return $custom_text;
  }

  public function holaplex_excerpt_length()
  {
    if (get_option("holaplex_excerpt_length")) {
      $excerpt_length = get_option("holaplex_excerpt_length");
    } else {
      $excerpt_length = 45;
    }
    return $excerpt_length;
  }

  public function send_graphql_request($query, $variables = [], $holaplex_api_key)
  {


    $holaplex_api_key = $holaplex_api_key ?? get_option('holaplex_api_key');
    $api_url = 'https://api.holaplex.com/graphql';  // API endpoint URL

    $headers = [
      'Content-Type' => 'application/json',
      'Accept' => 'application/json',
      'Authorization' => '' . $holaplex_api_key,
      'Accept-Encoding' => 'gzip, deflate, br',
      'Connection' => 'keep-alive',
      'DNT' => '1',
      'Origin' => 'file://'
    ];

    $data = [
      'query' => $query,
      'variables' => $variables,
    ];


    $body = wp_json_encode($data);

    $args = array(
      'headers' => $headers,
      'body' => $body,
    );

    $response = wp_remote_post($api_url, $args);

    if (is_wp_error($response)) {
      // Handle the error
      return false;
    }

    $response_code = wp_remote_retrieve_response_code($response);
    $response_body = wp_remote_retrieve_body($response);
    // Handle the response
    if ((int)$response_code === 200) {

      // Successful response
      $data = json_decode($response_body, true);
      return $data;
    } else {
      // Error response
      // Handle the error
      if (is_admin()) {

        add_action('admin_notices', function () {
          $class = 'notice notice-error';
          $message = __('There’s a problem with the Organization ID or API Token that you’ve entered. Please update these values.', 'holaplex-wp');

          printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
        });
      }
      return false;
    }
  }

  public function mint_drop($holaplex_customer_wallet_address, $holaplex_drop_id)
  {
    $holaplex_api_key = get_option('holaplex_api_key');

    $query = <<<'EOT'
    mutation MintNft($input: MintDropInput!) {
      mintEdition(input: $input) {
        collectionMint {
          address
          owner
        }
      }
    }
    EOT;

    $variables = [
      'input' => [
        'drop' =>  $holaplex_drop_id,
        'recipient' => $holaplex_customer_wallet_address
      ]
    ];
    $wallet_response = $this->send_graphql_request($query, $variables, $holaplex_api_key);

    return $wallet_response;
  }

  public function ensure_wallet_or_create_recursively($holaplex_project, $holaplex_project_id, $count = 0)
  {
    $holaplex_project_customer_wallet = $holaplex_project[$holaplex_project_id] ?? [];
    // check if $holaplex_project_customer_wallet has valid values for customer_id and wallet_address
    // if not, create a new wallet and return the new wallet address
    if (empty($holaplex_project_customer_wallet['customer_id']) || empty($holaplex_project_customer_wallet['wallet_address'])) {
      
      $customer_id = !empty($holaplex_project_customer_wallet['customer_id']) ? $holaplex_project_customer_wallet['customer_id'] : '';

      $holaplex_project_customer_wallet = $this->create_customer_wallet($holaplex_project_id, $customer_id);

      // check if values are valid, if not recursively call this function again after a timeout of 1 second
      if (empty($holaplex_project_customer_wallet['customer_id']) || empty($holaplex_project_customer_wallet['wallet_address'])) {
        sleep(2);
        if ($count < 2) {
          $count++;
          $this->ensure_wallet_or_create_recursively($holaplex_project, $holaplex_project_id, $count);
          return;
        } else {
          hookbug("Holaplex: Unable to create customer wallet after $count attempts");
          return;
        }
      }
      $holaplex_project[$holaplex_project_id] = $holaplex_project_customer_wallet;
      update_user_meta( get_current_user_id(), 'holaplex_customer_id', json_encode($holaplex_project));
      return $holaplex_project_customer_wallet;
    } else {
      return $holaplex_project_customer_wallet;
    }
  }

  public function create_customer_wallet($holaplex_project_id, $holaplex_project_customer_id = '')
  {

    if (empty($holaplex_project_customer_id)) {

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
          'project' => $holaplex_project_id,
        ],
      ];
      $core = new Holaplex_Core();
      $response = $core->send_graphql_request($create_customer_query, $create_customer_variables, get_option('holaplex_api_key'));
      // hookbug($response);
      // hookbug("Create Customer Response");
      // save customer_id to user meta
      $customer_id = $response['data']['createCustomer']['customer']['id'];
    } else {
      $customer_id = $holaplex_project_customer_id;
    }


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
    // hookbug($response);
    // hookbug("Create Wallet Response");
    $wallet_address = $response['data']['createCustomerWallet']['wallet']['address'];

    // Example response
    $response = array(
      'customer_id' => $customer_id,
      'wallet_address' => $wallet_address
    );


    return $response;
  }

  public function get_customer_nfts()
  {

    $holaplex_api = new Holaplex_Core();
    $holaplex_api_key = get_option('holaplex_api_key');
    $holaplex_customer_data = get_user_meta(get_current_user_id(), 'holaplex_customer_id', true);

    $project_id_array = json_decode($holaplex_customer_data, true);

    $get_customer_query = <<<'EOT'
    query GetCustomerNfts($project: UUID!, $customer: UUID!) {
        project(id: $project) {
            id
            customer(id: $customer) {
                mints {
                    id
                    address
                    createdAt
                    collectionId
                    collection {
                        id
                        blockchain
                        metadataJson {
                            id
                            name
                            description
                            image
                            externalUrl
                            }
                    }
                }
            }
        }
    }
    EOT;

    // loop through $project_id_array and send a request for each project
    $response = array();
    foreach ($project_id_array as $project_id => $customer) {

      $get_customer_variables = [
        'project' => $project_id,
        'customer' => $customer['customer_id'],
      ];
      $response[] = $holaplex_api->send_graphql_request($get_customer_query, $get_customer_variables, $holaplex_api_key);
    }

    return $response;
  }
}
