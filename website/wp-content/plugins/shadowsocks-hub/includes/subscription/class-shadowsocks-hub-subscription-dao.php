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

    static public function create_subscription($subscription)
    {
        global $wpdb;
        
        $return = $wpdb->insert(
            $wpdb->prefix . 'sshub_subscription',
            array(
                'id' => $subscription['id'],
                'userId' => $subscription['user_id'],
                'createdTime' => $subscription['created_time']
            )
        );
        return $return;
    }
}

