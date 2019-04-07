<?php
class Shadowsocks_Hub_Node_Service
{
    /**
     * @return WP_Error|true
     */
    public static function create_node($node)
    {
        return Shadowsocks_Hub_Node_Dao::create_node($node);
    }

    /**
     * @return WP_Error|true
     */
    public static function update_node($node)
    {
        return Shadowsocks_Hub_Node_Dao::update_node($node);
    }

    /**
     * @return WP_Error|shadowsocks_node
     */
    public static function get_node_by_id($id)
    {
        return Shadowsocks_Hub_Node_Dao::get_node_by_id($id);
    }

    /**
     * @return WP_Error|shadowsocks_node_array
     */
    public static function get_all_nodes()
    {
        return Shadowsocks_Hub_Node_Dao::get_all_nodes();
    }

    /**
     * @return WP_Error|shadowsocks_node
     */
    public static function ping_node_by_id($id)
    {
        return Shadowsocks_Hub_Node_Dao::ping_node_by_id($id);
    }

    /**
     * @return WP_Error|true
     */
    public static function delete_node_by_id($id)
    {
        return Shadowsocks_Hub_Node_Dao::delete_node_by_id($id);
    }
}
