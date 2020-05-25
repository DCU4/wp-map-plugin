<?php 
  require ('../../../wp-blog-header.php');
  
  // pull table out as json
  global $wpdb;

  $query = "SELECT * FROM `map_data`";
  $rows = $wpdb->get_results( $query );

  header("Content-type: application/json; charset=utf-8");
  
  $json = json_encode($rows, JSON_NUMERIC_CHECK);

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