<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
if ( !function_exists( 'add_action' ) ) {
  echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
  exit;
}
if (!defined('WP_UNINSTALL_PLUGIN')) exit();
