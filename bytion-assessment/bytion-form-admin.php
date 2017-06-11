<?php 
/**
 * Admin Dashboard Page for Bytion Form 
 */

// disable direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Bytion_Main_Page {

	/**
	 * Constructor will create Bytion admin dashboard.
	 */
	public function __construct()
	{   
		add_action('admin_menu', array($this, 'bytion_top_menu'));
	}

	public function bytion_top_menu() 
	{
		wp_enqueue_style( 'bytion-admin-style', plugins_url('/css/bytion-admin.css',__FILE__) );
		add_menu_page('Bytion Intro', 'Bytion', 'manage_options', __FILE__, array($this, 'bytion_plugin_page'), plugins_url('/img/icon.png',__FILE__), 25 );
		
		// Using different name for the first submenu item
		add_submenu_page(__FILE__, 'Bytion Introduction', 'Introduction', 'manage_options', __FILE__ );
		add_submenu_page(__FILE__, 'Bytion Submission Entries', 'Submission Entries', 'manage_options', __FILE__.'/display-form', array($this, 'bytion_display_form') );
	}
	public function bytion_plugin_page() 
	{
		?>
		<div id="bytion_wrapper" class="wrap about-wrap">
			<h1 class="wp-heading-inline">Bytion Assessment 1.0</h1>
			<p class="about-text">
				Thank you for taking the time to review this plugin! I really appreciate the opportunity and looking forward to work on future projects together.
			</p>
		</div>
		<?php
	}
	public function bytion_display_form()
	{
		$ListTable = new Bytion_Main_List_Table();
		$ListTable->prepare_items();
		?>
		<div id="bytion_wrapper" class="wrap">
		<span class="dashicons dashicons-smiley"></span>
			<h1 class="wp-heading-inline">Bytion Form Submission Entries</h1>
			<?php $ListTable->display(); ?>
		</div>
		<?php
	}
}

/**
 * Load WP_List_Table
 */
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Create a new table class that will extend the WP_List_Table
 */
class Bytion_Main_List_Table extends WP_List_Table
{   

	/**
	 * Prepare the items for the table to process
	 */
	public function prepare_items()
	{   

		global $wpdb;

		$table_name  = $wpdb->prefix.'bytion_form';
		$columns     = $this->get_columns();
		$hidden      = $this->get_hidden_columns();
		$sortable		 = $this->get_sortable_columns();
		$data        = $this->table_data();
		$perPage     = 10;
		$currentPage = $this->get_pagenum(); 

		$totalItems   = $wpdb->get_var("SELECT COUNT(*) FROM $table_name"); 

		$this->set_pagination_args( array(
			'total_items' => $totalItems,
			'per_page'    => $perPage
		) );

		$this->_column_headers = array($columns, $hidden, $sortable);
		
		//add sorting capability to bytion form
		usort( $data, array( &$this, 'usort_reorder' ) );
		
		$this->items = $data;
	}
	/**
	 * Override the parent columns method. Defines the columns to use in your listing table
	 */
	public function get_columns()
	{
		$columns = array(
			'fid' => 'Form ID',
			'name'=> 'Name',
			'email'=> 'Email',
			'time'=> 'Timestamp'
		);
		return $columns;
	}
	/**
	 * Define which columns are hidden
	 */
	public function get_hidden_columns()
	{
		return array();
	}

	/**
	 * Get the table data
	*/
	private function table_data()
	{   
		global $wpdb;

		$data        = array();
		$table_name  = $wpdb->prefix.'bytion_form';
		$page         = $this->get_pagenum();
		$page         = $page - 1;
		$start        = $page * 10;
		
		$bytion_datas = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY form_id ASC LIMIT $start,10", OBJECT );
		
		foreach( $bytion_datas as $bytion_data ) {			
			$data_value['fid']	= $bytion_data->form_id;
			$data_value['name'] = $bytion_data->form_name;
			$data_value['email'] = $bytion_data->form_email;
			$data_value['time'] = $bytion_data->form_time;
			$data[] = $data_value;	
		}

		return $data;
	}
	/**
	 * Define what data to show on each column of the table
	 */
	public function column_default( $item, $column_name )
	{  
		return $item[ $column_name ];      
	}
	
	/**
	 * Prepare table column for sorting capability
	 */
	function get_sortable_columns() {
		$sortable_columns = array(
			'fid'  => array('fid',false),
			'name' => array('name',false),
			'email'   => array('email',false),
			'time'   => array('time',false)
		);
		return $sortable_columns;
	}
	
	/**
	 * Add sorting capability to table column
	 */	
	function usort_reorder( $a, $b ) {
		// If no sort, default to form_id
		$orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'fid';
		// If no order, default to asc
		$order = ( ! empty($_GET['order'] ) ) ? $_GET['order'] : 'asc';
		// Determine sort order
		$result = strcmp( $a[$orderby], $b[$orderby] );
		// Send final sort direction to usort
		return ( $order === 'asc' ) ? $result : -$result;
	}
}

