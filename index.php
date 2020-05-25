<?php
/*
Plugin Name: WP USA Map
Description: Used to update and display an SVG map of the USA with data based on specific states
Author: Dylan Connor / REQ
*/

include( plugin_dir_path( __FILE__ ) .'shortcode.php');

function wp_usa_map_register_settings() {
	add_option( 'wp_usa_map_option_name', 'This is my option value.' );
	register_setting( 'wp_usa_map_options_group', 'wp_usa_map_option_name', 'wp_usa_map_callback' );
}
add_action( 'admin_init', 'wp_usa_map_register_settings' );


function wp_usa_map_register_options_page() {
  add_options_page( ' WP USA Map', ' WP USA Map', 'manage_options', 'wp_usa_map', 'wp_usa_map_options_page' );
}
add_action( 'admin_menu', 'wp_usa_map_register_options_page' );


function wp_usa_map_options_page() {
	include dirname( __FILE__ ) . '/options.php';
}


function wp_usa_map_enqueue() {
  wp_register_script( 'wp-usa-map-scripts', plugin_dir_path( __FILE__ ) . 'map/map.js', array('jquery'), date("H:i:s"), true );

  if ( shortcode_exists( 'responsemap' ) ) {
    wp_enqueue_style( 'wp-usa-map-style', plugin_dir_path( __FILE__ ) . 'map/map.css', array(), date("H:i:s"));
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