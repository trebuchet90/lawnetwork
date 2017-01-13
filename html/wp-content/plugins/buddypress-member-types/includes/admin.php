<?php
/**
 * @package WordPress
 * @subpackage BuddyPress Member Types
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'BuddyBoss_BMT_Admin' ) ):
/**
 *
 * BuddyPress Member Types Admin
 * ********************
 *
 *
 */
class BuddyBoss_BMT_Admin {
	/* Options/Load
	 * ===================================================================
	 */

	/**
	 * Plugin options
	 *
	 * @var array
	 */
	public	$options = array();
        
	private $network_activated = false,
			$plugin_slug = 'bmt-member-type',
			$menu_hook = 'admin_menu',
			$settings_page = 'edit.php?post_type=bmt-member-type',
			$capability = 'manage_options',
			$form_action = 'options.php',
			$plugin_settings_url;

	/**
	 * Empty constructor function to ensure a single instance
	 */
	public function __construct()
	{
		// ... leave empty, see Singleton below
	}


	/* Singleton
	 * ===================================================================
	 */

	/**
	 * Admin singleton
	 *
	 * @since BuddyPress Member Types (1.0.0)
	 *
	 * @param  array  $options [description]
	 *
	 * @uses BuddyBoss_BMT_Admin::setup() Init admin class
	 *
	 * @return object Admin class
	 */
	public static function instance() {
		static $instance = null;

		if ( null === $instance ) {
			$instance = new BuddyBoss_BMT_Admin;
			$instance->setup();
		}

		return $instance;
	}


	/* Utility functions
	 * ===================================================================
	 */

	/**
	 * Get option
	 *
	 * @since BuddyPress Member Types (1.0.0)
	 *
	 * @param  string $key Option key
	 *
	 * @uses BuddyBoss_BMT_Plugin::option() Get option
	 *
	 * @return mixed      Option value
	 */
	public function option( $key ) {
		$value = buddyboss_bmt()->option( $key );
		return $value;
	}

	/* Actions/Init
	 * ===================================================================
	 */

	/**
	 * Setup admin class
	 *
	 * @since BuddyPress Member Types (1.0.0)
	 *
	 * @uses buddyboss_bmt() Get options from main BuddyBoss_BMT_Plugin class
	 * @uses is_admin() Ensures we're in the admin area
	 * @uses curent_user_can() Checks for permissions
	 * @uses add_action() Add hooks
	 */
	public function setup() {
		if ( ( ! is_admin() && ! is_network_admin() ) || ! current_user_can( 'manage_options' ) )
		{
			return;
		}

		$this->plugin_settings_url = admin_url( 'admin.php?page=' . $this->plugin_slug );

		$this->network_activated = $this->is_network_activated();

		//if the plugin is activated network wide in multisite, we need to override few variables
		if ( $this->network_activated ) {
			// Main settings page - menu hook
			$this->menu_hook = 'network_admin_menu';

			// Main settings page - parent page
			$this->settings_page = 'settings.php';

			// Main settings page - Capability
			$this->capability = 'manage_network_options';

			// Settins page - form's action attribute
			$this->form_action = 'edit.php?action=' . $this->plugin_slug;

			// Plugin settings page url
			$this->plugin_settings_url = network_admin_url('settings.php?page=' . $this->plugin_slug);
		}

		//if the plugin is activated network wide in multisite, we need to process settings form submit ourselves
		if ( $this->network_activated ) {
			add_action('network_admin_edit_' . $this->plugin_slug, array( $this, 'save_network_settings_page' ));
		}

		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( $this->menu_hook, array( $this, 'admin_menu' ) );
	}

	/**
	* Check if the plugin is activated network wide(in multisite).
	*
	* @return boolean
	*/
	private function is_network_activated() {
	   $network_activated = false;
	   if ( is_multisite() ) {
		   if ( !function_exists('is_plugin_active_for_network') )
			   require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

		   if ( is_plugin_active_for_network(basename( constant( 'BUDDYBOSS_BMT_PLUGIN_DIR' ) ).'/buddyboss-bmt.php') ) {
			   $network_activated = true;
		   }
	   }
	   return $network_activated;
	}

	/**
	 * Register admin settings
	 *
	 * @since BuddyPress Member Types (1.0.0)
	 *
	 * @uses register_setting() Register plugin options
	 * @uses add_settings_section() Add settings page option sections
	 * @uses add_settings_field() Add settings page option
	 */
	public function admin_init() {
		
		register_setting( 'buddyboss_bmt_plugin_options', 'buddyboss_bmt_plugin_options', array( $this, 'plugin_options_validate' ) );
		add_settings_section( 'general_section', __( 'Registration Form', 'bp-member-types' ), array( $this, 'section_general' ), __FILE__ );
		add_settings_section( 'shortcodes_section', __( 'Shortcodes', 'bp-member-types' ), array( $this, 'section_general' ), __FILE__ );

		//Registration options
		add_settings_field( 'registration-required-field', __( 'Require on Registration', 'bp-member-types' ), array( $this, 'setting_registration_required_field' ), __FILE__, 'general_section' );
		add_settings_field( 'default-member-type', __( 'Default Member Type', 'bp-member-types' ), array( $this, 'setting_default_member_type' ), __FILE__, 'general_section' );
		add_settings_field( 'hide-member-type-field', __( 'Hide from Registration', 'bp-member-types' ), array( $this, 'setting_hide_member_type_field' ), __FILE__, 'general_section' );
		//Shortcodes options
		add_settings_field( 'member-type-loop', __( 'Display Members Loop', 'bp-member-types' ), array( $this, 'setting_member_type_loop' ), __FILE__, 'shortcodes_section' );

	}

	/**
	 * Add plugin settings page
	 *
	 * @since BuddyPress Member Types (1.0.0)
	 *
	 * @uses add_options_page() Add plugin settings page
	 */
	public function admin_menu() {
                $menu_name = __('Options','bp-member-types');
		add_submenu_page(
                    $this->settings_page, 'BuddyPress Member Types', $menu_name, $this->capability, $this->plugin_slug, array( $this, 'options_page' )
		);
	}

	/**
	 * Add plugin settings page
	 *
	 * @since BuddyPress Member Types (1.0.0)
	 *
	 * @uses BuddyBoss_BMT_Admin::admin_menu() Add settings page option sections
	 */
	public function network_admin_menu()
	{
		return $this->admin_menu();
	}

	/**
	 * Register admin scripts
	 *
	 * @since BuddyPress Member Types (1.0.0)
	 *
	 * @uses wp_enqueue_script() Enqueue admin script
	 * @uses wp_enqueue_style() Enqueue admin style
	 * @uses buddyboss_bmt()->assets_url Get plugin URL
	 */
	public function admin_enqueue_scripts() {
	}

	/* Settings Page + Sections
	 * ===================================================================
	 */

	/**
	 * Render settings page
	 *
	 * @since BuddyPress Member Types (1.0.0)
	 *
	 * @uses do_settings_sections() Render settings sections
	 * @uses settings_fields() Render settings fields
	 * @uses esc_attr_e() Escape and localize text
	 */
	public function options_page()
	{
	?>
            <div class="wrap">
                    <h1><?php _e( 'Options', 'bp-member-types' ); ?></h1>
                    <form action="<?php echo $this->form_action; ?>" method="post">

                            <?php
                                    if ( $this->network_activated && isset($_GET['updated']) ) {
                                            echo "<div class='updated'><p>" . __('Settings updated.', 'bp-member-types') . "</p></div>";
                                    }
                            ?>
                            <?php 
                                settings_fields('buddyboss_bmt_plugin_options');
                                do_settings_sections( __FILE__ );
                            ?>
                            <p class="submit">
                                <input name="bboss_g_s_settings_submit" type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes', 'bp-member-types' ); ?>" />
                            </p>
                    </form>
            </div>

	<?php
	}

	public function save_network_settings_page() {
		if ( ! check_admin_referer( 'buddyboss_bmt_plugin_options-options' ) )
			return;

		if ( ! current_user_can( $this->capability ) )
			die( 'Access denied!' );

		if ( isset( $_POST[ 'bboss_g_s_settings_submit' ] ) ) {
			$submitted = stripslashes_deep( $_POST[ 'buddyboss_bmt_plugin_options' ] );
			$submitted = sanitize_text_field( $submitted );

			update_site_option( 'buddyboss_bmt_plugin_options', $submitted );
		}

		// Where are we redirecting to?
		$base_url = trailingslashit( network_admin_url() ) . 'settings.php';
		$redirect_url = esc_url_raw(add_query_arg( array( 'page' => $this->plugin_slug, 'updated' => 'true' ), $base_url ));

		// Redirect
		wp_redirect( $redirect_url );
		die();
	}

		/**
	 * General settings section
	 *
	 * @since BuddyPress Member Types (1.0.0)
	 */
	public function section_general()
	{

	}

	/**
	 * Style settings section
	 *
	 * @since BuddyPress Member Types (1.0.0)
	 */
	public function section_style()
	{

	}

	/**
	 * Validate plugin option
	 *
	 * @since BuddyPress Member Types (1.0.0)
	 */
	public function plugin_options_validate( $input )
	{
		//$input['enabled'] = sanitize_text_field( $input['enabled'] );

		return $input; // return validated input
	}

	/* Settings Page Options
	 * ===================================================================
	 */

	/**
	 * Setting > BuddyBoss > Member Type > Hide Member Type Field
	 *
	 * @since BuddyPress Member Types (1.0.0)
	 *
	 * @uses BuddyBoss_BMT_Admin::option() Get plugin option
	 */
	public function setting_hide_member_type_field() {
		$value = $this->option('hide_member_type_field');

		$checked = '';

		if ( $value )
		{
			$checked = ' checked="checked" ';
		}

		echo "<input ".$checked." id='enabled' name='buddyboss_bmt_plugin_options[hide_member_type_field]' type='checkbox' value='true' />  ";

		_e( 'Remove member type selection from Registration Form.', 'bp-member-types' );
	}

	/**
	 * Setting > BuddyBoss > Member Type > Make Member Type Field Required
	 *
	 * @since BuddyPress Member Types (1.0.0)
	 *
	 * @uses BuddyBoss_BMT_Admin::option() Get plugin option
	 */
	public function setting_registration_required_field() {
		$value = $this->option('registration_required_field');

		$checked = '';

		if ( $value )
		{
			$checked = ' checked="checked" ';
		}

		echo "<input ".$checked." id='enabled' name='buddyboss_bmt_plugin_options[registration_required_field]' type='checkbox' value='true' />  ";

		_e( 'Require member type selection in Registration Form.', 'bp-member-types' );
	}


	/**
	 * Default member type if user is not selecting any in registration form
	 * and also pre filled value for member type
	 */
	public function setting_default_member_type() {

		$bp_member_type_selected    = $this->option('default_member_type');
		$post_ids                   = bmt_get_active_member_types();

		echo '<select name="buddyboss_bmt_plugin_options[default_member_type]">';
		echo '<option value="">---</option>';
		foreach ($post_ids as $pid) {

			$enable_register = get_post_meta($pid, '_bp_member_type_enable_registration', true);

			if ( $enable_register ) {

				//Member type label
				$bp_member_type_label = sanitize_title( get_post_meta( $pid, '_bp_member_type_label_singular_name', true) );

				?>
				<option value="<?php echo $bp_member_type_label ?>" <?php selected( $bp_member_type_selected, $bp_member_type_label ) ?>><?php echo get_the_title($pid); ?></option>
				<?php
			}
		}

		echo '</select>';
		echo ' <label>' . __( 'Set default member type in Registration Form.', 'bp-member-types' ) . '</label>';
	}


	/**
	 * How to use shortcodes to display member type loop for any type on any page
	 */
	public function setting_member_type_loop() {

		_e( 'To display all members of the same type on a dedicated page, add the following shortcode to any WordPress page:', 'bp-member-types' );
		_e( '<pre>[members type=name]</pre>', 'bp-member-types' );
		_e( 'You will need to change "name" to the name of your member type. Some examples:', 'bp-member-types' );
		_e( '<pre>[members type=teacher]</pre>', 'bp-member-types' );
		_e( '<pre>[members type=student]</pre>', 'bp-member-types' );
	}


}
// End class BuddyBoss_BMT_Admin

endif;

?>
