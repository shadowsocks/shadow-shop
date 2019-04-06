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

		check_admin_referer('delete-accounts');

		if (empty($_REQUEST['accounts'])) {
			wp_redirect($redirect);
			exit();
		}

		$accountids = (array)$_REQUEST['accounts'];

		$update = 'del';
		$delete_count = 0;

		$error_messages = array();
		foreach ($accountids as $id) {

			$account = Shadowsocks_Hub_Account_Service::get_account_by_id($id);

			if (is_wp_error($account)) {
				$error_message = $account->get_error_message();
				$error_messages[] = urlencode($error_message);
				continue;
			}

			$userId = $account['purchase']['userId'];
			$host = $account['node']['server']['ipAddressOrDomainName'];
			$port = $account['port'];
			$user = get_user_by('id', (int)$userId);
			$userEmail = $user->data->user_email;

			$result = Shadowsocks_Hub_Account_Service::delete_account_by_id($id);

			if (is_wp_error($result)) {
				$error_message = $result->get_error_message();
				if ($error_message == 'Account is in use') {
					$error_message = "Account ($host; $port; $userEmail) is in use. Delete its usage first.";
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
		), admin_url('admin.php?page=shadowsocks_hub_accounts'));

		wp_redirect($redirect);
		exit();

	case 'delete':

		//check_admin_referer('delete-accounts');

		if (empty($_REQUEST['accounts']))
			$accountids = array($_REQUEST['account']);
		else
			$accountids = (array)$_REQUEST['accounts'];

		?>

	<form method="post" name="updateaccounts" id="updateaccounts">
		<?php wp_nonce_field('delete-accounts') ?>

		<div class="wrap">
			<h1><?php _e('Delete Accounts'); ?></h1>
			<?php if (isset($_REQUEST['error'])) : ?>
				<div class="error">
					<p><strong><?php _e('ERROR:', 'shadowsocks-hub'); ?></strong> <?php _e('Please select an option.', 'shadowsocks-hub'); ?></p>
				</div>
			<?php endif; ?>

			<?php if (1 == count($accountids)) : ?>
				<p><?php _e('You have specified this account for deletion:', 'shadowsocks-hub'); ?></p>
			<?php else : ?>
				<p><?php _e('You have specified these accounts for deletion:', 'shadowsocks-hub'); ?></p>
			<?php endif; ?>

			<ul>
				<?php
				$go_delete = 0;
				foreach ($accountids as $id) {

					$account = Shadowsocks_Hub_Account_Service::get_account_by_id($id);

					if (is_wp_error($account)) {
						$error_message = $account->get_error_message();
						echo "<li><input type=\"hidden\" name=\"accounts[]\" value=\"" . esc_attr($id) . "\" />" . sprintf(__('<strong> %1$s </strong>', 'shadowsocks-hub'), $error_message) . "</li>\n";
					} else {
						$userId = $account['purchase']['userId'];
						$host = $account['node']['server']['ipAddressOrDomainName'];
						$port = $account['port'];
						$user = get_user_by('id', (int)$userId);
						$userEmail = $user->data->user_email;

						echo "<li><input type=\"hidden\" name=\"accounts[]\" value=\"" . esc_attr($id) . "\" />" . sprintf(__('Host: <strong> %1$s </strong>; Port: <strong> %2$s </strong>; User: <strong> %3$s </strong>', 'shadowsocks-hub'), $host, $port, $userEmail) . "</li>\n";
						$go_delete++;
					}
				}
				?>
			</ul>
			<?php if ($go_delete) :
				?>
				<input type="hidden" name="action" value="dodelete" />
				<?php submit_button(__('Confirm Deletion', 'shadowsocks-hub'), 'primary'); ?>
			<?php else : ?>
				<p><?php _e('There are no valid accounts selected for deletion.', 'shadowsocks-hub'); ?></p>
			<?php endif; ?>
		</div>
	</form>
	<?php
	break;
default:

	$messages = array();
	if (isset($_GET['update'])) :
		switch ($_GET['update']) {
			case 'del':
			case 'del_many':
				$delete_count = isset($_GET['delete_count']) ? (int)$_GET['delete_count'] : 0;
				if (1 == $delete_count) {
					$message = __('Account deleted.', 'shadowsocks-hub');
				} else {
					$message = _n('%s accounts deleted.', '%s accounts deleted.', $delete_count, 'shadowsocks-hub');
				}
				$messages[] = '<div id="message" class="updated notice is-dismissible"><p>' . sprintf($message, number_format_i18n($delete_count)) . '</p></div>';
				break;
			case 'add':
				if (isset($_GET['id']) && ($user_id = $_GET['id']) && current_user_can('edit_user', $user_id)) {
					/* translators: %s: edit page url */
					$messages[] = '<div id="message" class="updated notice is-dismissible"><p>' . sprintf(
						__('New account added.', 'shadowsocks-hub'),
						esc_url(add_query_arg(
							'wp_http_referer',
							urlencode(wp_unslash($_SERVER['REQUEST_URI'])),
							self_admin_url('user-edit.php?user_id=' . $user_id)
						))
					) . '</p></div>';
				} else {
					$messages[] = '<div id="message" class="updated notice is-dismissible"><p>' . __('New account added.', 'shadowsocks-hub') . '</p></div>';
				}
				break;
		}
	endif; ?>

	<?php if (isset($_REQUEST['errors'])) : ?>
		<div class="error">
			<ul>
				<?php
				$error_messages = $_REQUEST['errors'];
				foreach ($error_messages as $err)
					echo "<li>$err</li>\n";
				?>
			</ul>
		</div>
	<?php endif;

if (!empty($messages)) {
	foreach ($messages as $msg)
		echo $msg;
}
?>
	<div class="wrap">
		<h2>
			<?php _e('Accounts', 'shadowsocks-hub'); ?>
			<a href="<?php echo admin_url('admin.php?page=shadowsocks_hub_add_account'); ?>" class="page-title-action"><?php echo esc_html_x('Add New', 'account', 'shadowsocks-hub'); ?></a>
		</h2>
		<?php

		$all_accounts = Shadowsocks_Hub_Account_Service::get_all_accounts();

		if (!is_wp_error($all_accounts)) {
			$data = array();
			$arr_length = count($all_accounts);

			for ($i = 0; $i < $arr_length; $i++) {
				$userId = $all_accounts[$i]['purchase']['userId'];
				$user = get_user_by('id', (int)$userId);
				$userEmail = $user->data->user_email;

				$data[] = array(
					'id' => $all_accounts[$i]['id'],
					'protocol' => $all_accounts[$i]['node']['protocol'],
					'host' => $all_accounts[$i]['node']['server']['ipAddressOrDomainName'],
					'port' => $all_accounts[$i]['port'],
					'password' => $all_accounts[$i]['password'],
					'user' => $userEmail,
					'orderId' => $all_accounts[$i]['purchase']['orderId'],
					'lifeSpan' => $all_accounts[$i]['purchase']['lifeSpan'],
					'encryption' => $all_accounts[$i]['method'],
					'created_date' => date_i18n(get_option('date_format'), $all_accounts[$i]['createdTime'] / 1000) . ' ' . date_i18n(get_option('time_format'), $all_accounts[$i]['createdTime'] / 1000),
					'epoch_time' => $all_accounts[$i]['createdTime'],
				);
			}

			$this->accounts_obj->set_table_data($data);
		} else {

			$error_message = $account->get_error_message();
			?>
			<div class="error">
				<ul>
					<?php
					echo "<li>$error_message</li>\n";
					?>
				</ul>
			</div>
		<?php } ?>
		<form id="shadowsocks-hub-accounts-list-form" method="get">
			<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
			<?php
			$this->accounts_obj->prepare_items();
			$this->accounts_obj->search_box(__('Search Accounts', 'shadowsocks-hub'), 'shadowsocks-hub-node-find');
			$this->accounts_obj->display();
			?>
		</form>
	</div>

<?php

} // end of the $doaction switch
?>