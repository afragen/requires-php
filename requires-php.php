<?php
/**
 * Plugin Name:       Requires PHP
 * Plugin URI:        https://github.com/afragen/requires-php/
 * Description:       This plugin is used for testing.
 * Version:           0.1.0
 * Author:            Andy Fragen
 * License:           MIT
 * License URI:       http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * GitHub Plugin URI: https://github.com/afragen/requires-php/
 * Requires WP:       4.9
 * Requires PHP:      5.6
 */

namespace Fragen;

/**
 * Class Requires_PHP
 *
 * @package Fragen
 */
class Requires_PHP {

	public function __construct() {
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'pre_set_site_transient_update_plugins', ) );
	}

	public function pre_set_site_transient_update_plugins( $transient ) {

		foreach ( (array) $transient->response as $update ) {

			$url      = 'https://api.wordpress.org/plugins/info/1.1/';
			$url      = add_query_arg( array(
				'action'        => 'plugin_information',
				'request[slug]' => $update->slug,
			), $url );
			$response = wp_remote_get( $url );

			$response = null !== $response['body'] ? json_decode( $response['body'] ) : false;

			if ( isset( $response->requires_php ) &&
			     version_compare( $response->requires_php, PHP_VERSION, '>=' )
			) {
				unset( $transient->response[ $update->plugin ] );
			}
		}

		return $transient;
	}
}

new Requires_PHP();
