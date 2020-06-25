<?php 
  require ('../../../wp-blog-header.php');
  
  // pull description out as json
  global $wpdb;
  $state_copy = [];
  $query_destination = "SELECT `map_destination` FROM `map_data`";
  $destination_states = [];
  $rows_destination = $wpdb->get_results( $query_destination );
  foreach($rows_destination as $row) {
    array_push($destination_states, $row->map_destination);
  }
  $destination_states = array_unique($destination_states);
  foreach($destination_states as $state) { 
    array_push($state_copy, get_option($state.'stateCopy'));
  }

  $complete_copy = array_combine($destination_states, $state_copy);
  
  header("Content-type: application/json; charset=utf-8");
  
  $json = json_encode($complete_copy);

  if ($json === false) {
    // Avoid echo of empty string (which is invalid JSON), and
    // JSONify the error message instead:
    $json = json_encode(["jsonError" => json_last_error_msg()]);
    if ($json === false) {
        // This should not happen, but we go all the way now:
        $json = '{"jsonError":"unknown"}';
    }
    // Set HTTP response status code to: 500 - Internal Server Error
    http_response_code(500);
  }
  http_response_code(200);
  echo $json;
?>