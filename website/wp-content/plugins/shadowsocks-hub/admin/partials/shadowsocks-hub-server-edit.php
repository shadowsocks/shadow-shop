<?php

if (isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];
} else {
    $action = null;
}
;

if (isset($_REQUEST['server_id'])) {
    $server_id = $_REQUEST['server_id'];
} else {
    $server_id = null;
}
;

switch ($action) {
    case 'update':
        check_admin_referer('update-server_' . $server_id);

        $edit_link = add_query_arg(array(
            'page' => 'shadowsocks_hub_edit_server',
            'server_id' => $server_id,
        ), admin_url('admin.php'));

        $server = array(
            "id" => $server_id,
            "ipAddressOrDomainName" => $_REQUEST['ip_address_or_domain_name'],
        );

        $return = Shadowsocks_Hub_Server_Service::update_server($server);

        if (!is_wp_error($return)) {
            $redirect = add_query_arg('updated', true, $edit_link);
        } else {
            $error_message = $return->get_error_message();
            $redirect = add_query_arg(array(
                'error' => urlencode($error_message),
            ), $edit_link);
        }

        wp_redirect($redirect);
        die();

    default:
        ?>

<?php if (isset($_GET['updated'])): ?>
<div id="message" class="updated notice is-dismissible">
	<p><strong><?php _e('Server updated.')?></strong></p>
</div>
<?php endif;?>
<?php if (isset($_REQUEST['error'])): ?>
	<div class="error">
		<ul>
		<?php
$err = urldecode($_REQUEST['error']);
        echo "<li>$err</li>\n";
        ?>
		</ul>
	</div>
<?php endif;?>

<div class="wrap" id="server-edit-page">
<h1 class="wp-heading-inline">Edit Server</h1>


<hr class="wp-header-end">

<form action="<?php
$edit_link = add_query_arg(array(
            'page' => 'shadowsocks_hub_edit_server',
        ), admin_url('admin.php'));

        echo esc_url($edit_link);
        ?>" method="post" novalidate="novalidate">
<?php wp_nonce_field('update-server_' . $server_id);

        $server = Shadowsocks_Hub_Server_Service::get_server_by_id($server_id);

        if (!is_wp_error($server)) {
            $ip_address_or_domain_name = $server['ipAddressOrDomainName'];
        } else {
            $error_message = $server->get_error_message();?>
	<div class="error">
		<ul>
			<li><?php echo $error_message; ?></li>
		</ul>
	</div>
<?php }?>

<table class="form-table">
	<tr class="server-host-wrap">
		<th><label for="ip_address_or_domain_name"><?php _e('Host', 'shadowsocks-hub')?></label></th>
		<td><input type="text" name="ip_address_or_domain_name" id="ip_address_or_domain_name" value="<?php echo esc_attr($ip_address_or_domain_name); ?>" class="regular-text" /></td>
	</tr>
</table>

<input type="hidden" name="action" value="update" />
<input type="hidden" name="server_id" id="server_id" value="<?php echo esc_attr($server_id); ?>" />

<?php submit_button(__('Update Server', 'shadowsocks-hub'));?>

</form>
</div>
<?php
break;
}
?>