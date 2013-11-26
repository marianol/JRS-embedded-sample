<?php
/**
 * @file
 * This file includes Helper Functions for accesing the Jasperserver REST API
 *
 */

define('JRS_REST_URL',  'rest/');
define('JRS_RESTV2_URL', 'rest_v2/');

function jasperserver_sso_rest_keepalive() {
    // Bogus rest call to keep the JRS session alive
    // GET /jasperserver-pro/rest/resources?limit=1
    
    $JRS_REST_URL = $GLOBALS['base_root'] . ':' .  variable_get('jasperserver_sso_port', 8080) 
        . '/' . variable_get('jasperserver_sso_context', 'jasperserver-pro') . '/' . JRS_REST_URL ;
        
    $curl_opts = array();
    $curl_opts[CURLOPT_COOKIE] = $_SESSION['JRS_Cookie'];
    $response = jasperserver_sso_rest_doRequest($JRS_REST_URL . 'resources?limit=1', $curl_opts);
    // dd($response['metadata']['http_code'], $label = 'JRS Keepalive');
    return $response['metadata']['http_code'];
}

function jasperserver_sso_rest_post($service, $data, $JRS_REST_URL = '' , $headers=array()) {
  
  if ( $JRS_REST_URL == '' ) {
     $JRS_REST_URL = $GLOBALS['base_root'] . ':' .  variable_get('jasperserver_sso_port', 8080) 
        . '/' . variable_get('jasperserver_sso_context', 'jasperserver-pro') . '/' . JRS_REST_URL ;
  }

  $requestData = http_build_query($data);
  
  $headers[] = 'Content-Length: '.strlen($requestData);
  
  $curl_opts = array();
  $curl_opts[CURLOPT_CUSTOMREQUEST] = 'POST';
  $curl_opts[CURLOPT_HTTPHEADER] = $headers;
  $curl_opts[CURLOPT_POSTFIELDS] = $requestData;

  return jasperserver_sso_rest_doRequest($JRS_REST_URL . $service, $curl_opts);
}

function jasperserver_sso_rest_doRequest($url, $curl_opts) {
  $std_opts = array(
    CURLOPT_RETURNTRANSFER => true,  // return result instead of echoing
    CURLOPT_SSL_VERIFYPEER => false, // stop cURL from verifying the peer's certificate
    CURLOPT_FOLLOWLOCATION => true,  // follow redirects, Location: headers
    CURLOPT_MAXREDIRS      => 10,    // but dont redirect more than 10 times
    //CURLOPT_FILE         => '/dev/null', // saving cookies
    CURLOPT_HEADER         => true   // Get headers
  );
 
  $curl = curl_init($url);
  
  foreach ($std_opts  as $opt => $val)
      curl_setopt($curl, $opt, $val); 
   
  foreach ($curl_opts  as $opt => $val)
      curl_setopt($curl, $opt, $val);
  
  $response['body'] = curl_exec($curl);
  $response['metadata'] = curl_getinfo($curl);
  curl_close($curl);
  
  return $response;
}
