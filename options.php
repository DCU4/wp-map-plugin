<?php
  
  global $wpdb;
  if (isset($_POST["import"])) {
    $csv = $_FILES["file"]["tmp_name"];
    $complete = array();
    if($file = fopen($csv ,"r")){

      $headers = fgetcsv($file);
      $headers = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $headers);
      $n = 0;
      while(($row = fgetcsv($file)) !== FALSE){
        $n++;
        // remove percent from string
        $row[2] = str_replace("%", "", $row[2]);

        // make string a number
        $row[2] = floatval($row[2]);

        $complete = array_combine($headers, $row);
        
        // insert or update data into created table
        $query = "SELECT `map_id` FROM `map_data`";
        $rows = $wpdb->get_results( $query );
        if (count($rows) < $n){
          $wpdb->insert('map_data', 
          array(
            'map_id' => $n, 
            'map_source' => $complete['Source'],
            'map_destination' => $complete['Destination'],
            'map_percentage' => $complete['Percentage']  
            )
          );
        } else {
          $wpdb->update('map_data', 
            array(
              'map_source' => $complete['Source'],
              'map_destination' => $complete['Destination'],
              'map_percentage' => $complete['Percentage']  
            ), 
            array (
              'map_id' => $n
            )
          );
        }
      }
    }
    fclose($csv);
  }

?>
<style>
  .jackson-healthcare-map-admin .notice  {
    display: none;

  }
  .jackson-healthcare-map-admin .info {
    margin: 20px 0;
  }
  .jackson-healthcare-map-admin .info p {
    margin: 0;
  }
  .jackson-healthcare-map-admin .finish-message p.error {
    color: red;
  }
</style>

<div class="wrap jackson-healthcare-map-admin">
  <h1>Jackson Healthcare COVID-19 Response Map</h1>
  <div class="info">
    <p>Save your .CSV file with <strong>Souce</strong>, <strong>Destination</strong>, and <strong>Percentage</strong> as the headers. </p>
    <em>(ie. Row 1, columns A, B, and C, respectively)</em>

  </div>
  <form action="" method="post" enctype="multipart/form-data">
    <input type="file" name="file" id="file" accept=".csv">
    <input type="submit" name="import" id="submit" class="button button-primary" value="Import Map Data">
  </form>
  <p style="margin-top: 20px;">Use the shortcode <code>[responsemap]</code> on a page to display the map.</p>
  <div class="finish-message">
    <?php 
    if(isset($_POST["import"])) {
      if($_FILES["file"]['error'] == 0){ ?>
        <p>Success!</p>
      <?php } else if ($_FILES["file"]['error'] == 1) { ?>
        <p class="error">Error: The uploaded file exceeds the upload_max_filesize directive in php.ini</p>
      <?php } else if ($_FILES["file"]['error'] == 2) { ?>
        <p class="error">Error: The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.</p>
      <?php } else if ($_FILES["file"]['error'] == 3) { ?>
        <p class="error">Error: The uploaded file was only partially uploaded.</p>
      <?php } else if ($_FILES["file"]['error'] == 4) { ?>
        <p class="error">Error: No file was uploaded.</p>
      <?php } else if ($_FILES["file"]['error'] == 6) { ?>
        <p class="error">Error: Missing a temporary folder. </p>
      <?php } else if ($_FILES["file"]['error'] == 7) { ?>
        <p class="error">Error: Failed to write file to disk.</p>
      <?php } else if ($_FILES["file"]['error'] == 8) { ?>
        <p class="error">Error: A PHP extension stopped the file upload.</p>
    <?php } } ?>
  </div>
  
</div>
