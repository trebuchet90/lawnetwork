<?php

/**
 * Helper class to register the internal member type post type and the actual Member type
 * 
 */
class BP_Member_Type_Actions {

	private static $instance = null;

	private function __construct() {

		//register internal post type used to handle the member type
		add_action( 'bp_init', array( $this, 'register_post_type' ) );

		add_action( 'admin_menu', array( $this, 'add_import_menu' ) );
		add_action( 'admin_menu', array( $this, 'add_help_menu' ),11 );
		
		//register member type
		add_action( 'bp_register_member_types', array( $this, 'register_member_type' ) );

		add_action( 'bp_signup_validate', array( $this, 'bmt_validate_member_type_field' ) );
		add_action( 'bp_core_signup_user', array( $this, 'bmt_member_type_on_registration' ), 10, 5 );
		add_action( 'bp_core_activated_user', array( $this, 'bmt_member_type_on_registration_multisite' ), 10, 3 );
		
		add_filter( 'bp_signup_usermeta', array( $this, 'bmt_alter_usermeta' ) );
		
		// add setting link
		$buddyboss = BuddyBoss_BMT_Plugin::instance();
		$plugin = $buddyboss->basename;
		add_filter("plugin_action_links_$plugin", array($this, 'plugin_settings_link'));
		
		add_action("admin_init", array($this, 'changing_listing_label'));
                
                // remove users of a specific member type from members directory
                add_action( 'bp_ajax_querystring', array($this, 'exclude_users_from_directory_and_searches'), 999, 2 );
                // set member type while update user profile
                add_action( 'profile_update', array($this, 'update_user_member_type_set'), 10, 2 );
                // fix all member count
                add_filter( 'bp_core_get_active_member_count', array($this, 'fixed_all_member_count'), 999 );
	}

    function fixed_all_member_count($count){
        $exclude_user_ids = bmt_get_users_of_removed_member_types();
        if( isset($exclude_user_ids) && !empty($exclude_user_ids) ){
            $count = $count - count($exclude_user_ids);
        }
        return $count;
    }

    function update_user_member_type_set( $user_id, $old_user_data ){
        $user_detail = get_userdata($user_id);
        $user_role = $user_detail->roles[0];
        $get_member_type = bmt_get_member_type_by_wp_role($user_role);
        
        if( isset($get_member_type[0]['name']) && !empty($get_member_type[0]['name']) ){
            bp_set_member_type($user_id, $get_member_type[0]['name']);
        }
    }

    public static function get_instance() {

		if ( is_null( self::$instance ) )
			self::$instance = new self();

		return self::$instance;
	}

	/**
	 * Register internal post type
	 * 
	 * @return type
	 */
	public function register_post_type() {

		//only register on the main bp site
		if ( is_multisite() && ! bp_is_root_blog() )
			return;

		register_post_type( bmt_get_post_type(), array(
			'label' 	=> __( 'BuddyPress Member Types', 'bp-member-types' ),
			'labels' 	=> array(
				'name'                          => __( 'BuddyPress Member Types', 'bp-member-types' ),
				'singular_name' 		=> __( 'Member Type', 'bp-member-types' ),
				'menu_name'       		=> __( 'Member Types', 'bp-member-types' ),
				'all_items'       		=> __( 'All Member Types', 'bp-member-types' ),
				'add_new_item'  		=> __( 'New Member Type', 'bp-member-types' ),
				'new_item'                      => __( 'New Member Type', 'bp-member-types' ),
				'edit_item' 			=> __( 'Edit Member Type', 'bp-member-types' ),
				'search_items' 			=> __( 'Search Member Types', 'bp-member-types' ),
				'not_found_in_trash'            => __( 'No Member Types found in trash', 'bp-member-types' ),
				'not_found' 			=> __( 'No Member Types found', 'bp-member-types' )
			),
			'public' 	=> false, //this is a private post type, not accesible from front end
			'show_ui' 	=> true,
			'show_in_menu' 	=> true,
			'menu_position'	=> 24,
			'menu_icon' 	=> 'dashicons-groups',
			'supports'	=> array( 'title' )
		) );
	}
	/**
	 * Calling Support menu
	 */
	public function add_help_menu() {
		add_submenu_page('edit.php?post_type=bmt-member-type', __('Support','bp-member-types'), __('Support','bp-member-types'), 'manage_options', 'support', 'bmt_support_text' );
	}
        
	/**
	 * Calling Import menu
	 */
	public function add_import_menu() {
		add_submenu_page('edit.php?post_type=bmt-member-type', __('Import','bp-member-types'), __('Import','bp-member-types'), 'manage_options', 'import', 'bmt_import_func' );
	}

	/**
	 * Register all active member types
	 * 
	 */
	public function register_member_type() {

		//$this->register_post_type();
		$is_root_blog = bp_is_root_blog();
		//if we are not on the main bp site, switch to it before registering member type

		if ( ! $is_root_blog ) {
			switch_to_blog( bp_get_root_blog_id() );
		}
		//get all posts in memeber type post type

		$post_ids = bmt_get_active_member_types();
		
		//update meta cache to avoid multiple db calls 
		update_meta_cache( 'post', $post_ids );
		//build to register the memebr type
		$member_types = array();

		foreach ( $post_ids as $post_id ) {
			
			$name = strtolower(get_post_meta( $post_id, '_bp_member_type_label_singular_name', true ));
                        $name = str_replace(array(' ', ','), array('-', '-'), $name);

			$enable_directory = get_post_meta( $post_id, '_bp_member_type_enable_directory', true );

			$has_dir = false;

			if ( $enable_directory ) {
				$has_dir = true;
			}

			$member_types[ $name ] = array(
				'labels' => array(
					'name' => get_post_meta( $post_id, '_bp_member_type_label_name', true ),
					'singular_name' => get_post_meta( $post_id, '_bp_member_type_label_singular_name', true ),
				),
				'has_directory' => $has_dir
			);
		}

		foreach ( $member_types as $member_type => $args ) {
			bp_register_member_type( $member_type, $args );
		}

		if ( ! $is_root_blog ) {
			restore_current_blog();
		}
	}

	/**
	 * Validation of a member type field.
	 */
	public function bmt_validate_member_type_field() {
		global $bp;

		$is_registration_required_field = buddyboss_bmt()->option('registration_required_field');

		if ( ! empty( $is_registration_required_field )
		     && isset( $_REQUEST['bmt_member_type'] )
		     &&  empty( $_REQUEST['bmt_member_type'] )
		) {
			$bp->signup->errors['field_bmt_member_type'] = __( 'Please make sure you have selected member type', 'bp-member-types' );
		}
	}
	
	/**
	 * Update Member type on single site
	 * 
	 * @param type $user_id
	 * @param type $user_login
	 * @param type $user_password
	 * @param type $user_email
	 * @param type $usermeta
	 */
	public function bmt_member_type_on_registration( $user_id, $user_login, $user_password, $user_email, $usermeta ) {

		//Set default member type if user has not selected any
		$bmt_member_type = is_array( $usermeta ) && ! empty ( $usermeta['bmt_member_type'] ) ? $usermeta['bmt_member_type'] : buddyboss_bmt()->option('default_member_type');

		if ( ! empty( $bmt_member_type ) ) {
			
			if ( !empty($user_id ) ) { //for multisite $user_id is empty
				bp_set_member_type($user_id, $bmt_member_type );
			}
		}
	}
	
	/**
	 * Update member type on multisite
	 * 
	 * @param type $user_id
	 * @param type $key
	 * @param type $user
	 */
	public function bmt_member_type_on_registration_multisite( $user_id, $key, $user ) {

		//Set default member type if user has not selected any
		$bmt_member_type = is_array( $user ) && ! empty ( $user['meta']['bmt_member_type'] ) ? $user['meta']['bmt_member_type'] : buddyboss_bmt()->option('default_member_type');

		if ( ! empty( $bmt_member_type ) ) {
			
			if ( ! empty( $user_id ) ) {
				bp_set_member_type( $user_id, $bmt_member_type );
			}
		}
	}
	
	/**
	 * Add member type in $usermeta array
	 * 
	 * @param type $usermeta
	 * @return type array
	 */
	public function bmt_alter_usermeta($usermeta) {

		//Set default member type if user has not selected any member type
		$bmt_member_type = ! empty ( $_POST['bmt_member_type'] ) ? $_POST['bmt_member_type'] : buddyboss_bmt()->option('default_member_type');

		if ( !empty( $bmt_member_type ) ) {
			$usermeta['bmt_member_type'] = $bmt_member_type;
		}
		
		return apply_filters( 'bmt_alter_usermeta', $usermeta );
		
	}
	
	/**
	 * Add settings link on plugin page
	 * @param type $links
	 */
	function plugin_settings_link($links) {
            $links[] = '<a href="'.admin_url("edit.php?post_type=bmt-member-type").'">'.__("Manage","bp-member-types").'</a>';
            return $links;
	}
	
	function changing_listing_label() {
		global $pagenow, $typenow;
		
		if ( $typenow == 'bmt-member-type' && ( $pagenow == 'edit.php' || $pagenow == 'post-new.php' ) ) {
			wp_enqueue_script('bmt-admin-screen',buddyboss_bmt()->assets_url."/js/bmt-admin-screen.min.js",array('jquery'), BUDDYBOSS_BMT_PLUGIN_VERSION );
		}
	}

    public function exclude_users_from_directory_and_searches( $qs=false, $object=false ){

        $exclude_user_ids = bmt_get_users_of_removed_member_types();
        //print_r($exclude_user_ids);

        if( $object != 'members' )
            return $qs;

        $args = wp_parse_args( $qs );

        if( ! empty( $args['user_id'] ) )
            return $qs;

        if( ! empty( $args['exclude'] ) )
            $args['exclude'] = $args['exclude'] . ',' . implode( ',', $exclude_user_ids );
        else
            $args['exclude'] = implode( ',', $exclude_user_ids );

        $qs = build_query( $args );

        return $qs;

    }

}

BP_Member_Type_Actions::get_instance();