<?php
class Shadowsocks_Hub_Account_Service
{

    /**
     * @return WP_Error|shadowsocks_account_array
     */
    static public function get_accounts_for_current_user()
    {
        $user_id = get_current_user_id();

        return Shadowsocks_Hub_Account_Dao::get_accounts_by_user_id($user_id);
     }
}

