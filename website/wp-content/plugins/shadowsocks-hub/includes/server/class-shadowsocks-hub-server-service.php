<?php
class Shadowsocks_Hub_Server_Service
{
    /**
     * @return WP_Error|shadowsocks_server_array
     */
    public static function get_all_servers()
    {
        return Shadowsocks_Hub_Server_Dao::get_all_servers();
    }
}