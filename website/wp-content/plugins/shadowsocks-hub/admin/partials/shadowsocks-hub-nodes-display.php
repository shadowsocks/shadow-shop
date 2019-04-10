<?php

/**
 * The admin area of the plugin to load the User List Table
 */

if (isset($_REQUEST['action']) && -1 != $_REQUEST['action']) {
    $current_action = $_REQUEST['action'];
} elseif (isset($_REQUEST['action2']) && -1 != $_REQUEST['action2']) {
    $current_action = $_REQUEST['action2'];
} else {
    $current_action = null;
}

switch ($current_action) {
    case 'dodelete':

        check_admin_referer('delete-nodes');

        if (empty($_REQUEST['nodes'])) {
            wp_redirect($redirect);
            exit();
        }

        $nodeids = (array) $_REQUEST['nodes'];

        $update = 'del';
        $delete_count = 0;

        $error_messages = array();
        foreach ($nodeids as $id) {

            $node = Shadowsocks_Hub_Node_Service::get_node_by_id($id);

            if (is_wp_error($node)) {
                $error_message = $node->get_error_message();
                $error_messages[] = urlencode($error_message);
                continue;
            }

            $name = $node['name'];

            $return = Shadowsocks_Hub_Node_Service::delete_node_by_id($id);

            if (is_wp_error($return)) {
                $error_message = $return->get_error_message();
                if ($error_message == 'Node is in use') {
                    $error_message = "Node $name is in use. Delete its accounts first.";
                }
                $error_messages[] = urlencode($error_message);
                continue;
            }

            ++$delete_count;
        }

        $redirect = add_query_arg(array(
            'delete_count' => $delete_count,
            'update' => $update,
            'errors' => $error_messages,
        ), admin_url('admin.php?page=shadowsocks_hub_nodes'));

        wp_redirect($redirect);
        exit();

    case 'delete':

        //check_admin_referer('delete-nodes');

        if (empty($_REQUEST['nodes'])) {
            $nodeids = array($_REQUEST['node']);
        } else {
            $nodeids = (array) $_REQUEST['nodes'];
        }

        ?>

<form method="post" name="updatenodes" id="updatenodes">
<?php wp_nonce_field('delete-nodes')?>

<div class="wrap">
<h1><?php _e('Delete Nodes', 'shadowsocks-hub');?></h1>
<?php if (isset($_REQUEST['error'])): ?>
	<div class="error">
		<p><strong><?php _e('ERROR:', 'shadowsocks-hub');?></strong> <?php _e('Please select an option.', 'shadowsocks-hub');?></p>
	</div>
<?php endif;?>

<?php if (1 == count($nodeids)): ?>
	<p><?php _e('You have specified this node for deletion:', 'shadowsocks-hub');?></p>
<?php else: ?>
	<p><?php _e('You have specified these nodes for deletion:', 'shadowsocks-hub');?></p>
<?php endif;?>

<ul>
<?php
$go_delete = 0;
        foreach ($nodeids as $id) {

            $node = Shadowsocks_Hub_Node_Service::get_node_by_id($id);

            if (!is_wp_error($node)) {
                $name = $node['name'];
                echo "<li><input type=\"hidden\" name=\"nodes[]\" value=\"" . esc_attr($id) . "\" />" . sprintf(__('<strong> %1$s </strong>'), $name) . "</li>\n";
                $go_delete++;
            } else {
                $error_message = $node->get_error_message();
                echo "<li><input type=\"hidden\" name=\"nodes[]\" value=\"" . esc_attr($id) . "\" />" . sprintf(__('<strong> %1$s </strong>'), $error_message) . "</li>\n";
            }
        }
        ?>
	</ul>
<?php if ($go_delete):
        ?>
	<input type="hidden" name="action" value="dodelete" />
	<?php submit_button(__('Confirm Deletion', 'shadowsocks-hub'), 'primary');?>
<?php else: ?>
	<p><?php _e('There are no valid nodes selected for deletion.', 'shadowsocks-hub');?></p>
<?php endif;?>
</div>
</form>
<?php
break;
    default:

        $messages = array();
        if (isset($_GET['update'])):
            switch ($_GET['update']) {
                case 'del':
                case 'del_many':
                    $delete_count = isset($_GET['delete_count']) ? (int) $_GET['delete_count'] : 0;
                    if (1 == $delete_count) {
                        $message = __('Node deleted.');
                    } else {
                        $message = _n('%s nodes deleted.', '%s nodes deleted.', $delete_count);
                    }
                    $messages[] = '<div id="message" class="updated notice is-dismissible"><p>' . sprintf($message, number_format_i18n($delete_count)) . '</p></div>';
                    break;
                case 'add':
                    if (isset($_GET['id']) && ($user_id = $_GET['id']) && current_user_can('edit_user', $user_id)) {
                        /* translators: %s: edit page url */
                        $messages[] = '<div id="message" class="updated notice is-dismissible"><p>' . sprintf(__('New node added.', 'shadowsocks-hub'),
                            esc_url(add_query_arg('wp_http_referer', urlencode(wp_unslash($_SERVER['REQUEST_URI'])),
                                self_admin_url('user-edit.php?user_id=' . $user_id)))) . '</p></div>';
                    } else {
                        $messages[] = '<div id="message" class="updated notice is-dismissible"><p>' . __('New node added.', 'shadowsocks-hub') . '</p></div>';
                    }
                    break;
            }
        endif;?>

<?php if (isset($_REQUEST['errors'])): ?>
	<div class="error">
		<ul>
		<?php
$error_messages = $_REQUEST['errors'];
        foreach ($error_messages as $err) {
            echo "<li>$err</li>\n";
        }

        ?>
		</ul>
	</div>
<?php endif;

        if (!empty($messages)) {
            foreach ($messages as $msg) {
                echo $msg;
            }

        }
        ?>
<div class="wrap">
	<h2>
		<?php _e('Nodes', 'shadowsocks-hub');?>
		<a href="<?php echo admin_url('admin.php?page=shadowsocks_hub_add_node'); ?>" class="page-title-action"><?php echo esc_html_x('Add New', 'node'); ?></a>
	</h2>
	<?php
$error_messages = array();
        $error_occurred = false;
        $data = array();
        $allNodes = Shadowsocks_Hub_Node_Service::get_all_nodes();

        if (!is_wp_error($allNodes)) {

            foreach ($allNodes as $node) {

                $id = $node['id'];
                $node_state = Shadowsocks_Hub_Node_Service::ping_node_by_id($id);
                if (is_wp_error(($node_state))) {
                    $error_message = $node_state->get_error_message();
                    $error_messages[] = $error_message;
                    $node_state = "system error";
                }

                $data[] = array(
                    'id' => $node['id'],
                    'name' => $node['name'],
                    'node_state' => $node_state,
                    'host' => $node['server']['ipAddressOrDomainName'],
                    'protocol' => $node['protocol'],
                    'password' => $node['password'],
                    'port' => $node['port'],
                    'lower_bound' => $node['lowerBound'],
                    'upper_bound' => $node['upperBound'],
                    'comment' => $node['comment'],
                    'created_date' => date_i18n(get_option('date_format'), $node['createdTime'] / 1000) . ' ' . date_i18n(get_option('time_format'), $node['createdTime'] / 1000),
                    'epoch_time' => $node['createdTime'],
                );
            }

            $this->nodes_obj->set_table_data($data);
        } else {
            $error_message = $allNodes->get_error_message();
            $error_messages[] = $error_message;
        }

        if (!empty($error_messages)) {?>
            <div class="error">
            <ul>
                <?php foreach ($error_messages as $err) {
            echo "<li>$err</li>\n";
        }?>
		</ul>
	</div>
	<?php }?>
	<form id="shadowsocks-hub-nodes-list-form" method="get">
		<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
		<?php
$this->nodes_obj->prepare_items();
        $this->nodes_obj->search_box(__('Search Nodes'), 'shadowsocks-hub-node-find');
        $this->nodes_obj->display();
        ?>
	</form>
</div>

<?php

} // end of the $doaction switch
?>