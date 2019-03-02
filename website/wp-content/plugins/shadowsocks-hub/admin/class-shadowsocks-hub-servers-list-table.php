<?php

class Shadowsocks_Hub_Servers_List_Table extends Shadowsocks_Hub_WP_List_Table
{
    /*
    * Used for store data passed in by page
    */
    private $table_data;

    /**
     * Constructor.
     *
     * @since 3.1.0
     *
     * @see WP_List_Table::__construct() for more information on default arguments.
     *
     * @param array $args An associative array of arguments.
     */
    public function __construct($args = array())
    {
        parent::__construct(array(
            'singular' => 'server',
            'plural' => 'servers',
            'screen' => isset($args['screen']) ? $args['screen'] : null,
        ));
    }

    /**
     * Output 'no servers' message.
     *
     * @since 3.1.0
     */
    public function no_items()
    {
        _e( 'No server found.' );
    }


    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();
        $data = $this->get_table_data();
        usort($data, array(&$this, 'sort_data'));
        $user = get_current_user_id();
        $screen = get_current_screen();
        $screen_option = $screen->get_option('per_page', 'option');
        $items_per_page = get_user_meta($user, $screen_option, true);
        if (empty($items_per_page) || $items_per_page < 1) {
            $items_per_page = $screen->get_option('per_page', 'default');
        }
        $currentPage = $this->get_pagenum();
        $totalItems = count($data);
        $this->set_pagination_args(array(
            'total_items' => $totalItems,
            'per_page' => $items_per_page
        ));
        $data = array_slice($data, (($currentPage - 1) * $items_per_page), $items_per_page);
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }

    public function set_table_data($input) {
        if (!is_array($input)) {
            throw new Exception("\$input must be an array");
        }
        $this->table_data = $input;
    }

    public function get_table_data() {
        return $this->table_data;
    }

    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array
     */
    public function get_columns()
    {
        $columns = array(
            'cb'       => '<input type="checkbox" />',
            'ip_address_or_domain_name' => __( 'Host' ),
            'created_date' => __( 'Created On' ),
        );
        return $columns;
    }
    /**
     * Define which columns are hidden
     *
     * @return Array
     */
    public function get_hidden_columns()
    {
        return array();
    }
    /**
     * Define the sortable columns
     *
     * @return Array
     */
    public function get_sortable_columns()
    {
        $c = array(
			'ip_address_or_domain_name' => array('ip_address_or_domain_name', true),
			'created_date' => array('created_date', false)
		);

		return $c;
    }
    
    /**
     * Allows you to sort the data by the variables set in the $_GET
     *
     * @return Mixed
     */
    private function sort_data($a, $b)
    {
        // Set defaults
        $orderby = 'ip_address_or_domain_name';
        $order = 'asc';
        // If orderby is set, use this as the sort column
        if (!empty($_GET['orderby'])) {
            $orderby = $_GET['orderby'];
            if ('created_date' === $orderby) {
                $orderby = 'epoch_time';
            }
        }
        // If order is set use this as the order
        if (!empty($_GET['order'])) {
            $order = $_GET['order'];
        }

        if ('epoch_time' === $orderby) {
            $result = $a[$orderby] - $b[$orderby];
        } else {
            $result = strcmp($a[$orderby], $b[$orderby]);
        }

        if ($order === 'asc') {
            return $result;
        }
        return -$result;
    }

    /**
	 * Retrieve an associative array of bulk actions available on this table.
	 *
	 * @since  3.1.0
	 *
	 * @return array Array of bulk actions.
	 */
	protected function get_bulk_actions() {
        $actions = array();
        $actions['delete'] = __( 'Delete' );
        return $actions;
    }
    
    /**
	 * Generate the list table rows.
	 */
	public function display_rows() {

		foreach ( $this->items as $server_object ) {
			echo "\n\t" . $this->single_row( $server_object);
		}
    }
    
    /**
	 * Generate HTML for a single row on the servers admin panel.
	 *
	 * @param $server_object The current server object.
	 * @return string Output for a single row.
	 */
	public function single_row( $server_object) {

        $server_id = $server_object['id'];

		// Set up the hover actions for this server
		$actions = array();
        $checkbox = '<label class="screen-reader-text" for="server_' . $server_id . '">' . sprintf( __( 'Select %s' ), $server_object['ip_address_or_domain_name'] ) . '</label>'
						. "<input type='checkbox' name='servers[]' id='server_{$server_id}' value='{$server_id}' />";

        
        $edit_link = add_query_arg( array(
            'page' => 'shadowsocks_hub_edit_server',
            'server_id' => $server_id,
        ), admin_url('admin.php') );

		$actions['edit'] = '<a href="' . $edit_link . '">' . __( 'Edit' ) . '</a>';
        $actions['delete'] = "<a class='submitdelete' href='" . wp_nonce_url( "admin.php?page=shadowsocks_hub_servers&amp;action=delete&amp;server=$server_id", 'delete-servers' ) . "'>" . __( 'Delete' ) . "</a>";

		$r = "<tr id='server-$server_id'>";

		list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();

		foreach ( $columns as $column_name => $column_display_name ) {
			$classes = "$column_name column-$column_name";
			if ( $primary === $column_name ) {
				$classes .= ' has-row-actions column-primary';
			}

			if ( in_array( $column_name, $hidden ) ) {
				$classes .= ' hidden';
			}

			$data = 'data-colname="' . wp_strip_all_tags( $column_display_name ) . '"';

			$attributes = "class='$classes' $data";

			if ( 'cb' === $column_name ) {
				$r .= "<th scope='row' class='check-column'>$checkbox</th>";
			} else {
				$r .= "<td $attributes>";
				switch ( $column_name ) {
                    case 'ip_address_or_domain_name':
                        $r .= $server_object['ip_address_or_domain_name'];
						break;
					case 'created_date':
						$r .= $server_object['created_date'];
						break;
					default:
				}

				if ( $primary === $column_name ) {
					$r .= $this->row_actions( $actions );
				}
				$r .= "</td>";
			}
		}
		$r .= '</tr>';

		return $r;
	}
}
