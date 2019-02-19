<?php

if ( isset($_REQUEST['action']) && 'addserver' == $_REQUEST['action'] ) {
	check_admin_referer( 'add-server', '_wpnonce_add-server' );

	$data_array = array (
		"ipAddressOrDomainName" => $_REQUEST['ip_address_or_domain_name']
	);

	$return = Shadowsocks_Hub_Helper::call_api("POST", "http://sshub/api/server", json_encode($data_array));

	$error = $return['error'];
	$http_code = $return['http_code'];
	$response = $return['body'];

	if ($http_code === 201) {
		$redirect = add_query_arg( array(
			'update' => 'add',
		), admin_url('admin.php?page=shadowsocks_hub_servers') );

		wp_redirect( $redirect );
		die();

	} elseif ($http_code === 400) {
		$error_msg = "Invalid domain name or IP address";
	} elseif ($http_code === 409) {
		$error_msg = "Server already exists";
	} elseif ($http_code === 500) {
		$error_msg = "Backend system error (addServer)";
	} elseif ($error) {
		$error_msg = "Backend system error: ".$error;
	} else {
		$error_message = "Backend system error undetected error.";
	}

	$redirect = add_query_arg( array(
		'error' => urlencode($error_msg),
	), admin_url('admin.php?page=shadowsocks_hub_add_server') );

	wp_redirect( $redirect );
	die();
}
?>
<div class="wrap">
<h1 id="add-new-server"><?php _e( 'Add New Server' ); ?>
</h1>

<?php if ( isset($_REQUEST['error']) ) : ?>
	<div class="error">
		<ul>
		<?php
			$err = urldecode($_REQUEST['error']);
			echo "<li>$err</li>\n";
		?>
		</ul>
	</div>
<?php endif;

if ( ! empty( $messages ) ) {
	foreach ( $messages as $msg )
		echo '<div id="message" class="updated notice is-dismissible"><p>' . $msg . '</p></div>';
} ?>

<form method="post" name="addserver" id="addserver" class="validate" novalidate="novalidate">
<input name="action" type="hidden" value="addserver" />
<?php wp_nonce_field( 'add-server', '_wpnonce_add-server' ) ?>

<table class="form-table">
	<tr class="server-host-wrap">
		<th scope="row"><label for="addserver-host"><?php echo __('Host'); ?></label></th>
		<td><input name="ip_address_or_domain_name" type="text" id="ip_address_or_domain_name" value="" class="regular-text"/></td>
	</tr>
</table>

<?php submit_button( __( 'Add Server' )); ?>
</form>