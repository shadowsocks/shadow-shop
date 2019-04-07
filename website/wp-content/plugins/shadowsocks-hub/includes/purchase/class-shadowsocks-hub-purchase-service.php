<?php
class Shadowsocks_Hub_Purchase_Service
{
    /**
     * @return WP_Error|true
     */
    public static function create_purchase($purchase)
    {
        return Shadowsocks_Hub_Purchase_Dao::create_purchase($purchase);
    }
}
