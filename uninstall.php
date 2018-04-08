<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @link       https://github.com/afragen/requires-php
 *
 * @package    Requires_PHP
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;
$table         = is_multisite() ? $wpdb->base_prefix . 'sitemeta' : $wpdb->base_prefix . 'options';
$column        = is_multisite() ? 'meta_key' : 'option_name';
$delete_string = 'DELETE FROM ' . $table . ' WHERE ' . $column . ' LIKE %s LIMIT 1000';
$wpdb->query( $wpdb->prepare( $delete_string, array( '%php_check-%' ) ) );
