<?php
class Shadowsocks_Hub_Account_Dao
{
    /**
     * @return WP_Error|true
     */
    static public function create_account($account)
    {
        $data_array = array(
            "type" => "SsAccount",
            "uiType" => "wordpress",
            "nodeId" => $account['nodeId'],
            "userId" => $account['userId'],
            "orderId" => "adminApproval",
            "lifeSpan" => $account['lifeSpan'],
            "method" => $account['method'],
            "traffic" => (int)$account['traffic'],
        );

        $return = Shadowsocks_Hub_Helper::call_api("POST", "http://sshub/api/account", json_encode($data_array));

        $error = $return['error'];
        $http_code = $return['http_code'];
        $response = $return['body'];

        if ($http_code === 201) {
            return true;
        } elseif ($http_code === 400) {
            $error_message = "Invalid input";
        } elseif ($http_code === 404) {
            $error_message = "Node does not exist";
        } elseif ($http_code === 500) {
            $error_message = "Backend system error (addAccountByAdmin)";
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
    static public function update_account($account)
    {
        $data_array = array(
            "id" => $account['id'],
            "type" => "SsAccount",
            "port" => (int)$account['port'],
            "password" => $account['password'],
            "method" => $account['method'],
        );

        $return = Shadowsocks_Hub_Helper::call_api("PUT", "http://sshub/api/account", json_encode($data_array));

        $error = $return['error'];
        $http_code = $return['http_code'];
        $response = $return['body'];

        if ($http_code === 200) {
            return true;
        } elseif ($http_code === 400) {
            $error_message = "Invalid input.";
        } elseif ($http_code === 404) {
            $error_message = "Account does not exist.";
        } elseif ($http_code === 409) {
            $error_message = "New port has already been used.";
        } elseif ($http_code === 410) {
            $error_message = "Type does not match.";
        } elseif ($http_code === 500) {
            $error_message = "Backend system error (updateAccount)";
        } elseif ($error) {
            $error_message = "Backend system error: " . $error;
        } else {
            $error_message = "Backend system error undetected error.";
        }

        return new WP_Error('sshub_error', $error_message);
    }

    /**
     * @return WP_Error|shadowsocks_account_array
     */
    static public function get_accounts_by_user_id($user_id)
    {
        $data_array = array(
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
            $error_message = "Backend system error: " . $error;
        } else {
            $error_message = "Backend system error undetected error.";
        };

        return new WP_Error('sshub_error', $error_message);
    }

    /**
     * @return WP_Error|shadowsocks_account
     */
    static public function get_account_by_id($id)
    {
        $data_array = array(
            "ids" => array($id),
        );

        $return = Shadowsocks_Hub_Helper::call_api("GET", "http://sshub/api/account/accounts", $data_array);

        $error = $return['error'];
        $http_code = $return['http_code'];
        $response = $return['body'];

        if ($http_code === 200) {
            return $response[0];
        } elseif ($http_code === 400) {
            $error_message = "Invalid account id";
        } elseif ($http_code === 500) {
            $error_message = "Backend system error (getAccountsByIds)";
        } elseif ($error) {
            $error_message = "Backend system error: " . $error;
        } else {
            $error_message = "Backend system error undetected error.";
        };

        return new WP_Error('sshub_error', $error_message);
    }

    /**
     * @return WP_Error|shadowsocks_account_array
     */
    static public function get_all_accounts()
    {
        $return = Shadowsocks_Hub_Helper::call_api("GET", "http://sshub/api/account/all", false);

        $error = $return['error'];
        $http_code = $return['http_code'];
        $response = $return['body'];

        if ($http_code === 200) {
            return $response;
        } elseif ($http_code === 500) {
            $error_message = "Backend system error (getAllAccounts)";
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
    static public function delete_account_by_id($id)
    {
        $data_array = array(
            "id" => $id,
        );

        $return = Shadowsocks_Hub_Helper::call_api("DELETE", "http://sshub/api/account", $data_array);

        $error = $return['error'];
        $http_code = $return['http_code'];
        $response = $return['body'];

        if ($http_code === 204) {
            return true;
        } elseif ($http_code === 400) {
            $error_message = "Validation error";
        } elseif ($http_code === 409) {
            $error_message = "Account is in use";
        } elseif ($http_code === 500) {
            $error_message = "Backend system error (deleteAccount)";
        } elseif ($error) {
            $error_message = "Backend system error: " . $error;
        } else {
            $error_message = "Backend system error undetected error.";
        };

        return new WP_Error('sshub_error', $error_message);
    }
}
