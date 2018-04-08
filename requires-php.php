<?php
/**
 * Plugin Name:       Requires PHP
 * Plugin URI:        https://github.com/afragen/requires-php/
 * Description:       Perform PHP checks against dot org plugins.
 * Version:           0.6.0
 * Author:            Andy Fragen
 * License:           MIT
 * License URI:       http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * GitHub Plugin URI: https://github.com/afragen/requires-php/
 * Requires WP:       4.9
 * Requires PHP:      5.3
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
		add_filter( 'plugin_row_meta', array( $this, 'plugin_update_nag' ), 10, 2 );
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
	 * Adds small PHP upgrade nag to plugin row.
	 *
	 * @param array  $links
	 * @param string $file
	 *
	 * @return array $links
	 */
	public function plugin_update_nag( $links, $file ) {
		$slug = dirname( $file );
		if ( ! $this->is_required_php( $slug ) ) {
			$links[] = '<span style="color:#f00;">' . __( 'Upgrade PHP for available plugin update.' ) . '</span>';
		}

		return $links;
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
		$response = $this->get_cache( $slug );
		if ( ! $response ) {
			$url      = 'https://api.wordpress.org/plugins/info/1.2/';
			$url      = add_query_arg( array(
				'action'        => 'plugin_information',
				'request[slug]' => $slug,
			), $url );
			$response = wp_remote_get( $url );
			$response = null !== $response['body'] ? json_decode( $response['body'] ) : false;
			$this->set_cache( $slug, $response );
		}

		return $response;
	}

	/**
	 * Set dot org API query to option.
	 *
	 * @param string    $slug
	 * @param \stdClass $response
	 *
	 * @return bool
	 */
	protected function set_cache( $slug, $response ) {
		$cache_key = 'php_check-' . md5( $slug );
		$timeout   = '+' . 12 . ' hours';

		$response->timeout = strtotime( $timeout );
		update_site_option( $cache_key, $response );

		return true;
	}

	/**
	 * Get cached dot org API query from set option.
	 *
	 * @param string $slug
	 *
	 * @return bool|mixed
	 */
	protected function get_cache( $slug ) {
		$cache_key = 'php_check-' . md5( $slug );
		$cache     = get_site_option( $cache_key );

		if ( empty( $cache->timeout ) || time() > $cache->timeout ) {
			return false;
		}

		return $cache;
	}
}

new Requires_PHP();
