<?php
class Shadowsocks_Hub_Traffic_Dao
{
    /**
     * @return WP_Error|account_usage
     */
    public static function get_all_account_usage_by_user_id($user_id)
    {
        $data_array = array(
            "uiType" => "wordpress",
            "userId" => $user_id,
        );

        $return = Shadowsocks_Hub_Helper::call_api("GET", "http://sshub/api/traffic/user", $data_array);

        $error = $return['error'];
        $http_code = $return['http_code'];
        $response = $return['body'];

        if ($http_code === 200) {
            $accountUsages = $response;
            return $accountUsages;
        } elseif ($http_code === 500) {
            $error_message = "Backend system error (getLatestUsageByUserId)";
        } elseif ($error) {
            $error_message = "Backend system error: " . $error;
        } else {
            $error_message = "Backend system error undetected error.";
        }

        return new WP_Error('sshub_error', $error_message);
    }
}
