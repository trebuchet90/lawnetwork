<?php
/**
 * BP Member type List screen helper
 * 
 */
class BP_Member_Type_Index_Helper {
	/**
	 *
	 * @var BP_Member_Generator_Admin_List_Helper
	 */
	private static $instance = null;
		
	private $post_type = '';
	
	private function __construct () {
		
		$this->post_type = bmt_get_post_type();
		
		$this->init();
		
	}
	/**
	 * 
	 * @return BP_Member_Generator_Admin_List_Helper
	 */
	public static function get_instance() {
		
		if( is_null( self::$instance ) )
			self::$instance = new self();
		
		return self::$instance;
		
	}
	
	private function init() {
		//add column
		add_filter( 'manage_'. $this->post_type . '_posts_columns', array( $this, 'add_column' ) );
		add_action( 'manage_'. $this->post_type. '_posts_custom_column', array( $this, 'show_data' ), 10, 2 );
		//sortable columns
		add_filter( 'manage_edit-' . $this->post_type. '_sortable_columns', array( $this, 'add_sortable_columns' ) );
		add_action( 'load-edit.php', array( $this, 'add_request_filter' ) );
		
		//hide quick edit link on the custom post type list screen
		add_filter( 'post_row_actions', array( $this, 'hide_quickedit' ), 10, 2 );
		
	}
	
	/**
	 * Add new columns to the post type list screen
	 * @param type $columns
	 * @return type
	 */
	public function add_column( $columns ) {

		$columns['title'] = __( 'Label', 'bp-member-types' );
		$columns['member_type'] = __( 'Type', 'bp-member-types' );
		$columns['enable_directory'] = __( 'Members Directory', 'bp-member-types' );
		$columns['enable_registration'] = __( 'Registration Form', 'bp-member-types' );
		$columns['total_users'] = __( 'Users', 'bp-member-types' );
		
		unset( $columns['date'] );
		
		return $columns;
	}
	
	public function add_sortable_columns( $columns ) {
		
		$columns['total_users']	= 'total_users';
		$columns['enable_registration']	= 'enable_registration';
		$columns['enable_directory']	= 'enable_directory';
		$columns['member_type']			= 'member_type';
		
		return $columns;
	}
	
	public function show_data( $column, $post_id  ) {
		
		switch( $column ) {
			
			case 'member_type':
				
				echo  get_post_meta( $post_id, '_bp_member_type_label_singular_name', true );
				break;
						
			case 'enable_directory':
				
				if( get_post_meta( $post_id, '_bp_member_type_enable_directory', true ) )
						echo __( 'Display', 'bp-member-types' );
				else
					echo __( 'Hide', 'bp-member-types' );
				
				break;
				
			case 'enable_registration':
				
				if( get_post_meta( $post_id, '_bp_member_type_enable_registration', true ) )
						echo __( 'Display', 'bp-member-types' );
				else
					echo __( 'Hide', 'bp-member-types' );
				
				break;
				
			case 'total_users':
				
				$name = strtolower(get_post_meta( $post_id, '_bp_member_type_label_singular_name', true ));
				$name = str_replace(array(' ', ','), array('-', '-'), $name);
				$type_id = bmt_member_term_taxonomy_id($name);
				
				echo count(bmt_members_by_type($type_id));
				
				break;
			
		}
		
	}
	
	public function add_request_filter() {
		
		add_filter( 'request', array( $this, 'sort_items' ) );
		
	}
	/**
	 * Sort list of member type post types
	 * 
	 * @param type $qv
	 * @return string
	 */
	public function sort_items( $qv ) {
		
		if( ! isset( $qv['post_type'] ) || $qv['post_type'] != $this->post_type )
			return $qv;
		
		if( ! isset( $qv['orderby'] ) )
			return $qv;
		
		switch( $qv['orderby'] ) {
			
			case 'member_type':
				
				$qv['meta_key'] = '_bp_member_type_name';
				$qv['orderby'] = 'meta_value';
				
				break;
			
			case 'enable_registration':
				
				$qv['meta_key'] = '_bp_member_type_enable_registration';
				$qv['orderby'] = 'meta_value_num';
				
				break;
			
			case 'enable_directory':
				
				$qv['meta_key'] = '_bp_member_type_enable_directory';
				$qv['orderby'] = 'meta_value_num';
				
				break;
			
		}
		
		return $qv;
	}
	/**
	 * Hide quick edit link

	 * @param type $actions
	 * @param type $post
	 * @return type
	 */
	public function hide_quickedit( $actions, $post ) {
		
		if ( $this->post_type == $post->post_type )
			unset( $actions['inline hide-if-no-js'] );
		
		return $actions;
	}

}


BP_Member_Type_Index_Helper::get_instance();
