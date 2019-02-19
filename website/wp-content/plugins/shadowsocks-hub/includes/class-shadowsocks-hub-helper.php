<?php

class Shadowsocks_Hub_Helper
{


    static function call_api($method, $url, $data = false)
    {
        $curl = curl_init();

        switch ($method) {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "DELETE":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
                if ($data)
                    $url = sprintf("%s?%s", $url, http_build_query($data));
                break;
            default:
                if ($data)
                    $url = sprintf("%s?%s", $url, http_build_query($data));
        }
     
        // OPTIONS:
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
        ));
        curl_setopt($curl, CURLOPT_FAILONERROR, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    
        // EXECUTE:
        $result = curl_exec($curl);

        // ERROR HANDLING:
        $return = array();

        $return['http_code'] = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if (!$result) {
            $return['error'] = curl_error($curl);
        }
            
        if ($return['http_code'] >= 200 && $return['http_code'] < 300) {
            $return['body'] = json_decode($result, true);
        } else {
            $return['body'] = $result;
        }
        
        curl_close($curl);

        if (!isset($return['http_code'])) {
            $return['http_code'] = null;
        }
        if (!isset($return['error'])) {
            $return['error'] = null;
        }
        if (!isset($return['body'])) {
            $return['body'] = null;
        }
        return $return;
    }
}
