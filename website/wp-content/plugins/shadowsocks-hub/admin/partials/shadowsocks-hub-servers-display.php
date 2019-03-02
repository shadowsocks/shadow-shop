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

		check_admin_referer('delete-servers');

		if (empty($_REQUEST['servers'])) {
			wp_redirect($redirect);
			exit();
		}

		$serverids = (array)$_REQUEST['servers'];

		$update = 'del';
		$delete_count = 0;

		$error_messages = array();
		foreach ($serverids as $id) {

			$data_array = array (
				"id" => $id,
			);
		
			$return = Shadowsocks_Hub_Helper::call_api("GET", "http://sshub/api/server", $data_array);
		
			$error = $return['error'];
			$http_code = $return['http_code'];
			$response = $return['body'];
		
			if ($http_code === 200) {
				$ip_address_or_domain_name = $response['ipAddressOrDomainName'];
			} elseif ($http_code === 400) {
				$error_messages[] = urlencode("Invalid server id");
				continue;
			} elseif ($http_code === 404) {
				$error_messages[] = urlencode("Server does not exist");
				continue; // no need to proceed with calling delete
			} elseif ($http_code === 500) {
				$error_messages[] = urlencode("Backend system error (getServerById)");
				continue; // no need to proceed with calling delete
			} elseif ($error) {
				$error_messages[] = urldecode("Backend system error: ".$error);
				continue;
			} else {
				$error_messages[] = urldecode("Backend system error undetected error.");
				continue;
			};
		
			$return = Shadowsocks_Hub_Helper::call_api("DELETE", "http://sshub/api/server", $data_array);
		
			$error = $return['error'];
			$http_code = $return['http_code'];
			$response = $return['body'];
		
			if ($http_code === 204) {
				++$delete_count;
			} elseif ($http_code === 400) {
				$error_messages[] = urlencode("Validation error");
			} elseif ($http_code === 409) {
				$error_messages[] = urlencode("$ip_address_or_domain_name is in use. Delete its nodes first.");
			} elseif ($http_code === 500) {
				$error_messages[] = urlencode("Backend system error (deleteServer)");
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
		), admin_url('admin.php?page=shadowsocks_hub_servers'));
		
		wp_redirect($redirect);
		exit();

	case 'delete':

        //check_admin_referer('delete-servers');
        
		if (empty($_REQUEST['servers']))
			$serverids = array($_REQUEST['server']);
		else
			$serverids = (array)$_REQUEST['servers'];

		?>

<form method="post" name="updateservers" id="updateservers">
<?php wp_nonce_field('delete-servers') ?>

<div class="wrap">
<h1><?php _e('Delete Servers', 'shadowsocks-hub'); ?></h1>
<?php if (isset($_REQUEST['error'])) : ?>
	<div class="error">
		<p><strong><?php _e('ERROR:', 'shadowsocks-hub'); ?></strong> <?php _e('Please select an option.', 'shadowsocks-hub'); ?></p>
	</div>
<?php endif; ?>

<?php if (1 == count($serverids)) : ?>
	<p><?php _e('You have specified this server for deletion:', 'shadowsocks-hub'); ?></p>
<?php else : ?>
	<p><?php _e('You have specified these servers for deletion:', 'shadowsocks-hub'); ?></p>
<?php endif; ?>

<ul>
<?php
$go_delete = 0;
foreach ($serverids as $id) {

	$data_array = array (
		"id" => $id,
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
	
	if ($http_code === 200) {
		echo "<li><input type=\"hidden\" name=\"servers[]\" value=\"" . esc_attr($id) . "\" />" . sprintf(__('<strong> %1$s </strong>'), $ip_address_or_domain_name) . "</li>\n";
		$go_delete++;	
	} else {
		echo "<li><input type=\"hidden\" name=\"servers[]\" value=\"" . esc_attr($id) . "\" />" . sprintf(__('<strong> %1$s </strong>'), $error_message) . "</li>\n";
	}
}
?>
	</ul>
<?php if ($go_delete) :
?>
	<input type="hidden" name="action" value="dodelete" />
	<?php submit_button(__('Confirm Deletion', 'shadowsocks-hub'), 'primary'); ?>
<?php else : ?>
	<p><?php _e('There are no valid servers selected for deletion.', 'shadowsocks-hub'); ?></p>
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
				$message = __( 'Server deleted.', 'shadowsocks-hub' );
			} else {
				$message = _n( '%s servers deleted.', '%s servers deleted.', $delete_count, 'shadowsocks-hub' );
			}
			$messages[] = '<div id="message" class="updated notice is-dismissible"><p>' . sprintf( $message, number_format_i18n( $delete_count ) ) . '</p></div>';
			break;
		case 'add':
			if ( isset( $_GET['id'] ) && ( $user_id = $_GET['id'] ) && current_user_can( 'edit_user', $user_id ) ) {
				/* translators: %s: edit page url */
				$messages[] = '<div id="message" class="updated notice is-dismissible"><p>' . sprintf( __( 'New server added.', 'shadowsocks-hub' ),
					esc_url( add_query_arg( 'wp_http_referer', urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ),
						self_admin_url( 'user-edit.php?user_id=' . $user_id ) ) ) ) . '</p></div>';
			} else {
				$messages[] = '<div id="message" class="updated notice is-dismissible"><p>' . __( 'New server added.', 'shadowsocks-hub' ) . '</p></div>';
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
		<?php _e('Servers'); ?>
		<a href="<?php echo admin_url('admin.php?page=shadowsocks_hub_add_server'); ?>" class="page-title-action"><?php echo esc_html_x('Add New', 'server'); ?></a>
	</h2>
	<?php
	$return = Shadowsocks_Hub_Helper::call_api("GET", "http://sshub/api/server/all", false);

    $error = $return['error'];
    $http_code = $return['http_code'];
	$response = $return['body'];

	$data = array();
	if ($http_code === 200) {
        $arr_length = count($response);

        for ($i = 0; $i < $arr_length; $i++) {
            $data[] = array(
                'id' => $response[$i]['id'],
                'ip_address_or_domain_name' => $response[$i]['ipAddressOrDomainName'],
                'created_date' => date_i18n(get_option('date_format'), $response[$i]['createdTime'] / 1000).' '.date_i18n(get_option('time_format'), $response[$i]['createdTime'] / 1000),
                'epoch_time' => $response[$i]['createdTime'],
            );
		}
	} elseif ($http_code === 500) {
		$error_message = "Backend system error (getAllServers)";
	} elseif ($error) {
		$error_message = "Backend system error: ".$error;
	} else {
		$error_message = "Backend system error undetected error.";
	}; 

	if ($http_code === 200) {
		$this->servers_obj->set_table_data($data);
	} else { ?>
		<div class="error">
		<ul>
		<?php
			echo "<li>$error_message</li>\n";
		?>
		</ul>
	</div>
	<?php
	}
	?>
	<form id="shadowsocks-hub-servers-list-form" method="get">
		<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
		<?php
			$this->servers_obj->prepare_items();
			$this->servers_obj->search_box(__('Search Servers', 'shadowsocks-hub'), 'shadowsocks-hub-server-find');
			$this->servers_obj->display();
	?>					
	</form>
</div>

<?php

} // end of the $doaction switch
?>