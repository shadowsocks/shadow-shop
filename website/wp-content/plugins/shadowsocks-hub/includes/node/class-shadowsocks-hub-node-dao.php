<?php
class Shadowsocks_Hub_Node_Dao
{
    /**
     * @return WP_Error|true
     */
    public static function create_node($node)
    {
        $data_array = array(
            "name" => $node['name'],
            "serverId" => $node['serverId'],
            "protocol" => "shadowsocks",
            "password" => $node['password'],
            "port" => (int) $node['port'],
            "lowerBound" => (int) $node['lowerBound'],
            "upperBound" => (int) $node['upperBound'],
            "comment" => $node['comment'],
        );

        $return = Shadowsocks_Hub_Helper::call_api("POST", "http://sshub/api/node", json_encode($data_array));

        $error = $return['error'];
        $http_code = $return['http_code'];
        $response = $return['body'];

        if ($http_code === 201) {
            return true;
        } elseif ($http_code === 400) {
            $error_message = "Invalid input";
        } elseif ($http_code === 404) {
            $error_message = "Server does not exist";
        } elseif ($http_code === 409) {
            $error_message = "Node already exists";
        } elseif ($http_code === 500) {
            $error_message = "Backend system error (addNode)";
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
    public static function update_node($node)
    {
        $data_array = array(
            "id" => $node['id'],
            "name" => $node['name'],
            "protocol" => "shadowsocks",
            "password" => $node['password'],
            "port" => (int) $node['port'],
            "lowerBound" => (int) $node['lowerBound'],
            "upperBound" => (int) $node['upperBound'],
            "comment" => $node['comment'],
        );
        $return = Shadowsocks_Hub_Helper::call_api("PUT", "http://sshub/api/node", json_encode($data_array));

        $error = $return['error'];
        $http_code = $return['http_code'];
        $response = $return['body'];

        if ($http_code === 200) {
            return true;
        } elseif ($http_code === 400) {
            $error_message = "Invalid input";
        } elseif ($http_code === 404) {
            $error_message = "Node does not exist.";
        } elseif ($http_code === 409) {
            $error_message = "Node already exists.";
        } elseif ($http_code === 500) {
            $error_message = "Backend system error (updateNode)";
        } elseif ($error) {
            $error_message = "Backend system error: " . $error;
        } else {
            $error_message = "Backend system error undetected error.";
        }

        return new WP_Error('sshub_error', $error_message);
    }

    /**
     * @return WP_Error|shadowsocks_node
     */
    public static function get_node_by_id($id)
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
    public static function get_all_nodes()
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
     * @return WP_Error|shadowsocks_node
     */
    public static function ping_node_by_id($id)
    {
        $data_array = array(
            "id" => $id,
        );

        $ping_return = Shadowsocks_Hub_Helper::call_api("GET", "http://sshub/api/node/ping", $data_array);

        $error = $ping_return['error'];
        $http_code = $ping_return['http_code'];
        $response = $ping_return['body'];

        if ($http_code === 200) {
            $node_state = "ok";
            return $node_state;
        } elseif ($http_code === 400) {
            $error_message = "Backend system error (pingNodeById, invalid input)";
        } elseif ($http_code === 404) {
            $error_message = "Backend system error (pingNodeById, id does not exist)";
        } elseif ($http_code === 500) {
            $error_message = "Backend system error (pingNodeById, sshub error)";
        } elseif ($http_code === 522) {
            $error_message = "Backend system error (pingNodeById, shadowsocks restful api authToken input validation error)";
        } elseif ($http_code === 526) {
            $error_message = "Backend system error (pingNodeById, shadowsocks restful api authToken internal error)";
        } elseif ($http_code === 504) {
            $node_state = "shadowsocks restful api offline";
            return $node_state;
        } elseif ($http_code === 523) {
            $node_state = "shadowsocks restful api invalid password";
            return $node_state;
        } elseif ($http_code === 524) {
            $node_state = "shadowsocks-libev offline";
            return $node_state;
        } elseif ($http_code === 525) {
            $node_state = "shadowsocks-libev no response";
            return $node_state;
        } else {
            $error_message = "Backend system error undetected error.";
        }
        return new WP_Error('sshub_error', $error_message);
    }

    /**
     * @return WP_Error|true
     */
    public static function delete_node_by_id($id)
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
