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
			if ( isset( $response->requires_php ) &&
			     version_compare( $response->requires_php, PHP_VERSION, '>=' )
			) {
				unset( $transient->response[ $update->plugin ] );
			}
		}

		return $transient;
	}
	/**
	 * Get the dot org API data for the plugin or theme slug.
	 *
	 * @param string $slug Plugin or theme slug.
	 * @param string $type {'plugin'|'theme'}.
	 *                     Default is 'plugin'.
	 *
	 * @return object|bool $response
	 */
	protected function get_dot_org_api_data( $slug, $type = 'plugin' ) {
		$url      = 'https://api.wordpress.org/' . $type . 's/info/1.1/';
		$url      = add_query_arg( array(
			'action'        => $type . '_information',
			'request[slug]' => $slug,
		), $url );
		$response = wp_remote_get( $url );
		$response = null !== $response['body'] ? json_decode( $response['body'] ) : false;

		return $response;
	}
}

new Requires_PHP();
