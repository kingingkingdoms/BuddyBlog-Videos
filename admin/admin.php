<?php
/**
 * Admin settings helper class.
 *
 * @package buddyblog
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once dirname( __FILE__ ) . '/options-buddy/ob-loader.php';

/**
 * Class BuddyBlog_Admin_Settings_Helper
 */
class BuddyBlogVideos_Admin_Settings_Helper {
	/**
	 * Holds optionsbuddy settings page object
	 *
	 * @var OptionsBuddy_Settings_Page
	 */
	private $page;

	/**
	 * BuddyBlog_Admin constructor.
	 */
	public function __construct() {

		// Create a options page.
		$this->page = new OptionsBuddy_Settings_Page( 'buddyblogvideos-settings' );
		$this->page->set_bp_mode(); // Make it to use bp_get_option/bp_update_option.

		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_footer', array( $this, 'admin_css' ) );
	}

	/**
	 * Set settings
	 */
	public function admin_init() {

		$page = $this->page;
		// Add_section
		// You can pass section_id, section_title, section_description, the section id must be unique for this page, section descriptiopn is optional.
		$page->add_section( 'basic_section', __( 'Settings', 'buddyblogvideos' ), __( 'Settings for BuddyBlogVideos.', 'buddyblogvideos' ) );

		$post_types = get_post_types( array(
			'public' => true,
		) ); // Public post types.

		$post_type_options = array();

		foreach ( $post_types as $post_type ) {
			$post_type_object                = get_post_type_object( $post_type );
			$post_type_options[ $post_type ] = $post_type_object->labels->name;
		}

		$post_statuses = array(
			'publish' => __( 'Published', 'buddyblogvideos' ),
			'draft'   => __( 'Draft', 'buddyblogvideos' ),
		);

		$comment_statuses = array(
			'open'  => __( 'Open', 'buddyblogvideos' ),
			'close' => __( 'Closed', 'buddyblogvideos' ),
		);

		$default_post_type = buddyblogvideos_get_posttype() ? buddyblogvideos_get_posttype() : 'post';
		$taxonomies        = get_object_taxonomies( $default_post_type );

		if ( isset( $taxonomies['post_format'] ) ) {
			unset( $taxonomies['post_format'] );
		}

		$tax = array();

		foreach ( $taxonomies as $taxonomy ) {
			$tax_object       = get_taxonomy( $taxonomy );
			$tax[ $taxonomy ] = $tax_object->labels->name;
		}

		// Add fields.
		$page->get_section( 'basic_section' )->add_fields( array( // Remember, we registered basic section earlier.
			array(
				'name'    => 'post_type',
				'label'   => __( 'Blog Post Type', 'buddyblogvideos' ),
				'desc'    => __( 'Set the post type for user blog.', 'buddyblogvideos' ),
				'type'    => 'select',
				'default' => $default_post_type,
				'options' => $post_type_options,
			),
			array(
				'name'    => 'post_status',
				'label'   => __( 'Default post status', 'buddyblogvideos' ),
				'desc'    => __( 'What should be the post status when user submits the form?', 'buddyblogvideos' ),
				'type'    => 'select',
				'default' => 'publish',
				'options' => $post_statuses,
			),
			array(
				'name'    => 'allow_upload',
				'label'   => __( 'Allow Upload?', 'buddyblogvideos' ),
				'desc'    => __( 'Want to allow user to upload?', 'buddyblogvideos' ),
				'type'    => 'select',
				'default' => 1,
				'options' => array(
					1 => __( 'Yes', 'buddyblogvideos' ),
					0 => __( 'No', 'buddyblogvideos' ),
				),
			),
			array(
				'name'    => 'comment_status',
				'label'   => __( 'Comment status?', 'buddyblogvideos' ),
				'desc'    => __( 'Do you want to allow commenting on user posts?', 'buddyblogvideos' ),
				'type'    => 'select',
				'default' => 'open',
				'options' => $comment_statuses,
			),
			array(
				'name'    => 'show_comment_option',
				'label'   => __( 'Allow post author to enable/disable comment?', 'buddyblogvideos' ),
				'desc'    => __( 'If you enable, A user will be able to change the comment status for his/her post.', 'buddyblogvideos' ),
				'type'    => 'radio',
				'default' => 1,
				'options' => array(
					1 => __( 'Yes', 'buddyblogvideos' ),
					0 => __( 'No', 'buddyblogvideos' ),
				),
			),
			array(
				'name'    => 'post_update_redirect',
				'label'   => __( 'Where to redirect after creating/updating post?', 'buddyblogvideos' ),
				'desc'    => __( 'If you select archive, user will be redirected to the post list, if single, user will be redirected to single post page if the post is published.', 'buddyblogvideos' ),
				'type'    => 'select',
				'default' => 'archive',
				'options' => array(
					'archive' => __( 'Archive page', 'buddyblogvideos' ),
					'single'  => __( 'Single post page', 'buddyblogvideos' ),
				),
			),
			array(
				'name'    => 'enable_taxonomy',
				'label'   => __( 'Enable Taxonomy?', 'buddyblogvideos' ),
				'desc'    => __( 'If you enable, users will be able to select terms from the selected taxonomies.', 'buddyblogvideos' ),
				'type'    => 'radio',
				'default' => 1,
				'options' => array(
					1 => __( 'Yes', 'buddyblogvideos' ),
					0 => __( 'No', 'buddyblogvideos' ),
				),
			),
			array(
				'name'    => 'allowed_taxonomies',
				'label'   => __( 'Select allowed taxonomies', 'buddyblogvideos' ),
				'desc'    => __( 'Please check the taxonomies you want users to be able to attach to their post.', 'buddyblogvideos' ),
				'type'    => 'multicheck',
				'default' => 'category',
				'options' => $tax,
			),
			array(
				'name'    => 'show_posts_on_profile',
				'label'   => __( 'Show single posts on user profile?', 'buddyblogvideos' ),
				'desc'    => __( 'If you enable it, the permalink to single post will be something like http://yoursite.com/members/username/buddyblogvideos/postname.', 'buddyblogvideos' ),
				'type'    => 'radio',
				'default' => 0,
				'options' => array(
					1 => __( 'Yes', 'buddyblogvideos' ),
					0 => __( 'No', 'buddyblogvideos' ),
				),
			),
			array(
				'name'    => 'limit_no_of_posts',
				'label'   => __( 'Limit number of posts a user can create?', 'buddyblogvideos' ),
				'desc'    => __( 'If you enable it, You can control the allowed number of posts from the next option.', 'buddyblogvideos' ),
				'type'    => 'radio',
				'default' => 0,
				'options' => array(
					1 => __( 'Yes', 'buddyblogvideos' ),
					0 => __( 'No', 'buddyblogvideos' ),
				),
			),
			array(
				'name'    => 'max_allowed_posts',
				'label'   => __( 'How many posts a user can create?', 'buddyblogvideos' ),
				'desc'    => __( 'Only applies if you have enabled the limit on posts from above option.', 'buddyblogvideos' ),
				'type'    => 'text',
				'default' => 10,
			),
			array(
				'name'    => 'publish_cap',
				'label'   => __( 'Which capability is required for pusblishing?', 'buddyblogvideos' ),
				'desc'    => __( 'Please check for https://codex.wordpress.org/Roles_and_Capabilities allowed capabilities.', 'buddyblogvideos' ),
				'type'    => 'text',
				'default' => 'read',
			),
			array(
				'name'    => 'allow_unpublishing',
				'label'   => __( 'Allow users to unpublish their own post?', 'buddyblogvideos' ),
				'desc'    => '',
				'type'    => 'radio',
				'default' => 0,
				'options' => array(
					1 => __( 'Yes', 'buddyblogvideos' ),
					0 => __( 'No', 'buddyblogvideos' ),
				),
			),
			array(
				'name'    => 'post_cap',
				'label'   => __( 'Which capability is required for creating post?', 'buddyblogvideos' ),
				'desc'    => __( 'Please check for https://codex.wordpress.org/Roles_and_Capabilities allowed capabilities.', 'buddyblogvideos' ),
				'type'    => 'text',
				'default' => 'read',
			),
			array(
				'name'    => 'allow_edit',
				'label'   => __( 'Allow user to edit their post?', 'buddyblogvideos' ),
				'desc'    => __( 'if you disable it, user will not be able to edit their own post.', 'buddyblogvideos' ),
				'type'    => 'radio',
				'default' => 1,
				'options' => array(
					1 => __( 'Yes', 'buddyblogvideos' ),
					0 => __( 'No', 'buddyblogvideos' ),
				),
			),
			array(
				'name'    => 'dashboard_edit_cap',
				'label'   => __( 'Which capability can edit post in backend(WordPress Dashboard)?', 'buddyblogvideos' ),
				'desc'    => __( 'User with these capabilities will nto be redirected to front end editor for editing post., user will not be able to edit their own post.', 'buddyblogvideos' ),
				'type'    => 'text',
				'default' => 'publish_posts',
			),
			array(
				'name'    => 'allow_delete',
				'label'   => __( 'Allow user to delete their post?', 'buddyblogvideos' ),
				'desc'    => __( 'if you disable it, user will not be able to delete their own post.', 'buddyblogvideos' ),
				'type'    => 'radio',
				'default' => 1,
				'options' => array(
					1 => __( 'Yes', 'buddyblogvideos' ),
					0 => __( 'No', 'buddyblogvideos' ),
				),
			),
		) );

		do_action( 'buddyblogvideos_admin_register_settings', $page );

		$page->init();
	}

	/**
	 * Add option page
	 */
	public function admin_menu() {
		add_options_page( __( 'BuddyBlogVideos Settings', 'buddyblogvideos' ), __( 'BuddyBlog Articles', 'buddyblogvideos' ), 'manage_options', 'buddyblogvideos', array(
			$this->page,
			'render',
		) );
	}

	/**
	 * Returns all the settings fields
	 *
	 */
	public function admin_css() {

		if ( ! isset( $_GET['page'] ) || $_GET['page'] != 'buddyblogvideos' ) {
			return;
		}

		?>
        <style type="text/css">.wrap .form-table {margin: 10px;}</style>
		<?php
	}
}

new BuddyBlogVideos_Admin_Settings_Helper();
