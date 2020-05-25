<?php
/*
Plugin Name: Jackson Healthcare Map
Description: Used to update and display the COVID-19 response map
Author: Dylan Connor / REQ
*/

include( plugin_dir_path( __FILE__ ) .'shortcode.php');

function jh_map_register_settings() {
	add_option( 'jh_map_option_name', 'This is my option value.' );
	register_setting( 'jh_map_options_group', 'jh_map_option_name', 'jh_map_callback' );
}
add_action( 'admin_init', 'jh_map_register_settings' );


function jh_map_register_options_page() {
  add_options_page( 'Jackson Healthcare Map', 'Jackson Healthcare Map', 'manage_options', 'jh_map', 'jh_map_options_page' );
}
add_action( 'admin_menu', 'jh_map_register_options_page' );


function jh_map_options_page() {
	include dirname( __FILE__ ) . '/options.php';
}


function jh_map_enqueue() {
  wp_register_script( 'jh-map-scripts', '/wp-content/plugins/jackson-healthcare-map/map/map.js', array('jquery'), date("H:i:s"), true );

  if ( shortcode_exists( 'responsemap' ) ) {
    wp_enqueue_style( 'jh-map-style', '/wp-content/plugins/jackson-healthcare-map/map/map.css', array(), date("H:i:s"));
    wp_enqueue_script('jh-map-scripts');
  }
}
add_action( 'wp_enqueue_scripts', 'jh_map_enqueue' );


function jh_map_create_db() {
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
register_activation_hook( __FILE__, 'jh_map_create_db' );