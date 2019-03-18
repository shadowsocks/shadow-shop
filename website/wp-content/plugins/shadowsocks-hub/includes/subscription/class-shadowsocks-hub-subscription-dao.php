<?php
class Shadowsocks_Hub_Subscription_Dao
{
    /**
     * @return WP_Error|shadowsocks_account_array
     */
    static public function get_shadowsocks_accounts($id)
    {
        // TODO: get_user_id
        $user_id = "1";
        return Shadowsocks_Hub_Account_Dao::get_accounts_by_user_id($user_id);
     }
}

