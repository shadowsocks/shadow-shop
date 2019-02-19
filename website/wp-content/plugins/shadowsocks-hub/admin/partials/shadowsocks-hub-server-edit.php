<?php

if (isset($_REQUEST['action'])) {
	$action = $_REQUEST['action'];
} else {
	$action = null;
};

if (isset($_REQUEST['server_id'])) {
	$server_id = $_REQUEST['server_id'];
} else {
	$server_id = null;
};

switch ($action) {
case 'update':
check_admin_referer('update-server_' . $server_id);


// Update the server.
$data_array = array (
	"id" => $server_id,
	"ipAddressOrDomainName" => $_REQUEST['ip_address_or_domain_name']
);

$return = Shadowsocks_Hub_Helper::call_api("PUT", "http://sshub/api/server", json_encode($data_array));

$error = $return['error'];
$http_code = $return['http_code'];
$response = $return['body'];

$edit_link = add_query_arg( array(
	'page' => 'shadowsocks_hub_edit_server',
	'server_id' => $server_id,
), admin_url('admin.php') );

if ($http_code === 200) {
	$redirect = add_query_arg( 'updated', true, $edit_link );

	wp_redirect( $redirect );
	die();
} elseif ($http_code === 400) {
	$error_msg = "Invalid domain name or IP address.";
} elseif ($http_code === 404) {
	$error_msg = "Id does not exist.";
} elseif ($http_code === 409) {
	$error_msg = "Server already exists.";
} elseif ($http_code === 500) {
	$error_msg = "Backend system error (updateServer)";
} elseif ($error) {
	$error_msg = "Backend system error: ".$error;
} else {
	$error_msg = "Backend system error undetected error.";
}

$redirect = add_query_arg( array(
	'error' => urlencode($error_msg),
), $edit_link );

wp_redirect( $redirect );
die();


$edit_link = add_query_arg( array(
	'page' => 'shadowsocks_hub_edit_server',
), admin_url('admin.php') );
if ( !is_wp_error( $errors ) ) {
	$redirect = add_query_arg( 'updated', true, $edit_link );
	wp_redirect($redirect);
	exit;
}

default:
?>

<?php if ( isset($_GET['updated']) ) : ?>
<div id="message" class="updated notice is-dismissible">
	<p><strong><?php _e('Server updated.') ?></strong></p>
</div>
<?php endif; ?>
<?php if ( isset($_REQUEST['error']) ) : ?>
	<div class="error">
		<ul>
		<?php
			$err = urldecode($_REQUEST['error']);
			echo "<li>$err</li>\n";
		?>
		</ul>
	</div>
<?php endif; ?>

<div class="wrap" id="server-edit-page">
<h1 class="wp-heading-inline">Edit Server</h1>


<hr class="wp-header-end">

<form action="<?php 
$edit_link = add_query_arg( array(
	'page' => 'shadowsocks_hub_edit_server',
), admin_url('admin.php') );

echo esc_url( $edit_link ); 
?>" method="post" novalidate="novalidate">
<?php wp_nonce_field('update-server_' . $server_id) ?>
<?php
$data_array = array (
	"id" => $server_id,
);

$return = Shadowsocks_Hub_Helper::call_api("GET", "http://sshub/api/server", $data_array);

$error = $return['error'];
$http_code = $return['http_code'];
$response = $return['body'];

if ($http_code === 200) {
	$ip_address_or_domain_name = $response['ipAddressOrDomainName'];
} elseif ($http_code === 400) {
	$error_message = "Invalid server id";
} elseif ($http_code === 404) {
	$error_message = "Server does not exist";
} elseif ($http_code === 500) {
	$error_message = "Backend system error (getServerById)";
} elseif ($error) {
	$error_message = "Backend system error: ".$error;
} else {
	$error_message = "Backend system error undetected error.";
}; 
if ($http_code !== 200) { ?>
	<div class="error">
		<ul>
			<li><?php echo $error_message;?></li>
		</ul>
	</div>
<?php
}
?>

<table class="form-table">
	<tr class="server-host-wrap">
		<th><label for="ip_address_or_domain_name"><?php _e('Host') ?></label></th>
		<td><input type="text" name="ip_address_or_domain_name" id="ip_address_or_domain_name" value="<?php echo esc_attr($ip_address_or_domain_name); ?>" class="regular-text" /></td>
	</tr>
</table>

<input type="hidden" name="action" value="update" />
<input type="hidden" name="server_id" id="server_id" value="<?php echo esc_attr($server_id); ?>" />

<?php submit_button( 'Update Server' ); ?>

</form>
</div>
<?php
break;
}
?>