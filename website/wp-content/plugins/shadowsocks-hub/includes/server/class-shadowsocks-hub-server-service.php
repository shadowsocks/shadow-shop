<?php
class Shadowsocks_Hub_Server_Service
{
    /**
     * @return WP_Error|shadowsocks_server
     */
    public static function get_server_by_id($id)
    {
        return Shadowsocks_Hub_Server_Dao::get_server_by_id($id);
    }

    /**
     * @return WP_Error|shadowsocks_server_array
     */
    public static function get_all_servers()
    {
        return Shadowsocks_Hub_Server_Dao::get_all_servers();
    }

    /**
     * @return WP_Error|true
     */
    public static function delete_server_by_id($id)
    {
        return Shadowsocks_Hub_Server_Dao::delete_server_by_id($id);
    }
}
