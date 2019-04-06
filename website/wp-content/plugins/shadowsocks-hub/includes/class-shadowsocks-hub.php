<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       www.shadowshop.org
 * @since      1.0.0
 *
 * @package    Shadowsocks_Hub
 * @subpackage Shadowsocks_Hub/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Shadowsocks_Hub
 * @subpackage Shadowsocks_Hub/includes
 * @author     Eggham Carnegie <eggham.carnegie@gmail.com>
 */
class Shadowsocks_Hub {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Shadowsocks_Hub_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'PLUGIN_NAME_VERSION' ) ) {
			$this->version = PLUGIN_NAME_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'shadowsocks-hub';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Shadowsocks_Hub_Loader. Orchestrates the hooks of the plugin.
	 * - Shadowsocks_Hub_i18n. Defines internationalization functionality.
	 * - Shadowsocks_Hub_Admin. Defines all hooks for the admin area.
	 * - Shadowsocks_Hub_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-shadowsocks-hub-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-shadowsocks-hub-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-shadowsocks-hub-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-shadowsocks-hub-public.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-shadowsocks-hub-helper.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-shadowsocks-hub-wp-list-table.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-shadowsocks-hub-servers-list-table.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-shadowsocks-hub-nodes-list-table.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-shadowsocks-hub-accounts-list-table.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/subscription/class-shadowsocks-hub-subscription-controller.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/subscription/class-shadowsocks-hub-subscription-service.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/subscription/class-shadowsocks-hub-subscription-dao.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/account/class-shadowsocks-hub-account-service.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/account/class-shadowsocks-hub-account-dao.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/node/class-shadowsocks-hub-node-service.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/node/class-shadowsocks-hub-node-dao.php';
		$this->loader = new Shadowsocks_Hub_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Shadowsocks_Hub_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Shadowsocks_Hub_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Shadowsocks_Hub_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_top_level_menu' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_dashboard_sub_menu' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_server_sub_menu' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_add_server_page' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_edit_server_page' );
		$this->loader->add_filter('set-screen-option', $plugin_admin, 'set_server_list_table_screen_options', 10, 3);
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_node_sub_menu' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_add_node_page' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_edit_node_page' );
		$this->loader->add_filter('set-screen-option', $plugin_admin, 'set_node_list_table_screen_options', 10, 3);
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_account_sub_menu' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_add_account_page' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_edit_account_page' );
		$this->loader->add_filter('set-screen-option', $plugin_admin, 'set_account_list_table_screen_options', 10, 3);
		$this->loader->add_action( 'woocommerce_after_register_taxonomy', $plugin_admin, 'add_sshub_product_attributes' );
		$this->loader->add_action( 'rest_api_init', $plugin_admin, 'register_subscription_route' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Shadowsocks_Hub_Public( $this->get_plugin_name(), $this->get_version() );

		//$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		//$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		//Hide Edit Address Tab @ My Account
		$this->loader->add_filter('woocommerce_account_menu_items', $plugin_public, 'remove_tabs_from_my_account', 999);
		//WooCommerce Add shadowsocks accounts Tab @ My Account
		$this->loader->add_action('init', $plugin_public, 'add_shadowsocks_account_endpoint');
		$this->loader->add_filter('query_vars', $plugin_public, 'shadowsocks_account_query_vars', 0);
		$this->loader->add_filter('woocommerce_account_menu_items', $plugin_public, 'add_shadowsocks_account_link_my_account');
		$this->loader->add_action('woocommerce_account_shadowsocks-account_endpoint', $plugin_public, 'shadowsocks_account_content');
		$this->loader->add_filter('the_title', $plugin_public, 'shadowsocks_account_title');
		//Replace defaut my account dashboard template with usage template
		$this->loader->add_filter('woocommerce_locate_template', $plugin_public, 'usage_dashboard_template', 10, 3);
		$this->loader->add_filter('the_title', $plugin_public, 'dashboard_title');
		//Edit my account menu order
		$this->loader->add_filter ( 'woocommerce_account_menu_items', $plugin_public, 'my_account_menu_order' );
		//Load css
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'add_stylesheet' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'add_qrcode_js' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'add_google_charts_loader_js' );
		$this->loader->add_action( 'woocommerce_after_account_navigation', $plugin_public, 'check_my_account_menu_icon' );
		//Remove checkout fields
		$this->loader->add_filter( 'woocommerce_checkout_fields', $plugin_public, 'remove_checkout_fields', 999, 1 );
		$this->loader->add_action( 'woocommerce_order_status_processing', $plugin_public, 'add_purchase_to_sshub' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Shadowsocks_Hub_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
