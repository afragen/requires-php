<?php
/**
 * Plugin Name:       Requires PHP
 * Plugin URI:        https://github.com/afragen/requires-php/
 * Description:       This plugin is used for testing.
 * Version:           0.3.0
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

	/**
	 * Requires_PHP constructor.
	 */
	public function __construct() {
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'pre_set_site_transient_update_plugins', ) );
	}

	/**
	 * Unset update transient as appropriate.
	 *
	 * @param object $transient Update transient.
	 *
	 * @return object $transient Update transient.
	 */
	public function pre_set_site_transient_update_plugins( $transient ) {
		foreach ( (array) $transient->response as $update ) {
			if ( ! $this->is_required_php( $update ) ) {
				unset( $transient->response[ $update->plugin ] );
			}
		}

		return $transient;
	}

	/**
	 * Test for required PHP version.
	 *
	 * @param \stdClass $repo Repository being tested from update transient.
	 *
	 * @return bool
	 */
	protected function is_required_php( $repo ) {
		$response = $this->get_dot_org_api_data( $repo->slug );

		return ( isset( $response->requires_php ) && version_compare( PHP_VERSION, $response->requires_php, '>=' ) );
	}

	/**
	 * Get the dot org API data for the plugin or theme slug.
	 *
	 * @param string $slug Plugin or theme slug.
	 *
	 * @return object|bool $response
	 */
	protected function get_dot_org_api_data( $slug ) {
		$url      = 'https://api.wordpress.org/plugins/info/1.1/';
		$url      = add_query_arg( array(
			'action'        => 'plugin_information',
			'request[slug]' => $slug,
		), $url );
		$response = wp_remote_get( $url );
		$response = null !== $response['body'] ? json_decode( $response['body'] ) : false;

		return $response;
	}
}

new Requires_PHP();
