<?php

if(!class_exists('WP_List_Table')){
   require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
class Octavius_Top_Links_Table extends WP_List_Table {

   /**
    * Constructor, we override the parent to pass our own arguments
    * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
    */
    function __construct() {
       parent::__construct( array(
      'singular'=> 'wp_list_text_link', //Singular label
      'plural' => 'wp_list_test_links', //plural label, also this well be one of the table css class
      'ajax'   => false //We won't support Ajax for this table
      ) );
    }
    /**
	* Define the columns that are going to be used in the table
	* @return array $columns, the array of columns to use with the table
	*/
	function get_columns() {
		return $columns= array(
			'col_content'=>__('Content'),
			'col_live'=>__('Live'),
			'col_5_minutes'=>__('5 Minutes'),
		);
	}
	/**
	 * Decide which columns to activate the sorting functionality on
	 * @return array $sortable, the array of columns that can be sorted by the user
	 */
	public function get_sortable_columns() {
		return $sortable = array(
			'col_live'=> array('live', false),
			'col_5_minutes'=>array('5minutes', false),
		);
	}
	/**
	 * Prepare the table with different parameters, pagination, columns and table elements
	 */
	function prepare_items($number) {
		$items = array();
		// TODO: get live data and order data by GET[orderby]
		for ($i=0; $i < $number ; $i++) { 
			$items[] = (object) array(
			"col_content" => "Das ist ein Artikel",
			"col_live" => rand(1,5),
			"col_5_minutes" => rand(1,5),
			"link" => "/wp-admin/",
			);
		}
		
		$this->items = $items;
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);
	}
	/**
	 * hide table nav both top and bottom
	 * @param  [type] $which top|bottom
	 */
	function display_tablenav($which){}
	/**
	 * return column value
	 */
	function column_default( $item, $column_name ) {
		$array = (array)$item;
		return $array[$column_name];
	}
	/**
	 * render column col_content
	 */
	function column_col_content($item){
		return "<a href='".$item->link."'>".$item->col_content."</a>";
	}
	/**
	 * Display the rows of records in the table
	 * If I'd like to overwrite normal render precess
	 * otherwise use column_default or column_{name}
	 * @return string, echo the markup of the rows
	 */
	// function display_rows(){}

}
