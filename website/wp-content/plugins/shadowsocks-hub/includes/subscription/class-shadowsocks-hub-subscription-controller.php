<?php

class Shadowsocks_Hub_Subscription_Controller extends WP_REST_Controller
{

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes()
    {
        $version = '1';
        $namespace = 'shadow-shop/v' . $version;
        $base = 'subscription';
        register_rest_route($namespace, '/' . $base, array(
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array($this, 'create_or_update_subscription'),
                'permission_callback' => array($this, 'create_item_permissions_check'),
                'args'                => $this->get_endpoint_args_for_item_schema(true),
            ),
        ));
        register_rest_route($namespace, '/' . $base . '/(?P<id>[\d]+)', array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array($this, 'get_subscription'),
                'permission_callback' => array($this, 'get_item_permissions_check'),
                'args'                => array(
                    'context' => array(
                        'default' => 'view',
                    ),
                ),
            )
        ));
    }

    /**
     * Get one item from the collection
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function get_subscription($request)
    {
        //get parameters from request
        $params = $request->get_params();
        $id = $params['id'];
        $item = Shadowsocks_Hub_Subscription_Service::get_subscription($id);
        $data = $this->prepare_item_for_response($item, $request);

        //return a response or error based on some conditional
        if (!is_wp_error($item)) {
            return new WP_REST_Response($data, 200);
        } else {
            return new WP_Error('500', get_error_message($item));
        }
    }

    /**
     * Create one item from the collection
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Request
     */
    public function create_or_update_subscription()
    {
        $result = Shadowsocks_Hub_Subscription_Service::create_or_update_subscription();

        if ($result == 1) {
            return new WP_REST_Response($result, 200);
        }

        return new WP_Error('cant-create', __('message', 'text-domain'), array('status' => 500));
    }

    /**
     * Check if a given request has access to get a specific item
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|bool
     */
    public function get_item_permissions_check($request)
    {
        return true;
    }

    /**
     * Check if a given request has access to create items
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|bool
     */
    public function create_item_permissions_check($request)
    {
        //return current_user_can('edit_something');
        return true;
    }

    /**
     * Prepare the item for the REST response
     *
     * @param mixed $item WordPress representation of the item.
     * @param WP_REST_Request $request Request object.
     * @return mixed
     */
    public function prepare_item_for_response($item, $request)
    {
        return $item;
    }
}
