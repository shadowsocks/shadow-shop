<?php
class Shadowsocks_Hub_Traffic_Service
{
    /**
     * @return WP_Error|account_usage
     */
    static public function get_all_account_usage_for_current_user()
    {
        $user_id = get_current_user_id();

        return Shadowsocks_Hub_Traffic_Dao::get_all_account_usage_by_user_id($user_id);
    }
}
