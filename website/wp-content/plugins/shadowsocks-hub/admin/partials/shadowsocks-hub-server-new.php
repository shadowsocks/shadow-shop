<?php

if (isset($_REQUEST['action']) && 'addserver' == $_REQUEST['action']) {
    check_admin_referer('add-server', '_wpnonce_add-server');

    $server = array(
        "ipAddressOrDomainName" => $_REQUEST['ip_address_or_domain_name'],
    );

    $return = Shadowsocks_Hub_Server_Service::create_server($server);
    if (!is_wp_error($return)) {
        $redirect = add_query_arg(array(
            'update' => 'add',
        ), admin_url('admin.php?page=shadowsocks_hub_servers'));
    } else {
        $error_message = $return->get_error_message();
        $redirect = add_query_arg(array(
            'error' => urlencode($error_message),
        ), admin_url('admin.php?page=shadowsocks_hub_add_server'));
    }

    wp_redirect($redirect);
    die();
}
?>
<div class="wrap">
<h1 id="add-new-server"><?php _e('Add New Server', 'shadowsocks-hub');?>
</h1>

<?php if (isset($_REQUEST['error'])): ?>
	<div class="error">
		<ul>
		<?php
$err = urldecode($_REQUEST['error']);
echo "<li>$err</li>\n";
?>
		</ul>
	</div>
<?php endif;

if (!empty($messages)) {
    foreach ($messages as $msg) {
        echo '<div id="message" class="updated notice is-dismissible"><p>' . $msg . '</p></div>';
    }

}?>

<form method="post" name="addserver" id="addserver" class="validate" novalidate="novalidate">
<input name="action" type="hidden" value="addserver" />
<?php wp_nonce_field('add-server', '_wpnonce_add-server')?>

<table class="form-table">
	<tr class="server-host-wrap">
		<th scope="row"><label for="addserver-host"><?php _e('Host', 'shadowsocks-hub');?></label></th>
		<td><input name="ip_address_or_domain_name" type="text" id="ip_address_or_domain_name" value="" class="regular-text"/></td>
	</tr>
</table>

<?php submit_button(__('Add Server', 'shadowsocks-hub'));?>
</form>