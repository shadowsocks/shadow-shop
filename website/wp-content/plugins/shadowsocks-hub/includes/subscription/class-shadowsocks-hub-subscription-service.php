<?php
class Shadowsocks_Hub_Subscription_Service
{

    /**
     * @return WP_Error|shadowsocks_account_array
     */
    static public function get_subscription($id)
    {
        $shadowsocksAccounts = Shadowsocks_Hub_Subscription_Dao::get_shadowsocks_accounts($id);
        return $shadowsocksAccounts;
    }

    static public function create_or_update_subscription()
    {
        $user_id = get_current_user_id();
        $user_id = "1";
        $random_string = Shadowsocks_Hub_Subscription_Service::generateRandomString();
        $created_time = (int) time();

        $subscription = array(
            'user_id' => $user_id,
            'id' => $random_string,
            'created_time' => $created_time
        );
        return Shadowsocks_Hub_Subscription_Dao::create_or_update_subscription($subscription);
    }

    static public function generateRandomString($length = 8) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
