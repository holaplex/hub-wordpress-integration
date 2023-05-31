<?php



class Holaplex_Core
{


  public function send_graphql_request($query, $variables = [], $holaplex_api_key)
  {
    $api_url = 'https://api.holaplex.com/graphql';  // API endpoint URL

    $headers = [
      'Content-Type' => 'application/json',
      'Accept' => 'application/json',
      'Authorization' => '' . $holaplex_api_key, // get_option('holaplex_api_key'),
      'Accept-Encoding' => 'gzip, deflate, br',
      'Connection' => 'keep-alive',
      'DNT' => '1',
      'Origin' => 'file://'
    ];

    $data = [
      'query' => $query,
      'variables' => $variables,
    ];


    $body = json_encode($data);

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
    if ($response_code === 200) {
      // Successful response
      $data = json_decode($response_body, true);
      return $data;
    } else {
      // Error response
      // Handle the error
      return false;
    }

  }
}
