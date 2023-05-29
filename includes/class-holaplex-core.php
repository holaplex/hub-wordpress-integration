<?php



  class Holaplex_Core {


    public function send_graphql_request($query, $variables = [], $holaplex_api_key)
    {
      $api_url = 'https://api.holaplex.com/graphql';  // API endpoint URL
  
      $headers = [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: '. $holaplex_api_key, // get_option('holaplex_api_key'),
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

  }