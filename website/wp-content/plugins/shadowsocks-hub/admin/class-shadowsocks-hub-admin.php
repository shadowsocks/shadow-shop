<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       www.shadowshop.org
 * @since      1.0.0
 *
 * @package    Shadowsocks_Hub
 * @subpackage Shadowsocks_Hub/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Shadowsocks_Hub
 * @subpackage Shadowsocks_Hub/admin
 * @author     Eggham Carnegie <eggham.carnegie@gmail.com>
 */
class Shadowsocks_Hub_Admin
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	private $users_obj;

	private $servers_obj;

	private $nodes_obj;

	private $accounts_obj;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	public function add_top_level_menu()
	{
		add_menu_page(
			'Shadow Shop Plugin admin page',
			'Shadow Shop',
			'manage_categories',
			'shadowsocks_hub_plugin',
			array($this, 'top_level_menu_init'),
			'dashicons-store',
			2
		);
	}

	public function top_level_menu_init()
	{
		echo '<h1>Shadow Shop</h1>';
		echo '<p>Please select a sub-menu</p>';
	}

	public function add_dashboard_sub_menu()
	{
		add_submenu_page(
			'shadowsocks_hub_plugin',
			__( 'Dashboard', 'shadowsocks-hub' ),
			__( 'Dashboard', 'shadowsocks-hub' ),
			'manage_categories',
			'shadowsocks_hub_plugin'
		);
	}

	public function add_server_sub_menu()
	{
		$hook = add_submenu_page(
			'shadowsocks_hub_plugin',
			__( 'Servers', 'shadowsocks-hub' ),
			__( 'Servers', 'shadowsocks-hub' ),
			'manage_categories',
			'shadowsocks_hub_servers',
			array($this, 'server_sub_menu_init')
		);

		add_action('load-' . $hook, array($this, 'load_server_list_table_screen_options'));
	}

	public function load_server_list_table_screen_options()
	{

		$arguments = array(
			'label' => __( 'Servers Per Page', 'shadowsocks-hub' ),
			'default' => 5,
			'option' => 'servers_per_page'
		);

		add_screen_option('per_page', $arguments);
		
		// instantiate the Server List Table
		$this->servers_obj = new Shadowsocks_Hub_Servers_List_Table();
	}

	public function server_sub_menu_init()
	{
		// render the List Table
		include_once('partials/shadowsocks-hub-servers-display.php');
	}

	public function set_server_list_table_screen_options($status, $option, $value) {
		if ( 'servers_per_page' == $option ) return $value;
	}

	public function add_add_server_page()
	{

		$hook = add_submenu_page(
			'_doesnt_exist',
			__( 'Add Server', 'shadowsocks-hub' ),
			__( 'Add Server', 'shadowsocks-hub' ),
			'manage_categories',
			'shadowsocks_hub_add_server',
			array($this, 'add_server_page')
		);

	}

	public function add_server_page()
	{
		include_once('partials/shadowsocks-hub-server-new.php');
	}

	public function add_edit_server_page()
	{

		$hook = add_submenu_page(
			'_doesnt_exist',
			__( 'Edit Server', 'shadowsocks-hub' ),
			__( 'Edit Server', 'shadowsocks-hub' ),
			'manage_categories',
			'shadowsocks_hub_edit_server',
			array($this, 'edit_server_page')
		);

	}

	public function edit_server_page()
	{
		include_once('partials/shadowsocks-hub-server-edit.php');
	}

	public function add_node_sub_menu()
	{

		$hook = add_submenu_page(
			'shadowsocks_hub_plugin',
			__( 'Nodes', 'shadowsocks-hub' ),
			__( 'Nodes', 'shadowsocks-hub' ),
			'manage_categories',
			'shadowsocks_hub_nodes',
			array($this, 'node_sub_menu_init')
		);

		add_action('load-' . $hook, array($this, 'load_node_list_table_screen_options'));
	}

	public function load_node_list_table_screen_options()
	{

		$arguments = array(
			'label' => __( 'Nodes Per Page', 'shadowsocks-hub' ),
			'default' => 5,
			'option' => 'nodes_per_page'
		);

		add_screen_option('per_page', $arguments);
		
		// instantiate the Node List Table
		$this->nodes_obj = new Shadowsocks_Hub_Nodes_List_Table();
	}

	public function node_sub_menu_init()
	{
		// render the List Table
		include_once('partials/shadowsocks-hub-nodes-display.php');
	}

	public function set_node_list_table_screen_options($status, $option, $value) {
		if ( 'nodes_per_page' == $option ) return $value;
	}

	public function add_add_node_page()
	{

		$hook = add_submenu_page(
			'_doesnt_exist',
			__( 'Add Node', 'shadowsocks-hub' ),
			__( 'Add Node', 'shadowsocks-hub' ),
			'manage_categories',
			'shadowsocks_hub_add_node',
			array($this, 'add_node_page')
		);

	}

	public function add_node_page()
	{
		include_once('partials/shadowsocks-hub-node-new.php');
	}

	public function add_edit_node_page()
	{

		$hook = add_submenu_page(
			'_doesnt_exist',
			__( 'Edit Node', 'shadowsocks-hub' ),
			__( 'Edit Node', 'shadowsocks-hub' ),
			'manage_categories',
			'shadowsocks_hub_edit_node',
			array($this, 'edit_node_page')
		);

	}

	public function edit_node_page()
	{
		include_once('partials/shadowsocks-hub-node-edit.php');
	}

	public function add_account_sub_menu()
	{

		$hook = add_submenu_page(
			'shadowsocks_hub_plugin',
			__( 'Accounts', 'shadowsocks-hub' ),
			__( 'Accounts', 'shadowsocks-hub' ),
			'manage_categories',
			'shadowsocks_hub_accounts',
			array($this, 'account_sub_menu_init')
		);

		add_action('load-' . $hook, array($this, 'load_account_list_table_screen_options'));
	}

	public function load_account_list_table_screen_options()
	{

		$arguments = array(
			'label' => __( 'Accounts Per Page', 'shadowsocks-hub' ),
			'default' => 5,
			'option' => 'accounts_per_page'
		);

		add_screen_option('per_page', $arguments);
		
		// instantiate the Node List Table
		$this->accounts_obj = new Shadowsocks_Hub_Accounts_List_Table();
	}

	public function account_sub_menu_init()
	{
		// render the List Table
		include_once('partials/shadowsocks-hub-accounts-display.php');
	}

	public function set_account_list_table_screen_options($status, $option, $value) {
		if ( 'accounts_per_page' == $option ) return $value;
	}

	public function add_add_account_page()
	{

		$hook = add_submenu_page(
			'_doesnt_exist',
			__( 'Add Account', 'shadowsocks-hub' ),
			__( 'Add Account', 'shadowsocks-hub' ),
			'manage_categories',
			'shadowsocks_hub_add_account',
			array($this, 'add_account_page')
		);

	}

	public function add_account_page()
	{
		include_once('partials/shadowsocks-hub-account-new.php');
	}

	public function add_edit_account_page()
	{

		$hook = add_submenu_page(
			'_doesnt_exist',
			__( 'Edit Account', 'shadowsocks-hub' ), 
			__( 'Edit Account', 'shadowsocks-hub' ),
			'manage_categories',
			'shadowsocks_hub_edit_account',
			array($this, 'edit_account_page')
		);

	}

	public function edit_account_page()
	{
		include_once('partials/shadowsocks-hub-account-edit.php');
	}

	public function add_sshub_product_attributes() {

		$attributes = array(
			"life_span" => array(
				"label" => "Life Span",
				"name" => "life_span",
				"terms" => array( 'Annually', 'Semiannually', 'Quarterly', 'Bimonthly', 'Monthly' )
			),
			"traffic" => array(
				"label" => "Traffic",
				"name" => "traffic",
				"terms" => array(
					'10T', '9T', '8T', '7T', '6T', '5T', '4T', '3T', '2T', '1T', 
					'900G', '800G', '700G', '600G', '500G', '400G', '300G', '200G', '100G',
					'90G', '80G', '70G', '60G', '50G', '40G', '30G', '20G', '10G',
					'9G', '8G', '7G', '6G', '5G', '4G', '3G', '2G', '1G',
					'900M', '800M', '700M', '600M', '500M', '400M', '300M', '200M', '100M'
				)
			),
			"encryption_method" => array(
				"label" => "Encryption Method",
				"name" => "encryption_method",
				"terms" => array (
					"chacha20-ietf", "chacha20", "salsa20",
					"xchacha20-ietf-poly1305", "chacha20-ietf-poly1305", "bf-cfb",
					"camellia-256-cfb", "camellia-192-cfb", "camellia-128-cfb",
					"aes-256-ctr", "aes-192-ctr", "aes-128-ctr",
					"aes-256-cfb", "aes-192-cfb", "aes-128-cfb",
					"aes-256-gcm", "aes-192-gcm", "aes-128-gcm"
				)
			),
		);
		
		foreach($attributes as $attribute) {

			if ( ! taxonomy_exists ( 'pa_' . $attribute[ 'name' ] )) {
				$this->create_attribute( $attribute[ 'label' ], $attribute[ 'name' ] );
			};

			$this->add_terms_to_attribute( $attribute[ 'name' ], $attribute[ 'terms' ] );
		};
	}

	private function create_attribute( $label, $slug ) {
		$args      = array(
			'name'         => $label,
			'slug'         => $slug,
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => '1',
		);

		$id = wc_create_attribute( $args );

		if ( is_wp_error( $id ) ) {
			error_log( "add product attribute error " . " slug = " . $slug );
			error_log("\$id = " . print_r($id, true) );
		}

		return $id;
	}

	private function add_terms_to_attribute ( $attribute_slug, $terms ) {

		foreach ( $terms as $term ) {
			$result = wp_insert_term( $term, 'pa_' . $attribute_slug );
		}

		return;
	}

	/**
 * This is our callback function that embeds our phrase in a WP_REST_Response
 */
function prefix_get_endpoint_phrase() {
	error_log("reached prefix_get_endpoint_phrase");
    // rest_ensure_response() wraps the data we want to return into a WP_REST_Response, and ensures it will be properly returned.
    return rest_ensure_response( 'Hello World, this is the WordPress REST API' );
}

	/**
 * This function is where we register our routes for our example endpoint.
 */
public function prefix_register_example_routes() {
	error_log("reached prefix_register_example_routes");
    // register_rest_route() handles more arguments but we are going to stick to the basics for now.
    register_rest_route( 'hello-world/v1', '/phrase', array(
        // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
        'methods'  => WP_REST_Server::READABLE,
        // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
        'callback' => array($this, 'prefix_get_endpoint_phrase'),
    ) );
}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Shadowsocks_Hub_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Shadowsocks_Hub_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/shadowsocks-hub-admin.css', array(), $this->version, 'all');

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Shadowsocks_Hub_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Shadowsocks_Hub_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/shadowsocks-hub-admin.js', array('jquery'), $this->version, false);

	}

}
