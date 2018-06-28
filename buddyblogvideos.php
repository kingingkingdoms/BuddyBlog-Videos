<?php
/**
 * Plugin Name: BuddyBlog Videos
 * Version: 1.3.2
 * Author: K&L
 * Author URI: https://millionairedigest.com/members/kl/
 * Plugin URI: https://millionairedigest.com/
 * Description: Add a BuddyPress profile nav tab called "Videos" to user's profiles for giving our users the ability to display all of the videos they've created.
 *
 * @package buddyblog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * BuddyBlog main class
 */
class BuddyBlogVideos {

	/**
	 * Singleton instance
	 *
	 * @var BuddyBlog
	 */
	private static $instance = null;

	/**
	 * Absolute path to this plugin directory.
	 *
	 * @var string
	 */
	private $path;

	/**
	 * Absolute url to this plugin directory.
	 *
	 * @var string
	 */
	private $url;

	/**
	 * Plugin basename.
	 *
	 * @var string
	 */
	private $basename;

	/**
	 * Constructor
	 */
	private function __construct() {

		$this->path     = plugin_dir_path( __FILE__ );
		$this->url      = plugin_dir_url( __FILE__ );
		$this->basename = plugin_basename( __FILE__ );

		$this->setup_constants();
		$this->setup();
	}

	/**
	 * Get singleton instance
	 *
	 * @return BuddyBlog
	 */
	public static function get_instance() {

		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Setup hooks.
	 */
	public function setup() {
		register_activation_hook( __FILE__, array( $this, 'install' ) );

		add_action( 'bp_include', array( $this, 'load' ) );
		add_action( 'bp_init', array( $this, 'load_textdomain' ), 2 );
		// add_action( 'bp_enqueue_scripts', array( $this, 'load_comment_js' ) );
	}

	/**
	 * Setup constants.
	 */
	private function setup_constants() {
		if ( ! defined( 'BUDDYBLOGVIDEOS_ARCHIVE_SLUG' ) ) {
			define( 'BUDDYBLOGVIDEOS_ARCHIVE_SLUG', 'my-videos' );
		}
	}
	/**
	 * Load required files
	 */
	public function load() {
		$files = array(
			'buddyblogvideos-loader.php',
		);

		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			$files[] = 'admin/admin.php';
		}

		foreach ( $files as $file ) {
			require_once $this->path . $file;
		}
	}

	/**
	 * Load translation files
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'buddyblogvideos', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Load comment js on singular posts.
	 */
	public function load_comment_js() {
		if ( bp_is_current_component( 'buddyblogvideos' ) && bp_is_current_action( 'my-videos' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}
	}

	/**
	 * Update settings on activation.
	 */
	public function install() {
		$default = array(
			//'root_slug'		=> 'videos',
			'post_type'             => 'video',
			'post_status'           => 'publish',
			'comment_status'        => 'open',
			'show_comment_option'   => 1,
			'custom_field_title'    => '',
			'enable_taxonomy'       => 1,
			'allowed_taxonomies'    => 1,
			'enable_category'       => 1,
			'enable_tags'           => 1,
			'show_posts_on_profile' => 0,
			'limit_no_of_posts'     => 0,
			'max_allowed_posts'     => 20,
			'publish_cap'           => 'read',
			'allow_unpublishing'    => 1, // subscriber //see https://codex.wordpress.org/Roles_and_Capabilities.
			'post_cap'              => 'read',
			'allow_edit'            => 1,
			'allow_delete'          => 1,
			// 'enabled_tags'		=> 1,
			// 'taxonomies'		    => array( 'category' ),
			'allow_upload'          => 0,
			'max_upload_count'      => 2,
		);

		if ( ! get_site_option( 'buddyblogvideos-settings' ) ) {
			add_site_option( 'buddyblogvideos-settings', $default );
		}

	}

	/**
	 * Get the main plugin file.
	 *
	 * @return string
	 */
	public function get_file() {
		return __FILE__;
	}

	/**
	 * Get absolute url to this plugin dir.
	 *
	 * @return string
	 */
	public function get_url() {
		return $this->url;
	}

	/**
	 * Get absolute path to this plugin dir.
	 *
	 * @return string
	 */
	public function get_path() {
		return $this->path;
	}
}

/**
 * Helper function to access the BuddyBlog singleton instance.
 *
 * @return BuddyBlog
 */
function buddyblogvideos() {
	return BuddyBlogVideos::get_instance();
}

// Instantiate.
buddyblogvideos();
