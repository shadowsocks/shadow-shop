<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       london.opensesame.vip
 * @since      1.0.0
 *
 * @package    Shadowsocks_Hub
 * @subpackage Shadowsocks_Hub/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Shadowsocks_Hub
 * @subpackage Shadowsocks_Hub/includes
 * @author     Eggham Carnegie <eggham.carnegie@gmail.com>
 */
class Shadowsocks_Hub_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'shadowsocks-hub',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
