<?php
/**
 * Plugin Name:       Requires PHP
 * Plugin URI:        https://github.com/afragen/requires-php/
 * Description:       This plugin is used for testing.
 * Version:           0.4.6
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
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'unset_update_plugins_transient', ) );
		add_filter( 'upgrader_pre_download', array( $this, 'exit_add_plugin_process' ) );
	}

	/**
	 * Unset update_plugins transient as appropriate.
	 *
	 * @param object $transient Update transient.
	 *
	 * @return object $transient Update transient.
	 */
	public function unset_update_plugins_transient( $transient ) {
		if ( isset( $transient->response ) ) {
			foreach ( (array) $transient->response as $update ) {
				if ( ! $this->is_required_php( $update->slug ) ) {
					unset( $transient->response[ $update->plugin ] );
				}
			}
		}

		return $transient;
	}

	/**
	 * Interrupt 'Add Plugin' cycle.
	 *
	 * @return \WP_Error|bool
	 */
	public function exit_add_plugin_process() {
		if ( isset( $_POST['slug'] ) && ! $this->is_required_php( $_POST['slug'] ) ) {
			return new \WP_Error( 'requires_php', __( 'Upgrade PHP to install this plugin.' ) );
		}

		return false;
	}

	/**
	 * Test for required PHP version.
	 *
	 * @param string $slug Slug of the repository being tested.
	 *
	 * @return bool
	 */
	protected function is_required_php( $slug ) {
		$response = $this->get_plugin_dot_org_api_data( $slug );

		return isset( $response->error ) ||
		       ( isset( $response->requires_php ) && version_compare( PHP_VERSION, $response->requires_php, '>=' ) );
	}

	/**
	 * Get the dot org API data for the plugin or theme slug.
	 *
	 * @param string $slug Plugin or theme slug.
	 *
	 * @return object|bool $response
	 */
	protected function get_plugin_dot_org_api_data( $slug ) {
		$url      = 'https://api.wordpress.org/plugins/info/1.2/';
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
