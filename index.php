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
// register activation hook
register_activation_hook(__FILE__, 'ssl_support_for_webbylife_platform_activation_handler');
