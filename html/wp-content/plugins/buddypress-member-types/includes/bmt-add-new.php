<?php

/**
 * Helper class for Edit Member Type screen
 * 
 */
class BP_Member_Type_Add_New_Screen_Helper {

	private static $instance = null;
	private $post_type = '';

	private function __construct() {

		$this->post_type = bmt_get_post_type();

		$this->init();
	}

	/**
	 * 
	 * @return BP_Member_Type_Generator_Admin_Edit_Screen_Helper
	 */
	public static function get_instance() {

		if ( is_null( self::$instance ) ) {

			self::$instance = new self();
		}

		return self::$instance;
	}

	private function init() {
		//save post
		add_action( 'save_post', array( $this, 'save_post' ) );

		add_action( 'add_meta_boxes', array( $this, 'register_metabox' ) );
		add_filter( 'post_updated_messages', array( $this, 'filter_update_messages' ) );
	}

	/**
	 * Register meta boxes
	 */
	public function register_metabox() {

        add_meta_box( 'bp-member-type-label-box', __( 'Labels', 'bp-member-types' ), array( $this, 'bp_member_type_labels_metabox' ), $this->post_type );
        add_meta_box( 'bp-member-type-visibility', __( 'Visibility', 'bp-member-types' ), array( $this, 'bp_member_type_visibility_metabox' ), $this->post_type );
        add_meta_box( 'bp-member-type-wp-role', __( 'WordPress Roles', 'bp-member-types' ), array( $this, 'bp_member_type_wprole_metabox' ), $this->post_type );
	}

	/**
	 * Generate Member Type Label Meta box
	 * 
	 * @param type $post
	 */
	public function bp_member_type_labels_metabox( $post ) {

		$meta = get_post_custom( $post->ID );

		$label_name = isset( $meta[ '_bp_member_type_label_name' ] ) ? $meta[ '_bp_member_type_label_name' ][ 0 ] : '';
		$label_singular_name = isset( $meta[ '_bp_member_type_label_singular_name' ] ) ? $meta[ '_bp_member_type_label_singular_name' ][ 0 ] : '';
		?>
		<table style="width: 100%;">
			<tr valign="top">
				<th scope="row" style="text-align: left; width: 15%;"><label for="bp-member-type[label_name]"><?php _e( 'Plural Label', 'bp-member-types' ); ?></label></th>
				<td>
					<input type="text" class="bmt-label-name" name="bp-member-type[label_name]" placeholder="<?php _e( 'e.g. Students', 'bp-member-types' ); ?>"  value="<?php echo esc_attr( $label_name ); ?>" style="width: 100%;" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" style="text-align: left; width: 15%;"><label for="bp-member-type[label_singular_name]"><?php _e( 'Singular Label', 'bp-member-types' ); ?></label></th>
				<td>
					<input type="text" class="bmt-singular-name" name="bp-member-type[label_singular_name]" placeholder="<?php _e( 'e.g. Student', 'bp-member-types' ); ?>" value="<?php echo esc_attr( $label_singular_name ); ?>" style="width: 100%;" />
				</td>
			</tr>
		</table>
		<?php wp_nonce_field( 'buddyboss-bmt-edit-member-type', '_buddyboss-bmt-nonce' ); ?>
		<?php
	}

	/**
	 * Generate Member Type Directory Meta box
	 * 
	 * @param type $post
	 */
	public function bp_member_type_visibility_metabox( $post ) {

		$meta = get_post_custom( $post->ID );
		$enable_registration = isset( $meta[ '_bp_member_type_enable_registration' ] ) ? $meta[ '_bp_member_type_enable_registration' ][ 0 ] : 0; //disabled by default
                $options_url = admin_url().'edit.php?post_type=bmt-member-type&page=bmt-member-type';
		?>
		<p>
                    <input type='checkbox' name='bp-member-type[enable_registration]' value='1' <?php checked( $enable_registration, 1 ); ?> />
                    <strong><?php _e( 'Display in <a href="'.$options_url.'">Registration Form</a>', 'bp-member-types' ); ?></strong>
		</p>
		<?php
		$enable_directory = isset( $meta[ '_bp_member_type_enable_directory' ] ) ? $meta[ '_bp_member_type_enable_directory' ][ 0 ] : 1; //enabled by default
		?>
		<p>
                    <input type='checkbox' name='bp-member-type[enable_directory]' value='1' <?php checked( $enable_directory, 1 ); ?> />
                    <strong><?php _e( 'Display tab in Members Directory', 'bp-member-types' ); ?></strong>
		</p>
		<?php
		$enable_remove = isset( $meta[ '_bp_member_type_enable_remove' ] ) ? $meta[ '_bp_member_type_enable_remove' ][ 0 ] : 0; //enabled by default
        ?>
        <p>
            <input type='checkbox' name='bp-member-type[enable_remove]' value='1' <?php checked( $enable_remove, 1 ); ?> />
            <strong><?php _e( 'Hide completely from Members Directory', 'bp-member-types' ); ?></strong>
        </p>
		<?php
	}

    /**
     * Generate Member Type WP Role Meta box
     *
     * @param type $post
     */
    public function bp_member_type_wprole_metabox( $post ) {

        global $wp_roles;
        $all_roles = $wp_roles->role_names;
        
        //remove bbPress roles
        unset($all_roles['bbp_keymaster']);
        unset($all_roles['bbp_spectator']);
        unset($all_roles['bbp_blocked']);
        unset($all_roles['bbp_moderator']);
        unset($all_roles['bbp_participant']);
        
        $selected_roles = get_post_meta($post->ID, '_bp_member_type_wp_roles', true);
        $selected_roles = (array) $selected_roles;
        ?>

        <p><?php _e( 'Choose WP roles to be auto-assigned to this member type (includes existing users).', 'bp-member-types' ); ?></p>

        <?php
        if( isset($all_roles) && !empty($all_roles) ){
            foreach($all_roles as $key => $val){
                $role_member_type = bmt_get_member_type_by_wp_role($key);
                $disabled = '';
                $disabled_style = '';
                $disable_message = '';
                if( isset($role_member_type) && !empty($role_member_type) && $post->ID != $role_member_type[0]['ID'] ){
                    $disabled = 'disabled readonly';
                    $disabled_style = 'style="color:#bbb"';
                    $disable_message = ' (Already assigned to "'.$role_member_type[0]['nice_name'].'" member type)';
                }
        ?>
            <p <?php echo $disabled_style;?>>
                <input type='checkbox' name='bp-member-type[wp_roles][]' <?php echo $disabled; ?> value='<?php echo $key;?>' <?php echo in_array($key, $selected_roles) ? 'checked' : ''; ?> /> <?php echo $val.$disable_message; ?>
            </p>

            <?php
            }
        }
    }

	/**
	 * Save all data as post meta
	 * 
	 * @param type $post_id
	 * @return type
	 */
	public function save_post( $post_id ) {

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return;

		$post = get_post( $post_id );

		if ( $post->post_type != $this->post_type )
			return;

		if ( ! isset( $_POST[ '_buddyboss-bmt-nonce' ] ) )
			return; 
		
		//verify nonce
		if ( ! wp_verify_nonce( $_POST[ '_buddyboss-bmt-nonce' ], 'buddyboss-bmt-edit-member-type' ) )
			return;

		//Save data
		$data = isset( $_POST[ 'bp-member-type' ] ) ? $_POST[ 'bp-member-type' ] : array();

		if ( empty( $data ) )
			return;

		$post_title = wp_kses( $_POST[ 'post_title' ], wp_kses_allowed_html( 'strip' ) );
		
		//for label
		$label_name = isset( $data[ 'label_name' ] ) ? wp_kses( $data[ 'label_name' ], wp_kses_allowed_html( 'strip' ) ) : $post_title;
		$singular_name = isset( $data[ 'label_singular_name' ] ) ? wp_kses( $data[ 'label_singular_name' ], wp_kses_allowed_html( 'strip' ) ) : $post_title;

		//Remove space
		$label_name     = trim( $label_name );
		$singular_name  = trim( $singular_name );

        $enable_directory = isset( $data[ 'enable_directory' ] ) ? absint( $data[ 'enable_directory' ] ) : 0; //default inactive
        $enable_remove = isset( $data[ 'enable_remove' ] ) ? absint( $data[ 'enable_remove' ] ) : 0; //default inactive
		$enable_registration = isset( $data[ 'enable_registration' ] ) ? absint( $data[ 'enable_registration' ] ) : 0; //default inactive

        $wp_roles = isset( $data[ 'wp_roles' ] ) ? wp_kses( $data[ 'wp_roles' ], wp_kses_allowed_html( 'strip' ) ) : '';

		update_post_meta( $post_id, '_bp_member_type_label_name', $label_name );
		update_post_meta( $post_id, '_bp_member_type_label_singular_name', $singular_name );

        update_post_meta( $post_id, '_bp_member_type_enable_directory', $enable_directory );
        update_post_meta( $post_id, '_bp_member_type_enable_remove', $enable_remove );
        update_post_meta( $post_id, '_bp_member_type_enable_registration', $enable_registration );

        $old_wp_roles = get_post_meta( $post_id, '_bp_member_type_wp_roles', true );
        update_post_meta( $post_id, '_bp_member_type_wp_roles', $wp_roles );

        //set this member type to users with these roles
        $name = strtolower( get_post_meta( $post_id, '_bp_member_type_label_singular_name', true ) );

        if( isset($name) && !empty($name) ){
            $member_type_name = str_replace(array(' ', ','), array('-', '-'), $name);
            
            if(!empty($old_wp_roles)) {
                bmt_remove_member_type_to_roles($old_wp_roles, $member_type_name);
            }
            if( !empty($wp_roles) ){
                bmt_set_member_type_to_roles($wp_roles, $member_type_name);
            }
        }
	}

	public function filter_update_messages( $messages ) {

		$update_message = $messages[ 'post' ];

		$update_message[ 1 ] = sprintf( __( 'Member type updated.', 'bp-member-types' ) );

		$update_message[ 4 ] = __( 'Member type updated.', 'bp-member-types' );

		$update_message[ 6 ] = sprintf( __( 'Member type published. ', 'bp-member-types' ) );

		$update_message[ 7 ] = __( 'Member type  saved.', 'bp-member-types' );

		$messages[ $this->post_type ] = $update_message;

		return $messages;
	}

}

BP_Member_Type_Add_New_Screen_Helper::get_instance();
