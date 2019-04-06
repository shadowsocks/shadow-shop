<?php
class Shadowsocks_Hub_Node_Service
{
    /**
     * @return WP_Error|shadowsocks_node
     */
    static public function get_node_by_id($id)
    {
        return Shadowsocks_Hub_Node_Dao::get_node_by_id($id);
    }

    /**
     * @return WP_Error|shadowsocks_node_array
     */
    static public function get_all_nodes()
    {
        return Shadowsocks_Hub_Node_Dao::get_all_nodes();
    }

    /**
     * @return WP_Error|true
     */
    static public function delete_node_by_id($id)
    {
        return Shadowsocks_Hub_Node_Dao::delete_node_by_id($id);
    }
}