<?php

/*
Plugin Name: SSL Support For WebByLife Platform
Description: Add automated SSL verification Support for WebByLife Platform.
If you use this plugin, you can easily generate SSL certificate for your domain.
This plugin will help you to generate SSL certificate for your domain automatically.
Where to generate SSL certificate? https://utility.webbylife.com
Version: 1.0
Author: nurujjamanpollob
Author URI: https://utility.webbylife.com
License: GPL2
*/

require_once plugin_dir_path(__FILE__) . 'handler/activation_handler.php';
require_once plugin_dir_path(__FILE__) . 'pages/setting.php';
require_once plugin_dir_path(__FILE__) . 'handler/form_submit_handler.php';
require_once plugin_dir_path(__FILE__) . 'pages/manage_acme_entry.php';

class AdminNotice {


	/**
	 * Register the activation hook
	 */
	public function __construct() {
		register_activation_hook( __FILE__, array( $this, 'install' ) );
	}

	/**
	 * Check the dependent plugin version
	 */
	protected function is_compatible(): bool {
		return make_get_request_to_api_integration();
	}

	/**
	 * Function to deactivate the plugin
	 */
	protected function deactivate_plugin() {
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		deactivate_plugins( plugin_basename( __FILE__ ) );
		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}
	}

	/**
	 * Deactivate the plugin and display a notice if the dependent plugin is not compatible or not active.
	 */
	public function install() {
		if ( ! $this->is_compatible()) {
			$this->deactivate_plugin();
			wp_die( 'Could not be activated. ' . $this->errormsg() );
		} else {
			ssl_support_for_webbylife_platform_activation_handler();
		}
	}

	function errormsg () {
		$site_plugin_page = get_site_url() . '/wp-admin/plugins.php';
		$class = 'notice notice-error';
		$message = __( '<p>The API is not working correctly.
                Maybe your server has automatic ACME certificate issuer, or another application or server intercepting request at
		                <?php get_site_url()?>/.well-known/acme-challenge/</p> <br>
            <p>To fix this issue, please check your server configuration and make sure that the request is not intercepted by another application or server.</p>
            <br>
            <p>We are sorry but deactivating this plugin. This plugin does not activate unless issues are fixed!</p>
            <p>Click here to go back to plugins page: <a href="' . $site_plugin_page . '">' . $site_plugin_page . '</a>', 'text-domain' );
		printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
	}

}

new AdminNotice();
