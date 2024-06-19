<?php
/**
 * Plugin Name:     Danial Pourgolab
 * Plugin URI:      https://danial.me
 * Plugin Prefix:   DP
 * Description:     A test plugin
 * Author:          Danial Pourgolab
 * Author URI:      https://danial.me
 * Text Domain:     danial-test
 * Domain Path:     /languages
 * Version:         1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
    require dirname( __FILE__ ) . '/vendor/autoload.php';
}