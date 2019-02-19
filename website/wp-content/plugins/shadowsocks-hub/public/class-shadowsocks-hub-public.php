<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       london.opensesame.vip
 * @since      1.0.0
 *
 * @package    Shadowsocks_Hub
 * @subpackage Shadowsocks_Hub/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Shadowsocks_Hub
 * @subpackage Shadowsocks_Hub/public
 * @author     Eggham Carnegie <eggham.carnegie@gmail.com>
 */
class Shadowsocks_Hub_Public {

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

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	public function remove_tabs_from_my_account($items)
	{
		unset($items['edit-address']);
		unset($items['downloads']);
		unset($items['payment-methods']);
    	return $items;
	}

	function add_shadowsocks_account_endpoint()
	{
	    add_rewrite_endpoint('shadowsocks-account', EP_ROOT | EP_PAGES);
	}

	function shadowsocks_account_query_vars($vars)
	{
	    $vars[] = 'shadowsocks-account';
	    return $vars;
	}

	function add_shadowsocks_account_link_my_account($items)
	{
 		$items['shadowsocks-account'] = 'Shadowsocks';
	    return $items;
	}

	function shadowsocks_account_title ( $title ) {
		global $wp_query;

		$is_endpoint = isset( $wp_query->query_vars['shadowsocks-account'] );
	
		if ( $is_endpoint && ! is_admin() && is_main_query() && in_the_loop() && is_account_page() ) {
			// New page title.
			$title = __( 'My shadowsocks accounts', 'woocommerce' );
	
			remove_filter( 'the_title', 'shadowsocks_account_title' );
		}
	
		return $title;
	}

	function shadowsocks_account_content()
	{
		include_once('partials/shadowsocks-accounts.php');
	}

	function dashboard_title ( $title ) {
		global $wp_query;

		$is_endpoint = isset( $wp_query->query_vars['dashboard'] );
	
		if ( $is_endpoint && ! is_admin() && is_main_query() && in_the_loop() && is_account_page() ) {
			// New page title.
			$title = __( 'My usage', 'woocommerce' );
	
			remove_filter( 'the_title', 'dashboard_title' );
		}
	
		return $title;
	}

	function my_account_menu_order() {
		$menuOrder = array(
			'dashboard'			=> __( 'Usage', 'woocommerce' ),
			'shadowsocks-account' => __('Shadowsocks', 'woocommerce'),
			'orders'             => __( 'Orders', 'woocommerce' ),
			'edit-account'    	=> __( 'Account Details', 'woocommerce' ),
			'customer-logout'    => __( 'Logout', 'woocommerce' ),
		);
		return $menuOrder;
	}

	function usage_dashboard_template ( $template, $template_name, $template_path ) {
		$basename = basename( $template );
		if ( $basename == 'dashboard.php' ) {
			$template = trailingslashit( plugin_dir_path( __FILE__ ) ) . 'partials/usage.php';
		}
		return $template;
	}

	public function add_stylesheet() {
		wp_register_style('sshubstylesheet', plugins_url( 'shadowsocks-hub/public/css/shadowsocks-hub-public.css') );
		wp_enqueue_style('sshubstylesheet');
	}

	public function add_qrcode_js() {
		wp_enqueue_script( "sshub-qrcode-script", plugin_dir_url( __FILE__ ) . 'js/qrcode.min.js', array( 'jquery' ), $this->version, false );
	}

	public function add_google_charts_loader_js() {
		wp_enqueue_script( "sshub-google-charts-script", plugin_dir_url( __FILE__ ) . 'js/loader.js', array(), $this->version, false );
	}

	public function check_my_account_menu_icon () {
		echo '<script type = "text/javascript">
			var orderElement = document.querySelector(".woocommerce-MyAccount-navigation ul li.woocommerce-MyAccount-navigation-link--orders a");
			var shadowsocksAccountElement = document.querySelector(".woocommerce-MyAccount-navigation ul li.woocommerce-MyAccount-navigation-link--shadowsocks-account a");
			var dashboardElement = document.querySelector(".woocommerce-MyAccount-navigation ul li.woocommerce-MyAccount-navigation-link--dashboard a");
			var orderIcon = getComputedStyle(orderElement, "::before").content;
			if (orderIcon === "\"\uf291\"") {
				shadowsocksAccountElement.setAttribute("menu-icon", "\uf029");
				dashboardElement.setAttribute("menu-icon", "\uf200");
			} else {
				shadowsocksAccountElement.setAttribute("menu-icon", "");
				dashboardElement.setAttribute("menu-icon", "");
			}
		</script>';
	}

	public function remove_checkout_fields ( $fields ) {
		unset($fields['billing']['billing_first_name']);
		unset($fields['billing']['billing_last_name']);
		unset($fields['billing']['billing_company']);
		unset($fields['billing']['billing_address_1']);
		unset($fields['billing']['billing_address_2']);
		unset($fields['billing']['billing_city']);
		unset($fields['billing']['billing_postcode']);
		unset($fields['billing']['billing_country']);
		unset($fields['billing']['billing_state']);
		unset($fields['billing']['billing_phone']);
		unset($fields['shipping']['shipping_first_name']);
		unset($fields['shipping']['shipping_last_name']);
		unset($fields['shipping']['shipping_company']);
		unset($fields['shipping']['shipping_address_1']);
		unset($fields['shipping']['shipping_address_2']);
		unset($fields['shipping']['shipping_city']);
		unset($fields['shipping']['shipping_postcode']);
		unset($fields['shipping']['shipping_country']);
		unset($fields['shipping']['shipping_state']);
		unset($fields['order']['order_comments']);

		return $fields;
	}

	public function add_purchase_to_sshub( $order_id )
	{
		if ( empty ( $order_id ) ) {
			error_log("\$order_id is empty. order_id =" . $order_id );
			return;
		};

		$order = wc_get_order( $order_id );
		$user = $order->get_user();
		if ( empty ( $user ) ) {
			error_log("\$user is empty. user =" . $user );
			return;
		};
		$user_id = $user->ID;

		// get product details
		$items = $order->get_items();

		// get the first product. Remaining products are ignored.
		$item = reset($items);

		foreach ( $items as $key => $item ){
	
			$product_id = $item->get_product_id();
			$product_name = $item->get_name();
			$product_quantity = $item->get_quantity();

			$product = wc_get_product($product_id);
			$life_span = $product->get_attribute( 'pa_life_span' );
			$traffic = $product->get_attribute( 'pa_traffic' );
			$encryption_method = $product->get_attribute( 'pa_encryption_method' );

			switch ($life_span) {
				case "Monthly":
					$formatted_life_span = "month";
					break;
				case "Bimonthly":
					$formatted_life_span = "bimonth";
					break;
				case "Quarterly":
					$formatted_life_span = "quarter";
					break;
				case "Semiannually":
					$formatted_life_span = "semiannual";
					break;
				case "Annually":
					$formatted_life_span = "annual";
					break;
				default:
					error_log("unkown life_span: " . $life_span);
					return;
			}

			$trafficUnit = substr($traffic, -1);
			switch ($trafficUnit) {
				case "M":
					$multiple = 1000 * 1000;
					break;
				case "G":
					$multiple = 1000 * 1000 * 1000;
					break;
				case "T":
					$multiple = 1000 * 1000 * 1000 * 1000;
					break;
				default:
				error_log("unkown traffic: " . $traffic);
				return;
			};
			$trafficQuantity = (int) substr($traffic, 0, -1);
			if (is_int($trafficQuantity)) {
				$trafficQuantity = (int) $trafficQuantity;
			}
			else {
				error_log("Invalid traffic quantity: " . $trafficQuantity);
				return;
			};
			$formatted_traffic = $trafficQuantity * $multiple;

			for ($i = 0; $i < $product_quantity; $i++ ) {

				$data_array = array (
					"uiType" => "wordpress",
					"userId" => (string) $user_id,
					"orderId" => $order_id . "_" . $product_id . "_" . $i,
					"lifeSpan" => $formatted_life_span,
					"traffic" => (int) $formatted_traffic,
					"accountParameters" => array (
						"type" => "shadowsocks",
						"method" => $encryption_method
					)
				);
	
				$return = Shadowsocks_Hub_Helper::call_api("POST", "http://sshub/api/purchase", json_encode($data_array));
	
				$error = $return['error'];
				$http_code = $return['http_code'];
				$response = $return['body'];
	
				if ($http_code === 201) {
					// do nothing
				} elseif ($http_code === 400) {
					$error_msg = "Invalid input";
					error_log("http status code:" . $http_code . ". " . $error_msg);
				} elseif ($http_code === 409) {
					// Normal. Purchase was added before. Do nothing
				} elseif ($http_code === 500) {
					$error_msg = "Backend system error (addPurchase)";
					error_log("http status code:" . $http_code . ". " . $error_msg);
				} elseif ($error) {
					$error_msg = "Backend system error: ".$error;
					error_log($error_msg);
				} else {
					$error_message = "Backend system error undetected error.";
					error_log($error_msg);
				}
			}
		}	
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/shadowsocks-hub-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/shadowsocks-hub-public.js', array( 'jquery' ), $this->version, false );

	}

}
