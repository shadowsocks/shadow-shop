<?php

if (isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];
} else {
    $action = null;
}
;

if (isset($_REQUEST['node_id'])) {
    $node_id = $_REQUEST['node_id'];
} else {
    $node_id = null;
}
;

switch ($action) {
    case 'update':
        check_admin_referer('update-node_' . $node_id);

        $edit_link = add_query_arg(array(
            'page' => 'shadowsocks_hub_edit_node',
            'node_id' => $node_id,
        ), admin_url('admin.php'));

        $node = array(
            "id" => $node_id,
            "name" => $_REQUEST['name'],
            "protocol" => "shadowsocks",
            "password" => $_REQUEST['password'],
            "port" => (int) $_REQUEST['port'],
            "lowerBound" => (int) $_REQUEST['lowerBound'],
            "upperBound" => (int) $_REQUEST['upperBound'],
            "comment" => $_REQUEST['comment'],
        );

        $return = Shadowsocks_Hub_Node_Service::update_node($node);

        if (!is_wp_error($return)) {
            $redirect = add_query_arg('updated', true, $edit_link);
        } else {
            $redirect = add_query_arg(array(
                'error' => urlencode($error_msg),
            ), $edit_link);
        }

        wp_redirect($redirect);
        die();

    default:
        ?>

<?php if (isset($_GET['updated'])): ?>
<div id="message" class="updated notice is-dismissible">
	<p><strong><?php _e('Node updated.', 'shadowsocks-hub')?></strong></p>
</div>
<?php endif;?>
<?php if (isset($_REQUEST['error'])): ?>
	<div class="error">
		<ul>
		<?php $err = urldecode($_REQUEST['error']);
        echo "<li>$err</li>\n";
        ?>
		</ul>
	</div>
<?php endif;?>

<div class="wrap" id="node-edit-page">
<h1 class="wp-heading-inline">Edit Node</h1>

<hr class="wp-header-end">

<form action="<?php $edit_link = add_query_arg(array(
            'page' => 'shadowsocks_hub_edit_node',
        ), admin_url('admin.php'));

        echo esc_url($edit_link);
        ?>" method="post" novalidate="novalidate">
<?php wp_nonce_field('update-node_' . $node_id);

        $node = Shadowsocks_Hub_Node_Service::get_node_by_id($node_id);
        if (!is_wp_error($node)) {
            $name = $node['name'];
            $serverName = $node['server']['ipAddressOrDomainName'];
            $serverId = $node['server']['id'];
            $password = $node['password'];
            $port = $node['port'];
            $lowerBound = $node['lowerBound'];
            $upperBound = $node['upperBound'];
            $comment = $node['comment'];
        } else {
            $error_message = $node->get_error_message();
            ?>
	<div class="error">
		<ul>
			<li><?php echo $error_message; ?></li>
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
		<th scope="row"><label for="role"><?php _e('Server', 'shadowsocks-hub');?></label></th>
		<td>
			<input type="text" name="serverId" id="serverId" value="<?php echo $serverName; ?>" disabled="disabled" class="regular-text"/> <span class="description"><?php _e('Server cannot be changed.', 'shadowsocks-hub');?></span>
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
				<span class="description"><?php _e('(optional)', 'shadowsocks-hub');?></span>
			</label></th>
		<td><input name="comment" type="text" id="comment" value="<?php echo $comment; ?>" class="regular-text"/></td>
	</tr>
</table>

<input type="hidden" name="action" value="update" />
<input type="hidden" name="serverId" id="serverId" value="<?php echo esc_attr($serverId); ?>" />
<input type="hidden" name="node_id" id="node_id" value="<?php echo esc_attr($node_id); ?>" />

<?php submit_button('Update Node', 'shadowsocks-hub');?>

</form>
</div>
<?php
break;
}
?>