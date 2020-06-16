<?php
/*
Plugin Name: USA Map
Description: Used to update and display the COVID-19 response map
Author: Dylan Connor / REQ
*/

include( plugin_dir_path( __FILE__ ) .'shortcode.php');

function wp_usa_map_register_settings() {
	add_option( 'wp_usa_map_option_name', 'This is my option value.' );
  register_setting( 'wp_usa_map_options_group', 'wp_usa_map_option_name', 'wp_usa_map_callback' );
  global $wpdb;
  $query_destination = "SELECT `map_destination` FROM `map_data`";
  $destination_states = [];
  $rows_destination = $wpdb->get_results( $query_destination );
  foreach($rows_destination as $row) {
    array_push($destination_states, $row->map_destination);
  }
  $destination_states = array_unique($destination_states);
  foreach($destination_states as $state) { 
    register_setting( 'wp_usa_map_options_group', $state.'stateCopy' );
  }
}
add_action( 'admin_init', 'wp_usa_map_register_settings' );


function wp_usa_map_register_options_page() {
  add_options_page( 'USA Map', 'USA Map', 'manage_options', 'wp_usa_map', 'wp_usa_map_options_page' );
}
add_action( 'admin_menu', 'wp_usa_map_register_options_page' );


function wp_usa_map_options_page() {
	include dirname( __FILE__ ) . '/options.php';
}


function wp_usa_map_enqueue() {
  wp_register_script( 'wp-usa-map-scripts', '/wp-content/plugins/wp-map-plugin/map/map.js', array('jquery'), date("H:i:s"), true );

  if ( shortcode_exists( 'responsemap' ) ) {
    wp_enqueue_style( 'wp-usa-map-style', '/wp-content/plugins/wp-map-plugin/map/map.css', array(), date("H:i:s"));
    wp_enqueue_script('wp-usa-map-scripts');
  }
}
add_action( 'wp_enqueue_scripts', 'wp_usa_map_enqueue' );


function wp_usa_map_create_db() {
  global $wpdb;
  $charset_collate = $wpdb->get_charset_collate();
  require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

  // Create the table
  $table_name = 'map_data';
  $sql = "CREATE TABLE $table_name (
    map_id INTEGER NOT NULL AUTO_INCREMENT,
    map_destination TEXT NOT NULL,
    map_source TEXT NOT NULL,
    map_percentage DECIMAL(18,2) NOT NULL,
    PRIMARY KEY (map_id)
  ) $charset_collate;";
  dbDelta( $sql );
  $wpdb->show_errors();
}
register_activation_hook( __FILE__, 'wp_usa_map_create_db' );