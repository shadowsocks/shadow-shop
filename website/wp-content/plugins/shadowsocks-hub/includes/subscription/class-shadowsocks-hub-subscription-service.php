<?php
class Shadowsocks_Hub_Subscription_Service
{

    /**
     * @return WP_Error|shadowsocks_account_array
     */
    static public function get_item($id)
    {
        $shadowsocksAccounts = Shadowsocks_Hub_Subscription_Dao::get_shadowsocks_accounts($id);
        return $shadowsocksAccounts;
    }

    public function create_item($request)
    {
        $item = $this->prepare_item_for_database($request);

        if (function_exists('slug_some_function_to_create_item')) {
            $data = slug_some_function_to_create_item($item);
            if (is_array($data)) {
                return new WP_REST_Response($data, 200);
            }
        }

        return new WP_Error('cant-create', __('message', 'text-domain'), array('status' => 500));
    }

    public function update_item($request)
    {
        $item = $this->prepare_item_for_database($request);

        if (function_exists('slug_some_function_to_update_item')) {
            $data = slug_some_function_to_update_item($item);
            if (is_array($data)) {
                return new WP_REST_Response($data, 200);
            }
        }

        return new WP_Error('cant-update', __('message', 'text-domain'), array('status' => 500));
    }

    public function delete_item($request)
    {
        $item = $this->prepare_item_for_database($request);

        if (function_exists('slug_some_function_to_delete_item')) {
            $deleted = slug_some_function_to_delete_item($item);
            if ($deleted) {
                return new WP_REST_Response(true, 200);
            }
        }

        return new WP_Error('cant-delete', __('message', 'text-domain'), array('status' => 500));
    }
}
