<?php defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/*
Plugin Name:  Wipi
Plugin URI:   wipi.com
Description:  WP launcher
Version:      0.0.1
Author:       dbeja
*/

define( 'WIPI_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WIPI_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WIPI_VERSION', '0.0.1' );

require_once WIPI_PLUGIN_DIR . '/includes/autoload.php';

$wipi = new WIPI\Wipi();
$wipi->init();
