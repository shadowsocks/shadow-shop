<?php
class Shadowsocks_Hub_Subscription_Service
{

    /**
     * @return WP_Error|shadowsocks_account_array
     */
    static public function get_item($id)
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

        error_log("subscription =" . implode(';', $subscription));

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

    public function update_item($request)
    {
        $item = $this->prepare_item_for_database($request);

        if (function_exists('slug_some_function_to_update_item')) {
            $data = slug_some_function_to_update_item($item);
            if (is_array($data)) {
                return new WP_REST_Response($data, 200);
            }
        }

        return new WP_Error('cant-update', __('message', 'text-domain'), array('status' => 500));
    }

    public function delete_item($request)
    {
        $item = $this->prepare_item_for_database($request);

        if (function_exists('slug_some_function_to_delete_item')) {
            $deleted = slug_some_function_to_delete_item($item);
            if ($deleted) {
                return new WP_REST_Response(true, 200);
            }
        }

        return new WP_Error('cant-delete', __('message', 'text-domain'), array('status' => 500));
    }
}
