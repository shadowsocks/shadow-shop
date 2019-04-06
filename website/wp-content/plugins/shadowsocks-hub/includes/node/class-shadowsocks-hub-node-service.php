<?php
class Shadowsocks_Hub_Node_Service
{
    /**
     * @return WP_Error|shadowsocks_node_array
     */
    static public function get_all_nodes()
    {
        return Shadowsocks_Hub_Node_Dao::get_all_nodes();
    }
}