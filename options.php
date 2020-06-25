<?php
  $update_num_message = '';
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
          $update_num_message = $n . ' rows of map data have been inserted.';
          $wpdb->insert('map_data', 
          array(
            'map_id' => $n, 
            'map_source' => $complete['Source'],
            'map_destination' => $complete['Destination'],
            'map_percentage' => $complete['Percentage']  
            )
          );
        } else {
          $update_num_message = $n . ' rows of map data have been updated.';
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

  $query_destination = "SELECT `map_destination` FROM `map_data`";
  $destination_states = [];
  $rows_destination = $wpdb->get_results( $query_destination );
  foreach($rows_destination as $row) {
    array_push($destination_states, $row->map_destination);
  }
  $destination_states = array_unique($destination_states);
  
?>
<style>
  .wp-map-plugin-admin .notice  {
    display: none;
  }
  
  .wp-map-plugin-admin .info {
    margin: 20px 0;
  }

  .wp-map-plugin-admin .finish-message p.error {
    color: red;
  }

  .wp-map-plugin-admin #stateCopy {
    display: flex;
    flex-direction: column;
    width: 60%;
  }
  .wp-map-plugin-admin #stateCopy {
    margin-bottom: 20px;
  }
  .wp-map-plugin-admin #stateCopy .state-copy-label{
    margin: 20px 0 10px;
    color: #23282d;
    font-size: 1.3em;
  }
</style>

<div class="wrap wp-map-plugin-admin">
  <h1>USA Map</h1>
  <div class="info">
    <p>Save your .CSV file with <strong>Source</strong>, <strong>Destination</strong>, and <strong>Percentage</strong> as the headers. <em>(ie. Row 1, columns A, B, and C, respectively)</em> </p>
    <p><strong>Source</strong>: Source States  </p>
    <p><strong>Destination</strong>: Destination states </p>
    <p><strong>Percentage</strong>:  Percentage </p>
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
        <p>Success! <?php echo $update_num_message; ?></p>
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
  <hr>
  <h3>Add copy for each state below</h3>
  <p>The save button is at the bottom!</p>
  <form id="stateCopy" method="post" action="options.php">
    <?php 
    settings_fields( 'wp_usa_map_options_group' );
    do_settings_sections( 'wp_usa_map_options_group' );
    foreach($destination_states as $state) { ?>
      <label class="state-copy-label" for="<?php echo $state; ?>stateCopy"><?php echo $state; ?> Copy</label> 
      <?php 
      wp_editor(get_option($state.'stateCopy'), $state.'stateCopy');?>
      
      <label class="state-hover-label" for="<?php echo $state; ?>stateHover"><?php echo $state; ?> Hover</label> 
      <input value="<?php echo get_option($state.'stateHover'); ?>" type="text" name="<?php echo $state; ?>stateHover" id="<?php echo $state; ?>stateHover" placeholder="Hover text">
    <?php
    }
    ?>
    <?php submit_button(); ?> 
  </form>
</div>
