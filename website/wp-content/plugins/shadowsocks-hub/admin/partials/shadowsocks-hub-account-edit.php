<?php

if (isset($_REQUEST['action'])) {
	$action = $_REQUEST['action'];
} else {
	$action = null;
};

if (isset($_REQUEST['account_id'])) {
	$account_id = $_REQUEST['account_id'];
} else {
	$account_id = null;
};

switch ($action) {
case 'update':
check_admin_referer('update-account_' . $account_id);


// Update the account.
$data_array = array (
	"id" => $account_id,
	"type" => "SsAccount",
	"port" => (int) $_REQUEST['port'],
	"password" => $_REQUEST['password'],
	"method" => $_REQUEST['method'],
);

$return = Shadowsocks_Hub_Helper::call_api("PUT", "http://sshub/api/account", json_encode($data_array));

$error = $return['error'];
$http_code = $return['http_code'];
$response = $return['body'];

$edit_link = add_query_arg( array(
	'page' => 'shadowsocks_hub_edit_account',
	'account_id' => $account_id,
), admin_url('admin.php') );

if ($http_code === 200) {
	$redirect = add_query_arg( 'updated', true, $edit_link );

	wp_redirect( $redirect );
	die();
} elseif ($http_code === 400) {
	$error_msg = "Invalid input.";
} elseif ($http_code === 404) {
	$error_msg = "Account does not exist.";
} elseif ($http_code === 409) {
	$error_msg = "New port has already been used.";
} elseif ($http_code === 410) {
	$error_msg = "Type does not match.";
} elseif ($http_code === 500) {
	$error_msg = "Backend system error (updateAccount)";
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

<div class="wrap" id="account-edit-page">
<h1 class="wp-heading-inline">Edit Account</h1>


<hr class="wp-header-end">

<form action="<?php 
$edit_link = add_query_arg( array(
	'page' => 'shadowsocks_hub_edit_account',
), admin_url('admin.php') );

echo esc_url( $edit_link ); 
?>" method="post" novalidate="novalidate">
<?php wp_nonce_field('update-account_' . $account_id);

$account = Shadowsocks_Hub_Account_Service::get_account_by_id($account_id);

if (!is_wp_error($account)) {
	$userId = $account['purchase']['userId'];
	$host = $account['node']['server']['ipAddressOrDomainName'];
	$protocol = $account['node']['protocol'];
	$port = $account['port'];
	$password = $account['password'];
	$method = $account['method'];
	$orderId = $account['purchase']['orderId'];
	$lifeSpan = $account['purchase']['lifeSpan'];
	$user = get_user_by('id', (int) $userId);
	$userEmail = $user->data->user_email;
} else {
	$error_message = $account->get_error_message(); ?>
	<div class="error">
		<ul>
			<li><?php echo $error_message;?></li>
		</ul>
	</div>
	<?php } ?>

<table class="form-table">
	<tr class="account-host-wrap">
		<th><label for="host"><?php _e('Host') ?></label></th>
		<td><input type="text" name="host" id="host" value="<?php echo esc_attr($host); ?>" class="regular-text" disabled="disabled" /><span class="description"><?php _e('Host cannot be changed.'); ?></span></td>
	</tr>
	<tr class="account-protocol-wrap">
		<th><label for="protocol"><?php _e('Protocol') ?></label></th>
		<td><input type="text" name="protocol" id="protocol" value="<?php echo esc_attr($protocol); ?>" class="regular-text" disabled="disabled" /><span class="description"><?php _e('Protocol cannot be changed.'); ?></span></td>
	</tr>
	<tr class="account-port-wrap">
		<th><label for="port"><?php _e('Port') ?></label></th>
		<td><input type="number" name="port" id="port" value="<?php echo esc_attr($port); ?>" class="regular-text"/></td>
	</tr>
	<tr class="account-password-wrap">
		<th><label for="password"><?php _e('Password') ?></label></th>
		<td><input type="text" name="password" id="password" value="<?php echo esc_attr($password); ?>" class="regular-text"/></td>
	</tr>
	<tr class="account-method-wrap">
		<th><label for="method"><?php _e('Encryption') ?></label></th>
		<td><input type="text" name="method" id="method" value="<?php echo esc_attr($method); ?>" class="regular-text" /></td>
	</tr>
	<tr class="account-user-wrap">
		<th><label for="user"><?php _e('User') ?></label></th>
		<td><input type="text" name="user" id="user" value="<?php echo esc_attr($userEmail); ?>" class="regular-text" disabled="disabled" /><span class="description"><?php _e('User cannot be changed.'); ?></span></td>
	</tr>
	<tr class="account-orderId-wrap">
		<th><label for="orderId"><?php _e('Order ID') ?></label></th>
		<td><input type="text" name="orderId" id="orderId" value="<?php echo esc_attr($orderId); ?>" class="regular-text" disabled="disabled" /><span class="description"><?php _e('Order ID cannot be changed.'); ?></span></td>
	</tr>
	<tr class="account-lifeSpan-wrap">
		<th><label for="lifeSpan"><?php _e('Life Span') ?></label></th>
		<td><input type="text" name="lifeSpan" id="lifeSpan" value="<?php echo esc_attr($lifeSpan); ?>" class="regular-text" disabled="disabled" /><span class="description"><?php _e('Life Span cannot be changed.'); ?></span></td>
	</tr>
</table>

<input type="hidden" name="action" value="update" />
<input type="hidden" name="account_id" id="account_id" value="<?php echo esc_attr($account_id); ?>" />

<?php submit_button( 'Update Account' ); ?>

</form>
</div>
<?php
break;
}
?>