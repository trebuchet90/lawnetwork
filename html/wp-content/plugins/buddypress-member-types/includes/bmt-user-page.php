<?php

/**
 * Admin/Network admin Users list helper
 * 
 */
class BP_Member_Type_User_Page {

	/**
	 *
	 * @var BP_Member_Type_Generator_Admin_User_List_Helper
	 */
	private static $instance = null;
	private $post_type = '';
	private $message = '';

	private function __construct() {

		$this->post_type = bmt_get_post_type();

		$this->init();
	}

	/**
	 * 
	 * @return BP_Member_Type_Admin_List_Helper
	 */
	public static function get_instance() {

		if ( is_null( self::$instance ) )
			self::$instance = new self();

		return self::$instance;
	}

	private function init() {

		// WordPress user screen

		add_filter( 'manage_users_columns', array( $this, 'user_role_column' ) );
		add_filter( 'manage_users_custom_column', array( $this, 'user_role_row'), 999, 3 );

		// Process bulk role change
		add_action( 'restrict_manage_users', array( $this, 'user_member_type_bulk_dropdown' ) );
		add_action( 'load-users.php', array( $this, 'user_member_type_bulk_change' ) );

		//show notices
		add_action( 'admin_notices', array( $this, 'notices' ) );
		add_action( 'network_admin_notices', array( $this, 'notices' ) );

		//Update status admin notice
		if( ! empty( $_GET['bmt-message'] ) ) {
			$this->message = esc_html( urldecode ( $_GET['bmt-message'] ) );
		}

	}
	
	/**
	 * Add Member Type column to the WordPress Users table
	 *
	 * @param array $columns Users table columns
	 * @return array $columns
	 */
	public static function user_role_column( $columns = array() ) {
		$columns['member_type'] = __( 'Member Type',  'bp-member-types' );
		return $columns;
	}
	
	/**
	 * Return member type for display in the WordPress Users list table
	 *
	 * @param string $empty
	 * @param string $column_name
	 * @param int $user_id
	 *
	 * @return string Displayable bbPress user role
	 */
	public static function user_role_row( $empty = '', $column_name, $user_id ){
		// Only looking for member type user role column
        switch ( $column_name ) {
            case 'member_type':
                // Get the member type
                $member_type = bp_get_member_type( $user_id );
                if( isset($member_type) && !empty($member_type) ){
                    return ucfirst($member_type);
                }
                break;

            default:
                break;
        }
        return $empty;
	}

	/**
	 * Add bulk member type dropdown to the WordPress users table
	 *
	 */
	public function user_member_type_bulk_dropdown() {
		global $wpdb;

		//only admin/super admin
		if ( ! current_user_can( 'edit_users' ) ) {
			return;
		}

		$query = "SELECT DISTINCT post_name, post_title FROM {$wpdb->posts} WHERE post_type = %s AND post_status = %s";
		$active_member_type = $wpdb->get_results( $wpdb->prepare( $query, bmt_get_post_type(), 'publish' ) );
		 ?>
		<label class="screen-reader-text" for="bmt-new-member-type"><?php esc_html_e( 'Change member type to&hellip;', 'bp-member-types' ) ?></label>
		<select name="bmt-new-member-type[]" id="bmt-new-member-type" style="display:inline-block; float:none;">
		<option value=''><?php esc_html_e( 'Change member type to&hellip;', 'bp-member-types' ) ?></option>
		<?php foreach ( $active_member_type as $member_type ) : ?>
			<option value="<?php echo $member_type->post_name; ?>"><?php echo $member_type->post_title; ?></option>
		<?php endforeach; ?>
		</select>
		<?php submit_button( __( 'Change', 'bp-member-types' ), 'secondary', 'bmt-change-member-type', false );

		wp_nonce_field( 'bmt-bulk-users', 'bmt-bulk-users-nonce' );
	}


	/**
	 * Process bulk dropdown form submission from the WordPress Users
	 * Table
	 *
	 * @return bool Always false
	 */
	public function user_member_type_bulk_change() {

		$input_name = 'users';

		if ( is_multisite() && is_network_admin() ) {
			$input_name = 'allusers';
		}

		$users = isset( $_REQUEST[ $input_name ] ) ? $_REQUEST[ $input_name ] : array();

		// Bail if no users specified
		if( empty( $users ) ) {
			return;
		}

		// Bail if this isn't a change member type action
		if ( empty( $_REQUEST['bmt-new-member-type'] ) || empty( $_REQUEST['bmt-change-member-type'] ) ) {
			return;
		}

		/**
		 * The problem was that the restrict_manage_users action gets called twice: once ABOVE the Users table, and once BELOW it.
		 * This means that TWO select dropdowns get created with the same name. When the Filter button is clicked,
		 * whatever value is in the second select element (i.e. the one BELOW the table) overrides the value in the first one, i.e. the one ABOVE the table.
		 *
		 * We can take advantage of the ability to turn HTML inputs into arrays of values,
		 * and then filter the array to get rid of any undefined values.
		 *
		 * See more at http://wordpress.stackexchange.com/questions/213576/how-to-filter-users-on-admin-users-page-by-custom-meta-field
		 */
		$new_member_type = $_REQUEST['bmt-new-member-type'];
		$new_member_type = ! empty( $new_member_type[ 0 ] ) ? $new_member_type[ 0 ] : $new_member_type[ 1 ];

		// Bail if nonce check fails
		check_admin_referer( 'bmt-bulk-users', 'bmt-bulk-users-nonce' );

		$updated = 0;

		// Run through user ids
		foreach ( (array) $_REQUEST['users'] as $user_id ) {
			$user_id = (int) $user_id;

			bp_set_member_type( $user_id, $new_member_type );
		}

		$updated = 1;

		$this->message = sprintf( __( 'Changed member type for %d user(s) to %s. ', 'bp-member-types'), count( $users ), $new_member_type );

		if( is_network_admin() )
			$url = network_admin_url( 'users.php' );
		else
			$url = admin_url( 'users.php' );

		$redirect = add_query_arg( array( 'updated' => $updated, 'bmt-message' => urlencode( $this->message ) ), $url );

		wp_safe_redirect( $redirect );

		exit();
	}

	/**
	 * Render notices
	 *
	 * @return type
	 */
	public function notices() {

		if( ! $this->message )
			return ;
		?>

		<div id="message" class="updated notice is-dismissible"><p><?php echo $this->message;?></p></div>

		<?php
	}




}

BP_Member_Type_User_Page::get_instance();
