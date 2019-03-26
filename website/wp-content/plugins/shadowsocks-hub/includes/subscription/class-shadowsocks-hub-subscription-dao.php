<?php
class Shadowsocks_Hub_Subscription_Dao
{
    /**
     * @return WP_Error|shadowsocks_account_array
     */
    static public function get_shadowsocks_accounts($id)
    {
        $user_id = get_current_user_id();
        $user_id = "1";
        return Shadowsocks_Hub_Account_Dao::get_accounts_by_user_id($user_id);
    }

    static public function create_or_update_subscription($subscription)
    {
        $id = $subscription['id'];
        $userId = $subscription['user_id'];
        $createdTime = $subscription['created_time'];

        global $wpdb;

        $existing_subscription = $wpdb->get_results(
            'SELECT * FROM ' . $wpdb->prefix . 'sshub_subscription' .
            ' WHERE userId=' . $userId);

        if (sizeof($existing_subscription) == 1) {
            $return = $wpdb->update(
                $wpdb->prefix . 'sshub_subscription', 
                array(
                    'id' => $id,
                    'userId' => $userId,
                    'createdTime' => $createdTime
                ), 
                array(
                    'userId' => $userId
                )
            );
            error_log("wpdb update return =" . $return);
        } else {
            $return = $wpdb->insert(
                $wpdb->prefix . 'sshub_subscription',
                array(
                    'id' => $id,
                    'userId' => $userId,
                    'createdTime' => $createdTime
                )
            );
            error_log("wpdb insert return =" . $return);
        }
        return $return;
    }
}
