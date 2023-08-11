<?php



class Holaplex_Core
{

  public $holaplex_status = '⛔ disconnected';
	public $holaplex_projects = [];
	public $holaplex_org_credits = 0;

  public $mint_cart_id = "";

  public function __construct() {
    $this->login_to_holaplex();
  }


  public function login_to_holaplex()
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

		$response = $this->send_graphql_request($query, $variables, $holaplex_api_key);

		if ($response) {
			$this->holaplex_status = '✅ connected';
			$this->holaplex_projects =  $response['data']['organization']['projects'];
			$this->holaplex_org_credits = $response['data']['organization']['credits']['balance'];
		} else {
			$this->holaplex_status = '⛔ disconnected';
			$this->holaplex_projects = [];
		}
	}

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
      hookbug($response_body);
      return false;
    }
  }

  public function mint_drop($holaplex_customer_wallet_address, $holaplex_drop_id, $quantity = 1)
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

    // send request $quantity times
    for ($i = 0; $i < $quantity; $i++) {
      $wallet_response = $this->send_graphql_request($query, $variables, $holaplex_api_key);
      sleep(1);
    }


    return $wallet_response;
  }

  public function ensure_wallet_or_create_recursively($holaplex_project, $holaplex_project_id, $asset_type, $count = 0)
  {
    $holaplex_project_customer_wallet = $holaplex_project[$holaplex_project_id] ?? [];
    // check if $holaplex_project_customer_wallet has valid values for customer_id and wallet_address
    // if not, create a new wallet and return the new wallet address
    if (empty($holaplex_project_customer_wallet['customer_id']) || empty($holaplex_project_customer_wallet['wallet_address'])) {

      $customer_id = !empty($holaplex_project_customer_wallet['customer_id']) ? $holaplex_project_customer_wallet['customer_id'] : '';

      $holaplex_project_customer_wallet = $this->create_customer_wallet($holaplex_project_id, $customer_id, $asset_type);

      // check if values are valid, if not recursively call this function again after a timeout of 1 second
      if (empty($holaplex_project_customer_wallet['customer_id']) || empty($holaplex_project_customer_wallet['wallet_address'])) {
        sleep(2);
        if ($count < 2) {
          $count++;
          $this->ensure_wallet_or_create_recursively($holaplex_project, $holaplex_project_id, $asset_type, $count);
          return;
        } else {
          hookbug("Holaplex: Unable to create customer wallet after $count attempts");
          return;
        }
      }
      $holaplex_project[$holaplex_project_id] = $holaplex_project_customer_wallet;
      update_user_meta( get_current_user_id(), 'holaplex_customer_id', wp_json_encode($holaplex_project));
      return $holaplex_project_customer_wallet;
    } else {
      return $holaplex_project_customer_wallet;
    }
  }

  public function create_customer_wallet($holaplex_project_id, $holaplex_project_customer_id = '', $asset_type)
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

      $response = $this->send_graphql_request($create_customer_query, $create_customer_variables, get_option('holaplex_api_key'));

      // save customer_id to user meta
      $customer_id = $response['data']['createCustomer']['customer']['id'];
    } else {
      $customer_id = $holaplex_project_customer_id;
    }

    sleep(3);

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
        "assetType" => $asset_type
      ],
    ];

    $wallet_response = $this->send_graphql_request($create_wallet_query, $create_wallet_variables, get_option('holaplex_api_key'));

    $wallet_address = $wallet_response['data']['createCustomerWallet']['wallet']['address'];
    hookbug($create_wallet_variables);

    // Example response
    $response = array(
      'customer_id' => $customer_id,
      'wallet_address' => $wallet_address
    );

    return $response;
  }

  public function get_customer_nfts()
  {

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
      $response[] = $this->send_graphql_request($get_customer_query, $get_customer_variables, $holaplex_api_key);
    }

    return $response;
  }

  public function get_drop($project_id, $drop_id)
  {
    $holaplex_api_key = get_option('holaplex_api_key');

    $get_drop_query = <<<'EOT'
        query GetDrop($project: UUID!, $drop: UUID!) {
          project(id: $project) {
            id
            treasury {
              wallets {
                address
                assetId
            }
          }
          drop(id: $drop) {
            id
            price
            status
            createdAt
            startTime
            endTime
            collection {
              id
              supply
              address
              totalMints
              blockchain
              signature
              sellerFeeBasisPoints
              creators {
                address
                verified
                share
              }
              metadataJson {
                id
                name
                image
                description
                symbol
                externalUrl
                animationUrl
                attributes {
                  id
                  traitType
                  value
                }
              }
            }
          }
      }
    }
    EOT;


    $get_drop_variables = [
      'project' => $project_id,
      'drop' => $drop_id,
    ];

    $response = $this->send_graphql_request($get_drop_query, $get_drop_variables, $holaplex_api_key);

    if (isset($response['data']['project']['drop'])) {
      return $response['data']['project']['drop'];
    } else {
      return [];
    }
  }

}
