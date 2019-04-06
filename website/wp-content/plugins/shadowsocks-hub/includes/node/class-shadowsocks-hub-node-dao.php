<?php
class Shadowsocks_Hub_Node_Dao
{
    /**
     * @return WP_Error|shadowsocks_node_array
     */
    static public function get_all_nodes()
    {
        $return = Shadowsocks_Hub_Helper::call_api("GET", "http://sshub/api/node/all", false);

        $error = $return['error'];
        $http_code = $return['http_code'];
        $response = $return['body'];

        if ($http_code === 200) {
            $allNodes = $response;
            return $allNodes;
        } elseif ($http_code === 500) {
            $error_message = "Backend system error (getAllNodes)";
        } elseif ($error) {
            $error_message = "Backend system error: " . $error;
        } else {
            $error_message = "Backend system error undetected error.";
        };

        return new WP_Error('sshub_error', $error_message);
    }
}
