<?php

class Shadowsocks_Hub_Nodes_List_Table extends Shadowsocks_Hub_WP_List_Table
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
            'singular' => 'node',
            'plural' => 'nodes',
            'screen' => isset($args['screen']) ? $args['screen'] : null,
        ));
    }

    /**
     * Output 'no nodes' message.
     *
     * @since 3.1.0
     */
    public function no_items()
    {
        _e('No node found.');
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
            'name' => 'Name',
            'node_state' => 'State',
            'host' => 'Host',
            'protocol' => 'Protocol',
            'password' => 'Password',
            'port' => 'Post',
            'lower_bound' => 'Lower Bound',
            'upper_bound' => 'Upper Bound',
            'created_date' => 'Created On'
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
            'name' => array('name', true),
            'host' => array('host', false),
            'protocol' => array('protocol', false),
			'ip_address_or_domain_name' => array('ip_address_or_domain_name', false),
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
        $orderby = 'name';
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

		foreach ( $this->items as $node_object ) {
			echo "\n\t" . $this->single_row( $node_object);
		}
    }
    
    /**
	 * Generate HTML for a single row on the nodes admin panel.
	 *
	 * @param $node_object The current node object.
	 * @return string Output for a single row.
	 */
	public function single_row( $node_object) {

        $node_id = $node_object['id'];

		// Set up the hover actions for this node
		$actions = array();
        $checkbox = '<label class="screen-reader-text" for="node_' . $node_id . '">' . sprintf( __( 'Select %s' ), $node_object['name'] ) . '</label>'
						. "<input type='checkbox' name='nodes[]' id='node_{$node_id}' value='{$node_id}' />";

        
        $edit_link = add_query_arg( array(
            'page' => 'shadowsocks_hub_edit_node',
            'node_id' => $node_id,
        ), admin_url('admin.php') );

		$actions['edit'] = '<a href="' . $edit_link . '">' . __( 'Edit' ) . '</a>';
        $actions['delete'] = "<a class='submitdelete' href='" . wp_nonce_url( "admin.php?page=shadowsocks_hub_nodes&amp;action=delete&amp;node=$node_id", 'delete-nodes' ) . "'>" . __( 'Delete' ) . "</a>";

		$r = "<tr id='node-$node_id'>";

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
                    case 'name':
                        $r .= $node_object['name'];
                        break;
                    case 'node_state':
                        $r .= $node_object['node_state'];
						break;
					case 'host':
						$r .= $node_object['host'];
                        break;
                    case 'protocol':
					    $r .= $node_object['protocol'];
                        break;
                    case 'password':
					    $r .= $node_object['password'];
                        break;
                    case 'port':
					    $r .= $node_object['port'];
                        break;
                    case 'lower_bound':
					    $r .= $node_object['lower_bound'];
                        break;
                    case 'upper_bound':
					    $r .= $node_object['upper_bound'];
                        break;
                    case 'comment':
					    $r .= $node_object['comment'];
                        break;
                    case 'created_date':
					    $r .= $node_object['created_date'];
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
