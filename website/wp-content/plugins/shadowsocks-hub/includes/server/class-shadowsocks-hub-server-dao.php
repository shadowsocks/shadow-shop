<?php
class Shadowsocks_Hub_Server_Dao
{
    /**
     * @return WP_Error|shadowsocks_server_array
     */
    public static function get_all_servers()
    {
        $return = Shadowsocks_Hub_Helper::call_api("GET", "http://sshub/api/server/all", false);

        $error = $return['error'];
        $http_code = $return['http_code'];
        $response = $return['body'];

        $data = array();
        if ($http_code === 200) {
            $allServers = $response;
            return $allServers;
        } elseif ($http_code === 500) {
            $error_message = "Backend system error (getAllServers)";
        } elseif ($error) {
            $error_message = "Backend system error: " . $error;
        } else {
            $error_message = "Backend system error undetected error.";
        }

        return new WP_Error('sshub_error', $error_message);
    }

}
