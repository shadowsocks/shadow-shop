<?php
class Shadowsocks_Hub_Node_Dao
{
    /**
     * @return WP_Error|shadowsocks_node
     */
    static public function get_node_by_id($id)
    {
        $data_array = array(
            "id" => $id,
        );

        $return = Shadowsocks_Hub_Helper::call_api("GET", "http://sshub/api/node", $data_array);

        $error = $return['error'];
        $http_code = $return['http_code'];
        $response = $return['body'];

        if ($http_code === 200) {
            return $response;
        } elseif ($http_code === 400) {
            $error_message = "Invalid node id";
        } elseif ($http_code === 404) {
            $error_message = "Node does not exist";
        } elseif ($http_code === 500) {
            $error_message = "Backend system error (getNodeById)";
        } elseif ($error) {
            $error_message = "Backend system error: " . $error;
        } else {
            $error_message = "Backend system error undetected error.";
        };

        return new WP_Error('sshub_error', $error_message);
    }

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

    /**
     * @return WP_Error|true
     */
    static public function delete_node_by_id($id)
    {
        $data_array = array(
            "id" => $id,
        );

        $return = Shadowsocks_Hub_Helper::call_api("DELETE", "http://sshub/api/node", $data_array);

        $error = $return['error'];
        $http_code = $return['http_code'];
        $response = $return['body'];

        if ($http_code === 204) {
            return true;
        } elseif ($http_code === 400) {
            $error_message = "Validation error";
        } elseif ($http_code === 409) {
            $error_message = "Node is in use";
        } elseif ($http_code === 500) {
            $error_message = "Backend system error (deleteNode)";
        } elseif ($error) {
            $error_message = "Backend system error: " . $error;
        } else {
            $error_message = "Backend system error undetected error";
        };

        return new WP_Error('sshub_error', $error_message);
    }
}
