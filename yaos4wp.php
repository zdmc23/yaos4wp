<?php
/**
 * @package YAOS4WP 
 */

/**
 * Plugin Name: Yet Another OAuth2 Server for WordPress (YAOS4WP)
 * Plugin URI: https://github.com/DiscipleTools/?
 * Description: Yet Another OAuth2 Server for WordPress (YAOS4WP)
 * Version: 0.1
 * Author: DT?
 * Author URI: https://github.com/DiscipleTools
 * License: GPLv2 or later
 * Text Domain: wp-oauth2-server-plugin
 */

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

Copyright 2018 DT?
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
if ( !function_exists( 'add_action' ) ) {
  echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
  exit;
}

define( 'YAOS4WP_VERSION', '0.1' );
define( 'YAOS4WP__MINIMUM_WP_VERSION', '7.0' );
define( 'YAOS4WP__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

require_once( YAOS4WP__PLUGIN_DIR . 'oauth2-server.php' );
if (!class_exists('OAuth2Server')) {
  register_activation_hook( __FILE__, array( 'YAOS4WP', 'activation' ) );
  register_deactivation_hook( __FILE__, array( 'YAOS4WP', 'deactivation' ) );
}
OAuth2Server::instance(); 
