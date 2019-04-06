<?php
class Shadowsocks_Hub_Account_Service
{

    /**
     * @return WP_Error|true
     */
    static public function create_account($account)
    {
        return Shadowsocks_Hub_Account_Dao::create_account($account);
    }

    /**
     * @return WP_Error|shadowsocks_account_array
     */
    static public function get_accounts_for_current_user()
    {
        $user_id = get_current_user_id();

        return Shadowsocks_Hub_Account_Dao::get_accounts_by_user_id($user_id);
    }

    /**
     * @return WP_Error|shadowsocks_account
     */
    static public function get_account_by_id($id)
    {
        return Shadowsocks_Hub_Account_Dao::get_account_by_id($id);
    }

    /**
     * @return WP_Error|shadowsocks_account_array
     */
    static public function get_all_accounts()
    {
        return Shadowsocks_Hub_Account_Dao::get_all_accounts();
    }

    /**
     * @return WP_Error|true
     */
    static public function delete_account_by_id($id)
    {
        return Shadowsocks_Hub_Account_Dao::delete_account_by_id($id);
    }
}
