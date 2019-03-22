<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              www.shadowshop.org
 * @since             1.0.0
 * @package           Shadow Shop
 *
 * @wordpress-plugin
 * Plugin Name:       Shadow Shop
 * Plugin URI:        www.shadowshop.org
 * Description:       Manage shadowsocks servers, nodes, and accounts
 * Version:           1.0.0
 * Author:            Eggham Carnegie
 * Author URI:        www.shadowshop.org
 * License:           Private
 * License URI:       
 * Text Domain:       shadowsocks-hub
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('PLUGIN_NAME_VERSION', '1.0.0');


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-shadowsocks-hub-activator.php
 */
function activate_shadowsocks_hub()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-shadowsocks-hub-activator.php';
	Shadowsocks_Hub_Activator::activate();
}

/**
 * This code create subscription table for shadowsocks_hub
 */
function shadowsocks_hub_create_db()
{
	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();
	$table_name = $wpdb->prefix . 'sshub_subscription';
	$user_table_name = $wpdb->prefix . 'users';

	$sql = "CREATE TABLE $table_name (
		id varchar(255) NOT NULL,
		userId bigint(20) UNSIGNED NOT NULL,
		createdTime bigint(20) NOT NULL,
		PRIMARY KEY id (id),
		UNIQUE KEY userId (userId),
		FOREIGN KEY  (userId) REFERENCES $user_table_name(ID)
	) $charset_collate;";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);

	add_option( "jal_db_version", "1.0" );
}


/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-shadowsocks-hub-deactivator.php
 */
function deactivate_shadowsocks_hub()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-shadowsocks-hub-deactivator.php';
	Shadowsocks_Hub_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_shadowsocks_hub');
register_activation_hook(__FILE__, 'shadowsocks_hub_create_db');
register_deactivation_hook(__FILE__, 'deactivate_shadowsocks_hub');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-shadowsocks-hub.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_shadowsocks_hub()
{

	$plugin = new Shadowsocks_Hub();
	$plugin->run();
}
run_shadowsocks_hub();
