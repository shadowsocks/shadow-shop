<?php
class Shadowsocks_Hub_Server_Dao
{
    /**
     * @return WP_Error|true
     */
    public static function create_server($server)
    {
        $data_array = array (
            "ipAddressOrDomainName" => $server['ipAddressOrDomainName']
        );
    
        $return = Shadowsocks_Hub_Helper::call_api("POST", "http://sshub/api/server", json_encode($data_array));
    
        $error = $return['error'];
        $http_code = $return['http_code'];
        $response = $return['body'];
    
        if ($http_code === 201) {
            return true;
        } elseif ($http_code === 400) {
            $error_message = "Invalid domain name or IP address";
        } elseif ($http_code === 409) {
            $error_message = "Server already exists";
        } elseif ($http_code === 500) {
            $error_message = "Backend system error (addServer)";
        } elseif ($error) {
            $error_message = "Backend system error: ".$error;
        } else {
            $error_message = "Backend system error undetected error.";
        }

        return new WP_Error('sshub_error', $error_message);
    }

    /**
     * @return WP_Error|shadowsocks_server
     */
    public static function get_server_by_id($id)
    {
        $data_array = array(
            "id" => $id,
        );

        $return = Shadowsocks_Hub_Helper::call_api("GET", "http://sshub/api/server", $data_array);

        $error = $return['error'];
        $http_code = $return['http_code'];
        $response = $return['body'];

        if ($http_code === 200) {
            return $response;
        } elseif ($http_code === 400) {
            $error_message = "Invalid server id";
        } elseif ($http_code === 404) {
            $error_message = "Server does not exist";
        } elseif ($http_code === 500) {
            $error_message = "Backend system error (getServerById)";
        } elseif ($error) {
            $error_message = "Backend system error: " . $error;
        } else {
            $error_message = "Backend system error undetected error.";
        }

        return new WP_Error('sshub_error', $error_message);
    }

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

    /**
     * @return WP_Error|true
     */
    public static function delete_server_by_id($id)
    {
        $data_array = array(
            "id" => $id,
        );

        $return = Shadowsocks_Hub_Helper::call_api("DELETE", "http://sshub/api/server", $data_array);

        $error = $return['error'];
        $http_code = $return['http_code'];
        $response = $return['body'];

        if ($http_code === 204) {
            return true;
        } elseif ($http_code === 400) {
            $error_message = "Validation error";
        } elseif ($http_code === 409) {
            $error_message = "Server is in use";
        } elseif ($http_code === 500) {
            $error_message = "Backend system error (deleteServer)";
        } elseif ($error) {
            $error_message = "Backend system error: " . $error;
        } else {
            $error_message = "Backend system error undetected error.";
        }

        return new WP_Error('sshub_error', $error_message);
    }
}
