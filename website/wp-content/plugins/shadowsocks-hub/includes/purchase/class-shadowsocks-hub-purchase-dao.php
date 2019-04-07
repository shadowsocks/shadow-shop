<?php
class Shadowsocks_Hub_Purchase_Dao
{
    /**
     * @return WP_Error|true
     */
    public static function create_purchase($purchase)
    {
        $data_array = array(
            "uiType" => "wordpress",
            "userId" => (string) $purchase['userId'],
            "orderId" => $purchase['orderId'],
            "lifeSpan" => $purchase['lifeSpan'],
            "traffic" => (int) $purchase['traffic'],
            "accountParameters" => array(
                "type" => "shadowsocks",
                "method" => $purchase['method'],
            ),
        );

        $return = Shadowsocks_Hub_Helper::call_api("POST", "http://sshub/api/purchase", json_encode($data_array));

        $error = $return['error'];
        $http_code = $return['http_code'];
        $response = $return['body'];

        if ($http_code === 201) {
            return true;
        } elseif ($http_code === 400) {
            $error_message = "Invalid input";
        } elseif ($http_code === 409) {
            // Normal. Purchase was added before. Do nothing
            return true;
        } elseif ($http_code === 500) {
            $error_message = "Backend system error (addPurchase)";
        } elseif ($error) {
            $error_message = "Backend system error: " . $error;
        } else {
            $error_message = "Backend system error undetected error.";
        }

        return new WP_Error('sshub_error', $error_message);
    }
}
