<?php
/**
 * Shortcodes
 *
 * @package     WordPress
 * @subpackage  BuddyBoss BMT
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Members by type Shortcode
 *
 * Displays buddypress members by type.
 *
 * @param array/string $atts an associative array of attributes, or an empty string if no attributes are given
 * @return string
 */
function bmt_members( $atts ) {

	ob_start();

	echo '<div id="buddypress">';

	echo '<div class="members">';

	if ( ! empty( $atts['type'] ) ) {
		// Set the "current" member type, if one is provided, in member directories.
		buddypress()->current_member_type = $atts['type'];
	}

	add_action( 'bp_ajax_querystring', 'bmp_members_shortcode_filter', 1, 2 );

	//Get a BuddyPress members-loop template part for display in a theme.
	bp_get_template_part( 'members/members-loop' );

	remove_action( 'bp_ajax_querystring', 'bmp_members_shortcode_filter', 1, 2 );

	echo '</div>';

	echo '</div>';

	return ob_get_clean();

}

add_shortcode( 'members', 'bmt_members' );

function bmp_members_shortcode_filter( $query_string, $object ){

	if ( empty( $object ) )
		return '';

	if ( 'members' == $object && bp_current_component() !== 'members' ) {
		$_COOKIE['bp-members-filter'] = 'alphabetical';
		$_COOKIE['bp-members-scope'] = 'all';
	}

	return $query_string;
}