<div class="wrap">
    <div class="boss-import-area">
        <form id="bmt-import-form" method="post" action="">
            <div class="import-panel-content">
                <h1><?php _e( 'Import Member Types', 'bp-member-types' ); ?></h1>
                <p><?php _e( 'Import your existing member types, that were created <strong>Manually with Code</strong> or from another <strong>Plugin</strong> ( needs to be active )', 'bp-member-types' ); ?></p><br/>

                <input type="submit" value="Run Migration" id="bmt-import-submit" name="bmt-import-submit" class="button-primary">
            </div>
        </form>
    </div>
</div>
<br />

<?php

if (isset($_POST['bmt-import-submit'])) {
    
    $registered_member_types = bp_get_member_types();
    $created_member_types = bmt_get_active_member_types();
    $active_member_types = array();
    
    foreach ( $created_member_types as $created_member_type ) {
        $name = strtolower(get_post_meta($created_member_type, '_bp_member_type_label_singular_name', true));
        $name = str_replace(array(' ', ','), array('-', '-'), $name);
        array_push($active_member_types, $name);
    }

    $registered_member_types = array_diff($registered_member_types, $active_member_types);
    
    if (empty($registered_member_types)) {
        ?>
            <div class="wrap">
                <div class="error notice " id="message"><p><?php _e('Nothing to import', 'bp-member-types'); ?></p></div>
            </div>
        <?php
    }
    
    foreach ( $registered_member_types as $key => $import_types_data ) {
        $sing_name = ucfirst($import_types_data);
        // Create post object
        $my_post = array(
            'post_type'     => 'bmt-member-type',
            'post_title'    => $sing_name,
            'post_status'   => 'publish',
            'post_author'   => get_current_user_id(),
        );
        
        // Insert the post into the database
        $post_id = wp_insert_post($my_post);
        
        if ( $post_id ) {

            update_post_meta( $post_id, '_bp_member_type_label_name', $sing_name );
            update_post_meta( $post_id, '_bp_member_type_label_singular_name', $sing_name );
            
            ?><div class="updated notice " id="message"><p><?php _e('Successfully Imported', 'bp-member-types'); ?></p></div><?php
        }
        
    }
    
}