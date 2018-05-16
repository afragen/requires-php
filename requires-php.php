<?php
/**
 * Plugin Name:       Requires PHP
 * Plugin URI:        https://github.com/afragen/requires-php/
 * Description:       Perform PHP checks against dot org plugins.
 * Version:           0.9.0
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
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'unset_update_plugins_transient' ) );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_update_nag' ), 10, 2 );
		add_filter( 'plugin_install_action_links', array( $this, 'disable_install_button' ), 10, 2 );
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
				if ( $this->is_required_php( $update->slug ) ) {
					unset( $transient->response[ $update->plugin ] );
					$this->show_admin_notice();
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
		if ( $this->is_required_php( $slug ) ) {
			$links[] = '<span style="color:#f00;">' . __( 'Upgrade PHP for available plugin update.' ) . '</span>';
		}

		return $links;
	}

	/**
	 * Filter plugin action links in Install Plugin page.
	 *
	 * @param array $action_links
	 * @param array $plugin
	 *
	 * @return array $action_links
	 */
	public function disable_install_button( $action_links, $plugin ) {
		$disable_button = '<button type="button" class="button button-disabled" disabled="disabled">';
		$disable_button .= __( 'Cannot install' );
		$disable_button .= '</button>';

		if ( $plugin['requires_php'] &&
		     version_compare( PHP_VERSION, $plugin['requires_php'], '<=' )
		) {
			unset( $action_links[0] );
			$action_links[] = __( 'PHP version too low' );
			$action_links[] = $disable_button;
			$action_links   = array_reverse( $action_links );
		}

		return $action_links;
	}

	/**
	 * Test for required PHP version.
	 *
	 * @param string $slug Slug of the repository being tested.
	 *
	 * @return bool True for below required PHP version.
	 *              False for above required PHP version or none set.
	 */
	protected function is_required_php( $slug ) {
		$response = $this->get_plugin_dot_org_api_data( $slug );

		if ( ! $response->requires_php ) {
			return false;
		}

		return version_compare( PHP_VERSION, $response->requires_php, '<=' );
	}

	/**
	 * Get the dot org API data for the plugin or theme slug.
	 *
	 * @param string $slug Plugin or theme slug.
	 *
	 * @return object|bool $response
	 */
	protected function get_plugin_dot_org_api_data( $slug ) {
		$response = get_site_transient( 'php_check-' . $slug );
		if ( ! $response ) {
			$url      = 'https://api.wordpress.org/plugins/info/1.2/';
			$url      = add_query_arg( array(
				'action'        => 'plugin_information',
				'request[slug]' => $slug,
			), $url );
			$response = wp_remote_get( $url );
			$response = null !== $response['body'] ? json_decode( $response['body'] ) : false;

			// Plugins not in dot org.
			if ( ! isset( $response->requires_php ) ) {
				$response->requires_php = false;
			}
			set_site_transient( 'php_check-' . $slug, $response, 12 * HOUR_IN_SECONDS );
		}

		return $response;
	}

	/**
	 * Add admin notice.
	 */
	private function show_admin_notice() {
		add_action( is_multisite() ? 'network_admin_notices' : 'admin_notices', array( $this, 'get_admin_notice' ) );
	}

	/**
	 * Create admin notice.
	 */
	public function get_admin_notice() {
		$message = '<span style="color:#f00;" class="dashicons dashicons-warning"></span>&nbsp;';
		$message .= __( 'Some updates are not shown in this list because they require a higher version of PHP.' );

		?>
		<div class="notice-error notice is-dismissible">
			<p>
				<?php echo $message; ?>
			</p>
		</div>
		<?php
	}

}

new Requires_PHP();
