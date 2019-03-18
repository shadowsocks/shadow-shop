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
                'callback'            => array($this, 'create_item'),
                'permission_callback' => array($this, 'create_item_permissions_check'),
                'args'                => $this->get_endpoint_args_for_item_schema(true),
            ),
        ));
        register_rest_route($namespace, '/' . $base . '/(?P<id>[\d]+)', array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array($this, 'get_item'),
                'permission_callback' => array($this, 'get_item_permissions_check'),
                'args'                => array(
                    'context' => array(
                        'default' => 'view',
                    ),
                ),
            ),
            array(
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => array($this, 'update_item'),
                'permission_callback' => array($this, 'update_item_permissions_check'),
                'args'                => $this->get_endpoint_args_for_item_schema(false),
            ),
            array(
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => array($this, 'delete_item'),
                'permission_callback' => array($this, 'delete_item_permissions_check'),
                'args'                => array(
                    'force' => array(
                        'default' => false,
                    ),
                ),
            ),
        ));
    }

    /**
     * Get one item from the collection
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function get_item($request)
    {
        //get parameters from request
        $params = $request->get_params();
        $id = $params['id'];
        $item = Shadowsocks_Hub_Subscription_Service::get_item($id);
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

    /**
     * Update one item from the collection
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Request
     */
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

    /**
     * Delete one item from the collection
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Request
     */
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
        return current_user_can('edit_something');
    }

    /**
     * Check if a given request has access to update a specific item
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|bool
     */
    public function update_item_permissions_check($request)
    {
        return $this->create_item_permissions_check($request);
    }

    /**
     * Check if a given request has access to delete a specific item
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|bool
     */
    public function delete_item_permissions_check($request)
    {
        return $this->create_item_permissions_check($request);
    }

    /**
     * Prepare the item for create or update operation
     *
     * @param WP_REST_Request $request Request object
     * @return WP_Error|object $prepared_item
     */
    protected function prepare_item_for_database($request)
    {
        return array();
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
