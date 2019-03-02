<?php

class Shadowsocks_Hub_Accounts_List_Table extends Shadowsocks_Hub_WP_List_Table
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
            'singular' => 'account',
            'plural' => 'accounts',
            'screen' => isset($args['screen']) ? $args['screen'] : null,
        ));
    }

    /**
     * Output 'no account' message.
     *
     * @since 3.1.0
     */
    public function no_items()
    {
        _e( 'No account found.' );
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
            'host' => __( 'Host' ),
            'port' => __( 'Port' ),
            'protocol' => __( 'Protocol' ),
            'encryption' => __( 'Encryption' ),
            'password' => __( 'Password' ),
            'user' => __( 'User' ),
            'orderId' => __( 'Order ID' ),
            'lifeSpan' => __( 'Life Span' ),
            'created_date' => __( 'Created On' )
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
            'host' => array('host', true),
            'port' => array('port', false),
            'user' => array('user', false),
            'orderId' => array('orderId', false),
            'lifeSpan' => array('lifeSpan', false),
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
        $orderby = 'host';
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

		foreach ( $this->items as $account_object ) {
			echo "\n\t" . $this->single_row( $account_object);
		}
    }
    
    /**
	 * Generate HTML for a single row on the nodes admin panel.
	 *
	 * @param $account_object The current node object.
	 * @return string Output for a single row.
	 */
	public function single_row( $account_object) {

        $account_id = $account_object['id'];

		// Set up the hover actions for this node
		$actions = array();
        $checkbox = '<label class="screen-reader-text" for="account_' . $account_id . '">' . sprintf( __( 'Select %s' ), $account_object['host'] ) . '</label>'
						. "<input type='checkbox' name='accounts[]' id='accounts_{$account_id}' value='{$account_id}' />";

        
        $edit_link = add_query_arg( array(
            'page' => 'shadowsocks_hub_edit_account',
            'account_id' => $account_id,
        ), admin_url('admin.php') );

		$actions['edit'] = '<a href="' . $edit_link . '">' . __( 'Edit' ) . '</a>';
        $actions['delete'] = "<a class='submitdelete' href='" . wp_nonce_url( "admin.php?page=shadowsocks_hub_accounts&amp;action=delete&amp;account=$account_id", 'delete-accounts' ) . "'>" . __( 'Delete' ) . "</a>";

		$r = "<tr id='account-$account_id'>";

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
					case 'host':
						$r .= $account_object['host'];
                        break;
                    case 'protocol':
					    $r .= $account_object['protocol'];
                        break;
                    case 'encryption':
                        $r .= $account_object['encryption'];
                        break;
                    case 'password':
					    $r .= $account_object['password'];
                        break;
                    case 'port':
					    $r .= $account_object['port'];
                        break;
                    case 'user':
					    $r .= $account_object['user'];
                        break;
                    case 'orderId':
					    $r .= $account_object['orderId'];
                        break;
                    case 'lifeSpan':
					    $r .= $account_object['lifeSpan'];
                        break;
                    case 'created_date':
					    $r .= $account_object['created_date'];
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
