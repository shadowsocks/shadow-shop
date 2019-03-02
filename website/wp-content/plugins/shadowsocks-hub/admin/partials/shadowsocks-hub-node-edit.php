<?php

if (isset($_REQUEST['action'])) {
	$action = $_REQUEST['action'];
} else {
	$action = null;
};

if (isset($_REQUEST['node_id'])) {
	$node_id = $_REQUEST['node_id'];
} else {
	$node_id = null;
};

switch ($action) {
case 'update':
check_admin_referer('update-node_' . $node_id);


// Update the node.

$data_array = array (
	"id" => $node_id,
	"name" => $_REQUEST['name'],
	"protocol" => "shadowsocks",
	"password" => $_REQUEST['password'],
	"port" => (int) $_REQUEST['port'],
	"lowerBound" => (int) $_REQUEST['lowerBound'],
	"upperBound" => (int) $_REQUEST['upperBound'],
	"comment" => $_REQUEST['comment'],
);
echo "\$data_array =";
echo "<pre>"; echo print_r($data_array); echo "</pre>";
$return = Shadowsocks_Hub_Helper::call_api("PUT", "http://sshub/api/node", json_encode($data_array));

$error = $return['error'];
$http_code = $return['http_code'];
$response = $return['body'];

$edit_link = add_query_arg( array(
	'page' => 'shadowsocks_hub_edit_node',
	'node_id' => $node_id,
), admin_url('admin.php') );

if ($http_code === 200) {
	$redirect = add_query_arg( 'updated', true, $edit_link );

	wp_redirect( $redirect );
	die();
} elseif ($http_code === 400) {
	$error_msg = "Invalid input";
} elseif ($http_code === 404) {
	$error_msg = "Node does not exist.";
} elseif ($http_code === 409) {
	$error_msg = "Node already exists.";
} elseif ($http_code === 500) {
	$error_msg = "Backend system error (updateNode)";
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
	'page' => 'shadowsocks_hub_edit_node',
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
	<p><strong><?php _e('Node updated.', 'shadowsocks-hub') ?></strong></p>
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

<div class="wrap" id="node-edit-page">
<h1 class="wp-heading-inline">Edit Node</h1>

<hr class="wp-header-end">

<form action="<?php 
$edit_link = add_query_arg( array(
	'page' => 'shadowsocks_hub_edit_node',
), admin_url('admin.php') );

echo esc_url( $edit_link ); 
?>" method="post" novalidate="novalidate">
<?php wp_nonce_field('update-node_' . $node_id) ?>
<?php 
$data_array = array (
	"id" => $node_id,
);

$return = Shadowsocks_Hub_Helper::call_api("GET", "http://sshub/api/node", $data_array);

$error = $return['error'];
$http_code = $return['http_code'];
$response = $return['body'];

if ($http_code === 200) {
	$name = $response['name'];
	$serverName = $response['server']['ipAddressOrDomainName'];
	$serverId = $response['server']['id'];
	$password = $response['password'];
	$port = $response['port'];
	$lowerBound = $response['lowerBound'];
	$upperBound = $response['upperBound'];
	$comment = $response['comment'];
} elseif ($http_code === 400) {
	$error_message = "Invalid node id";
} elseif ($http_code === 404) {
	$error_message = "Node does not exist";
} elseif ($http_code === 500) {
	$error_message = "Backend system error (getNodeById)";
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
	<tr class="node-name-wrap">
		<th scope="row"><label for="addnode-host"><?php echo __('Name', 'shadowsocks-hub'); ?></label></th>
		<td><input name="name" type="text" id="name" value="<?php echo $name; ?>" class="regular-text"/></td>
	</tr>
	<tr class="node-name-wrap">
		<th scope="row"><label for="role"><?php _e('Server', 'shadowsocks-hub'); ?></label></th>
		<td>
			<input type="text" name="serverId" id="serverId" value="<?php echo $serverName; ?>" disabled="disabled" class="regular-text"/> <span class="description"><?php _e('Server cannot be changed.', 'shadowsocks-hub'); ?></span>
		</td>
	</tr>
	<tr class="node-name-wrap">
		<th scope="row"><label for="addnode-host"><?php echo __('Password', 'shadowsocks-hub'); ?></label></th>
		<td><input type="text" name="password" id="password" value="<?php echo $password; ?>" class="regular-text"/></td>
	</tr>
	<tr class="node-name-wrap">
		<th scope="row"><label for="addnode-host"><?php echo __('Port', 'shadowsocks-hub'); ?></label></th>
		<td><input name="port" type="number" id="port" value="<?php echo $port; ?>" min="1" max="65535" class="regular-text"/></td>
	</tr>
	<tr class="node-name-wrap">
		<th scope="row"><label for="addnode-host"><?php echo __('Lower Bound', 'shadowsocks-hub'); ?></label></th>
		<td><input name="lowerBound" type="number" id="lowerBound" value="<?php echo $lowerBound; ?>" min="1" max="65535" class="regular-text"/></td>
	</tr>
	<tr class="node-name-wrap">
		<th scope="row"><label for="addnode-host"><?php echo __('Upper Bound', 'shadowsocks-hub'); ?></label></th>
		<td><input name="upperBound" type="number" id="upperBound" value="<?php echo $upperBound; ?>" min="1" max="65535" class="regular-text"/></td>
	</tr>
	<tr class="node-name-wrap">
		<th scope="row">
			<label for="addnode-host">
				<?php echo __('Comment', 'shadowsocks-hub'); ?>
				<span class="description"><?php _e( '(optional)', 'shadowsocks-hub' ); ?></span>
			</label></th>
		<td><input name="comment" type="text" id="comment" value="<?php echo $comment; ?>" class="regular-text"/></td>
	</tr>
</table>

<input type="hidden" name="action" value="update" />
<input type="hidden" name="serverId" id="serverId" value="<?php echo esc_attr($serverId); ?>" />
<input type="hidden" name="node_id" id="node_id" value="<?php echo esc_attr($node_id); ?>" />

<?php submit_button( 'Update Node', 'shadowsocks-hub' ); ?>

</form>
</div>
<?php
break;
}
?>