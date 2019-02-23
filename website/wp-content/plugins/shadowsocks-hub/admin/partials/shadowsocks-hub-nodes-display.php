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

		$nodeids = (array)$_REQUEST['nodes'];

		$update = 'del';
		$delete_count = 0;

		$error_messages = array();
		foreach ($nodeids as $id) {

			$data_array = array (
				"id" => $id,
			);
		
			$return = Shadowsocks_Hub_Helper::call_api("GET", "http://sshub/api/node", $data_array);
		
			$error = $return['error'];
			$http_code = $return['http_code'];
			$response = $return['body'];
		
			if ($http_code === 200) {
				$name = $response['name'];
			} elseif ($http_code === 400) {
				$error_messages[] = urlencode("Invalid node id");
				continue;
			} elseif ($http_code === 404) {
				$error_messages[] = urlencode("Node does not exist");
				continue; // no need to proceed with calling delete
			} elseif ($http_code === 500) {
				$error_messages[] = urlencode("Backend system error (getNodeById)");
				continue; // no need to proceed with calling delete
			} elseif ($error) {
				$error_messages[] = urldecode("Backend system error: ".$error);
				continue;
			} else {
				$error_messages[] = urldecode("Backend system error undetected error.");
				continue;
			};
		
			$return = Shadowsocks_Hub_Helper::call_api("DELETE", "http://sshub/api/node", $data_array);
		
			$error = $return['error'];
			$http_code = $return['http_code'];
			$response = $return['body'];
		
			if ($http_code === 204) {
				++$delete_count;
			} elseif ($http_code === 400) {
				$error_messages[] = urlencode("Validation error");
			} elseif ($http_code === 409) {
				$error_messages[] = urlencode("$name is in use. Delete its accounts first.");
			} elseif ($http_code === 500) {
				$error_messages[] = urlencode("Backend system error (deleteNode)");
			} elseif ($error) {
				$error_messages[] = urldecode("Backend system error: ".$error);	
			} else {
				$error_messages[] = urldecode("Backend system error undetected error.");
			};
		}

		$redirect = add_query_arg( array(
			'delete_count' => $delete_count, 
			'update' => $update,
			'errors' => $error_messages,
		), admin_url('admin.php?page=shadowsocks_hub_nodes'));
		
		wp_redirect($redirect);
		exit();

	case 'delete':

        //check_admin_referer('delete-nodes');
        
		if (empty($_REQUEST['nodes']))
			$nodeids = array($_REQUEST['node']);
		else
			$nodeids = (array)$_REQUEST['nodes'];

		?>

<form method="post" name="updatenodes" id="updatenodes">
<?php wp_nonce_field('delete-nodes') ?>

<div class="wrap">
<h1><?php _e('Delete Nodes'); ?></h1>
<?php if (isset($_REQUEST['error'])) : ?>
	<div class="error">
		<p><strong><?php _e('ERROR:'); ?></strong> <?php _e('Please select an option.'); ?></p>
	</div>
<?php endif; ?>

<?php if (1 == count($nodeids)) : ?>
	<p><?php _e('You have specified this node for deletion:'); ?></p>
<?php else : ?>
	<p><?php _e('You have specified these nodes for deletion:'); ?></p>
<?php endif; ?>

<ul>
<?php
$go_delete = 0;
foreach ($nodeids as $id) {

	$data_array = array (
		"id" => $id,
	);

	$return = Shadowsocks_Hub_Helper::call_api("GET", "http://sshub/api/node", $data_array);

    $error = $return['error'];
    $http_code = $return['http_code'];
	$response = $return['body'];

	if ($http_code === 200) {
		$name = $response['name'];
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
	
	if ($http_code === 200) {
		echo "<li><input type=\"hidden\" name=\"nodes[]\" value=\"" . esc_attr($id) . "\" />" . sprintf(__('<strong> %1$s </strong>'), $name) . "</li>\n";
		$go_delete++;	
	} else {
		echo "<li><input type=\"hidden\" name=\"nodes[]\" value=\"" . esc_attr($id) . "\" />" . sprintf(__('<strong> %1$s </strong>'), $error_message) . "</li>\n";
	}
}
?>
	</ul>
<?php if ($go_delete) :
?>
	<input type="hidden" name="action" value="dodelete" />
	<?php submit_button(__('Confirm Deletion'), 'primary'); ?>
<?php else : ?>
	<p><?php _e('There are no valid nodes selected for deletion.'); ?></p>
<?php endif; ?>
</div>
</form>
<?php
break;
default:

$messages = array();
	if ( isset($_GET['update']) ) :
		switch($_GET['update']) {
		case 'del':
		case 'del_many':
			$delete_count = isset($_GET['delete_count']) ? (int) $_GET['delete_count'] : 0;
			if ( 1 == $delete_count ) {
				$message = __( 'Node deleted.' );
			} else {
				$message = _n( '%s nodes deleted.', '%s nodes deleted.', $delete_count );
			}
			$messages[] = '<div id="message" class="updated notice is-dismissible"><p>' . sprintf( $message, number_format_i18n( $delete_count ) ) . '</p></div>';
			break;
		case 'add':
			if ( isset( $_GET['id'] ) && ( $user_id = $_GET['id'] ) && current_user_can( 'edit_user', $user_id ) ) {
				/* translators: %s: edit page url */
				$messages[] = '<div id="message" class="updated notice is-dismissible"><p>' . sprintf( __( 'New node added.' ),
					esc_url( add_query_arg( 'wp_http_referer', urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ),
						self_admin_url( 'user-edit.php?user_id=' . $user_id ) ) ) ) . '</p></div>';
			} else {
				$messages[] = '<div id="message" class="updated notice is-dismissible"><p>' . __( 'New node added.' ) . '</p></div>';
			}
			break;
		}
	endif; ?>

<?php if ( isset($_REQUEST['errors']) ) : ?>
	<div class="error">
		<ul>
		<?php
			$error_messages = $_REQUEST['errors'];
			foreach ( $error_messages as $err )
			echo "<li>$err</li>\n";
		?>
		</ul>
	</div>
<?php endif;

if ( ! empty($messages) ) {
	foreach ( $messages as $msg )
		echo $msg;
}
	?>
<div class="wrap">    
	<h2>
		<?php _e('Nodes'); ?>
		<a href="<?php echo admin_url('admin.php?page=shadowsocks_hub_add_node'); ?>" class="page-title-action"><?php echo esc_html_x('Add New', 'node'); ?></a>
	</h2>
	<?php
	$error_messages = array();
	$error_occurred = false;
	$return = Shadowsocks_Hub_Helper::call_api("GET", "http://sshub/api/node/all", false);

    $error = $return['error'];
    $http_code = $return['http_code'];
	$response = $return['body'];

	$data = array();
	if ($http_code === 200) {
        $arr_length = count($response);

        for ($i = 0; $i < $arr_length; $i++) {

			$data_array = array (
				"id" => $response[$i]['id'],
			);

			$ping_return = Shadowsocks_Hub_Helper::call_api("GET", "http://sshub/api/node/ping", $data_array);

    		$ping_error = $ping_return['error'];
    		$ping_http_code = $ping_return['http_code'];
			$ping_response = $ping_return['body'];

			$node_state = "";
			error_log("ping_http_code = " . $ping_http_code);
			if ( $ping_http_code === 200 ) {
				$node_state = "ok";
			} elseif ( $ping_http_code === 400 ) {
				$node_state = "system error";
				$error_messages[] = "Backend system error (pingNodeById, invalid input)";
				$error_occurred = true;
			} elseif ( $ping_http_code === 404 ) {
				$node_state = "system error";
				$error_messages[] = "Backend system error (pingNodeById, id does not exist)";
				$error_occurred = true;
			} elseif ( $ping_http_code === 522 ) {
				$node_state = "system error";
				$error_messages[] = "Backend system error (pingNodeById, shadowsocks restful api authToken input validation error)";
				$error_occurred = true;
			} elseif ( $ping_http_code === 523 ) {
				$node_state = "system error";
				$error_messages[] = "Backend system error (pingNodeById, shadowsocks restful api authToken invalid password)";
				$error_occurred = true;
			} elseif ( $ping_http_code === 526 ) {
				$node_state = "system error";
				$error_messages[] = "Backend system error (pingNodeById, shadowsocks restful api authToken internal error)";
				$error_occurred = true;
			} elseif ( $ping_http_code === 504 ) {
				$node_state = "shadowsocks restful api offline";
			} elseif ( $ping_http_code === 523 ) {
				$node_state = "system error";
				$error_messages[] = "Backend system error (pingNodeById, authentication to shadowsocks restful api failed)";
				$error_occurred = true;
			} elseif ( $ping_http_code === 524 ) {
				$node_state = "shadowsocks-libev offline";
			} elseif ( $ping_http_code === 525 ) {
				$node_state = "shadowsocks-libev no response";
			} else {
				$node_state = "system error";
				$error_messages[] = "Backend system error undetected error.";
				$error_occurred = true;
			};
			
            $data[] = array(
				'id' => $response[$i]['id'],
				'name' => $response[$i]['name'],
				'node_state' => $node_state,
				'host' => $response[$i]['server']['ipAddressOrDomainName'],
				'protocol' => $response[$i]['protocol'],
				'password' => $response[$i]['password'],
				'port'	=> $response[$i]['port'],
				'lower_bound' => $response[$i]['lowerBound'],
				'upper_bound' => $response[$i]['upperBound'],
				'comment' => $response[$i]['comment'],
                'created_date' => date_i18n(get_option('date_format'), $response[$i]['createdTime'] / 1000).' '.date_i18n(get_option('time_format'), $response[$i]['createdTime'] / 1000),
                'epoch_time' => $response[$i]['createdTime'],
            );
		}
	} elseif ($http_code === 500) {
		$error_messages[] = "Backend system error (getAllNodes)";
		$error_occurred = true;
	} elseif ($error) {
		$error_messages[] = "Backend system error: ".$error;
		$error_occurred = true;
	} else {
		$error_messages[] = "Backend system error undetected error.";
		$error_occurred = true;
	}; 

	if ( $http_code === 200 ) {
		$this->nodes_obj->set_table_data($data);
	};
	if ( $error_occurred ) { ?>
		<div class="error">
		<ul>
			<?php
				foreach ( $error_messages as $err )
					echo "<li>$err</li>\n";
			?>
		</ul>
	</div>
	<?php
	}
	?>
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