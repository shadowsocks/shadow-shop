<?php
class Shadowsocks_Hub_Subscription_Dao
{
    static public function get_user_id($id)
    {
        $user_id = "1";
        return $user_id;
    }

    static public function get_shadowsocks_accounts_by_user_id($user_id)
    {
        $data_array = array (
            "uiType" => "wordpress",
            "userId" => $user_id,
        );
                    
        $return = Shadowsocks_Hub_Helper::call_api("GET", "http://sshub/api/account/accounts_by_user_id", $data_array);
        
        $error = $return['error'];
        $http_code = $return['http_code'];
        $response = $return['body'];
        
        if ($http_code === 200) {
            $accounts = $response;
            return $accounts;
        } elseif ($http_code === 500) {
            $error_message = "Backend system error (getAccountsByUserId)";
        } elseif ($error) {
            $error_message = "Backend system error: ".$error;
        } else {
            $error_message = "Backend system error undetected error.";
        }; 
     }
}

