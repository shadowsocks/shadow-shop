<?php

/**
 * Fired during plugin activation
 *
 * @link       www.shadowshop.org
 * @since      1.0.0
 *
 * @package    Shadowsocks_Hub
 * @subpackage Shadowsocks_Hub/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Shadowsocks_Hub
 * @subpackage Shadowsocks_Hub/includes
 * @author     Eggham Carnegie <eggham.carnegie@gmail.com>
 */
class Shadowsocks_Hub_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		/**
		 * activate pretty permalinks
		 */
		global $wp_rewrite; 
		//Write the rule
		$wp_rewrite->set_permalink_structure('/%postname%/'); 
		//Set the option
		update_option( "rewrite_rules", FALSE ); 
		//Flush the rules and tell it to write htaccess
		$wp_rewrite->flush_rules( true );	
	}

}
