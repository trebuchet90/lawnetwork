<?php

/**
 * Get the post type we are using internally to store member type details
 * 
 * @return string
 */
function bmt_get_post_type() {

	return 'bmt-member-type';
}

/**
 * Gets member types id
 * 
 * @global type $wpdb
 * @param type $type_name
 * @return type int
 */
function bmt_member_type_id( $type_name ) {
    global $wpdb;
    $type_name = strtolower($type_name);
    $type_name = str_replace(array(' ', ','), array('-', '-'), $type_name);

    $type_id = $wpdb->get_col( "SELECT t.term_id FROM {$wpdb->prefix}terms t INNER JOIN {$wpdb->prefix}term_taxonomy tt ON t.term_id = tt.term_id WHERE t.slug = '" . $type_name . "' AND  tt.taxonomy = 'bp_member_type' " );
    return ! isset( $type_id[ 0 ] ) ? '' : $type_id[ 0 ];
}

/**
 * Gets member types term taxonomy id
 *
 * @global type $wpdb
 * @param type $type_name
 * @return type int
 */
function bmt_member_term_taxonomy_id( $type_name ) {
    global $wpdb;
    $type_name = strtolower($type_name);
    $type_name = str_replace(array(' ', ','), array('-', '-'), $type_name);

	$type_id = $wpdb->get_col( "SELECT tt.term_taxonomy_id FROM {$wpdb->prefix}term_taxonomy tt INNER JOIN {$wpdb->prefix}terms t ON t.term_id = tt.term_id WHERE t.slug = '" . $type_name . "' AND  tt.taxonomy = 'bp_member_type' " );
	return ! isset( $type_id[ 0 ] ) ? '' : $type_id[ 0 ];
}

/**
 * Get Member post by member type
 * 
 * @global type $wpdb
 * @param type $member_type
 * @return type array
 */
function bmt_member_post_by_type($member_type) {
    global $wpdb;
    $query = "SELECT DISTINCT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_name = %s AND post_status = %s";

    return $wpdb->get_col($wpdb->prepare($query, bmt_get_post_type(), $member_type, 'publish'));
}

/**
 * Gets member by type id
 *
 * @global type $wpdb
 * @param type $type_id
 * @return type array
 */
function bmt_members_by_type( $type_id ) {
    global $wpdb;

    $member_ids = array();

    if ( empty ( $type_id ) ) {
        return $member_ids;
    }

    $member_ids = $wpdb->get_col( "SELECT u.ID FROM {$wpdb->users} u INNER JOIN {$wpdb->prefix}term_relationships r ON u.ID = r.object_id WHERE u.user_status = 0 AND r.term_taxonomy_id = " . $type_id );

    return $member_ids;
}

function bmt_active_members_by_type( $type_id ) {
    global $wpdb;

    $member_ids = array();

    if ( empty ( $type_id ) ) {
        return $member_ids;
    }

    $get_user_ids = $wpdb->get_col( "SELECT u.ID FROM {$wpdb->users} u INNER JOIN {$wpdb->prefix}term_relationships r ON u.ID = r.object_id WHERE u.user_status = 0 AND r.term_taxonomy_id = " . $type_id );

    if ( isset( $get_user_ids ) && !empty( $get_user_ids ) ) {
        foreach ( $get_user_ids as $single ) {
            $member_activity = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}bp_activity a WHERE a.type = 'last_activity' AND a.user_id = " . $single );
            if ( $member_activity > 0 ) {
                $member_ids[] = $single;
            }
        }
    }

    return $member_ids;
}

/**
 * Get all member types
 * 
 * @global type $wpdb
 * @return type array
 */
function bmt_get_active_member_types() {

	global $wpdb;
	$query = "SELECT DISTINCT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_status = %s";

	return $wpdb->get_col( $wpdb->prepare( $query, bmt_get_post_type(), 'publish' ) );
}

/**
 * Get all plural labels
 * 
 * @return type array
 */
function bmt_plural_labels_array() {
	$member_types = buddypress()->members->types;
	$user_ids = array();
	
	foreach ($member_types as $key=>$member_type) {
		$user_ids[$key] = $member_type->labels['name'];
	}
	
	return $user_ids;
	
}

/**
 * Get all wp users
 * @global type $wpdb
 * @return type array
 */
function bmt_get_all_users(){
    global $wpdb;
    $user_ids = array();
    $user_ids = $wpdb->get_col("SELECT ID FROM {$wpdb->users}");
	
    return $user_ids;
}

/**
 * Get all xprofile field group
 * @global type $wpdb
 * @return type array
 */
function bmt_get_all_field_group() {
	global $wpdb;
    $group_ids = array();
    $group_ids = $wpdb->get_col("SELECT id FROM {$wpdb->base_prefix}bp_xprofile_groups");
	
    return $group_ids;
	
}

function bmt_support_text() {
	if ( file_exists( dirname( __FILE__ ) . '/help-support.php' ) ) {
		require_once( dirname( __FILE__ ) . '/help-support.php' );
	}
}

function bmt_import_func() {
	if ( file_exists( dirname( __FILE__ ) . '/bmt-import.php' ) ) {
		require_once( dirname( __FILE__ ) . '/bmt-import.php' );
	}
}

function bmt_get_users_by_roles($roles) {
    $roles = (array) $roles;
    $users = array();

    foreach ($roles as $role) :
        $users_query = new WP_User_Query( array(
            'fields' => 'ID',
            'role' => $role,
        ) );
        $results = $users_query->get_results();
        if ($results) $users = array_merge($users, $results);
    endforeach;

    return $users;
}

function bmt_set_member_type_to_roles($wp_roles, $member_type){
    $users = bmt_get_users_by_roles($wp_roles);
    if( isset($users) && !empty($users) ){
        foreach($users as $single){
            bmt_set_member_type($single, $member_type);
        }
    }
}

function bmt_remove_member_type_to_roles($wp_roles, $member_type){
    $users = bmt_get_users_by_roles($wp_roles);
    if( isset($users) && !empty($users) ){
        foreach($users as $single){
            bp_remove_member_type($single, $member_type);
        }
    }
}

function bmt_get_removed_member_types(){
    $bmt_ids = array();
    $post_type = bmt_get_post_type();
    $bmt_args = array(
        'post_type' => $post_type,
        'meta_query' => array(
            array(
                'key'     => '_bp_member_type_enable_remove',
                'value'   => 1,
                'compare' => '=',
            ),
        ),
        'nopaging' => true,
    );

    $bmt_query = new WP_Query($bmt_args);
    if ($bmt_query->have_posts()):
        while ($bmt_query->have_posts()):
            $bmt_query->the_post();

            $post_id = get_the_ID();
            $name = strtolower(get_post_meta( $post_id, '_bp_member_type_label_singular_name', true ));
            $name = str_replace(array(' ', ','), array('-', '-'), $name);
            $bmt_ids[] = array(
                'ID' => $post_id,
                'name' => $name,
            );
        endwhile;
    endif;
    wp_reset_query();
    wp_reset_postdata();
    return $bmt_ids;
}

function bmt_get_users_of_removed_member_types(){
    $user_ids = array();
    // get removed member type post ids
    $bmt_ids = bmt_get_removed_member_types();
    // get removed member type names/slugs
    $bmt_names = array();
    if( isset($bmt_ids) && !empty($bmt_ids) ){
        foreach($bmt_ids as $single){
            $bmt_names[] = $single['name'];
        }
    }

    // get member user ids
    if( isset($bmt_names) && !empty($bmt_names) ){
        foreach($bmt_names as $type_name){
            $type_id = bmt_member_type_id($type_name);
            $mb_users = bmt_active_members_by_type($type_id);
            if( isset($mb_users) && !empty($mb_users) ){
                foreach($mb_users as $single){
                    $user_ids[] = $single;
                }
            }
        }
    }

    return $user_ids;
}


function bmt_get_member_type_by_wp_role($role){
    $bmt_ids = array();
    $post_type = bmt_get_post_type();

    $bmt_args = array(
        'post_type' => $post_type,
        'nopaging' => true,
    );

    $bmt_query = new WP_Query($bmt_args);
    if ($bmt_query->have_posts()):
        while ($bmt_query->have_posts()):
            $bmt_query->the_post();

            $post_id = get_the_ID();
            $selected_roles = get_post_meta( $post_id, '_bp_member_type_wp_roles', true );
            $selected_roles = (array) $selected_roles;
            $singular_name = strtolower(get_post_meta( $post_id, '_bp_member_type_label_singular_name', true ));
            $name = str_replace(array(' ', ','), array('-', '-'), $singular_name);
            if( in_array($role, $selected_roles) ){
                $bmt_ids[] = array(
                    'ID' => $post_id,
                    'name' => $name,
                    'nice_name' => $singular_name,
                );
            }
        endwhile;
    endif;
    wp_reset_query();
    wp_reset_postdata();
    return $bmt_ids;
}

/**
 * Set type for a member.
 * custom to set member types on save_post
 *
 * @param int    $user_id     ID of the user.
 * @param string $member_type Member type.
 * @param bool   $append      Optional. True to append this to existing types for user,
 *                            false to replace. Default: false.
 * @return See {@see bp_set_object_terms()}.
 */
function bmt_set_member_type( $user_id, $member_type, $append = false ) {

	$retval = bp_set_object_terms( $user_id, $member_type, 'bp_member_type', $append );

	// Bust the cache if the type has been updated.
	if ( ! is_wp_error( $retval ) ) {
		wp_cache_delete( $user_id, 'bp_member_member_type' );

		/**
		 * Fires just after a user's member type has been changed.
		 *
		 * @since BuddyPress (2.2.0)
		 *
		 * @param int    $user_id     ID of the user whose member type has been updated.
		 * @param string $member_type Member type.
		 * @param bool   $append      Whether the type is being appended to existing types.
		 */
		do_action( 'bmt_set_member_type', $user_id, $member_type, $append );
	}

	return $retval;
}

function bmt_assign_wprole($user_id, $member_type, $append) {
    
    $req_post = bmt_member_post_by_type($member_type);
    
    if ( !isset($req_post) && !empty($req_post) ) {
        return;
    }
    $selected_roles = get_post_meta( $req_post[0], '_bp_member_type_wp_roles', true );
    
    if (is_array($selected_roles) && isset($selected_roles) ) {
        
        $user = new WP_User( $user_id );
        foreach ($selected_roles as $role) {
            $user->set_role($role);
        }
        
    }
    
}

add_action('bp_set_member_type','bmt_assign_wprole',10,3);