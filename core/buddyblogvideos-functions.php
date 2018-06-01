<?php
/**
 * BuddyBlog functions.
 *
 * @package buddyblog
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 0 );
}


/**
 * Are we inside the buddyblog component on users profile
 *
 * @return bool
 */
function bp_is_buddyblogvideos_component() {
	return bp_is_current_component( 'buddyblogvideos' );
}

/**
 * Get allowed post type for BuddyBlog
 *
 * @return string
 */
function buddyblogvideos_get_posttype() {
	$post_type = buddyblogvideos_get_option( 'post_type' );

	if ( ! $post_type ) {
		$post_type = 'post';
	}

	return apply_filters( 'buddyblogvideos_get_post_type', $post_type );
}

/**
 * Get allowed taxonomies
 *
 * @return array
 */
function buddyblogvideos_get_taxonomies() {

	return apply_filters( 'buddyblogvideos_get_taxonomies', buddyblogvideos_get_option( 'allowed_taxonomies' ) );
}

/**
 * Get total no. of Posts  posted by a user
 *
 * @param int $user_id user id.
 *
 * @return int
 *
 * @todo : may need revisist
 */
function buddyblogvideos_get_total_posted( $user_id = 0 ) {

	if ( ! $user_id ) {
		$user_id = bp_displayed_user_id();
	}

	// Needs revisit.
	global $wpdb;

	$count = $wpdb->get_var( $wpdb->prepare( "SELECT count('*') FROM {$wpdb->posts} WHERE post_author=%d AND post_type=%s AND (post_status='publish'||post_status='draft')", $user_id, buddyblogvideos_get_posttype() ) );

	return intval( $count );
}

/**
 * Get total no. of published post for the user
 *
 * @param int $user_id user id.
 *
 * @return int
 */
function buddyblogvideos_get_total_published_posts( $user_id = 0 ) {

	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}
	// Needs revisit.
	global $wpdb;

	$count = $wpdb->get_var( $wpdb->prepare( "SELECT count('*') FROM {$wpdb->posts} WHERE  post_author=%d AND post_type=%s AND post_status='publish'", $user_id, buddyblogvideos_get_posttype() ) );

	return intval( $count );
}

/**
 * Get allowed no. of posts
 * Use this filter
 *
 * @param int $user_id user id.
 *
 * @return int
 */
function buddyblogvideos_get_allowed_no_of_posts( $user_id = 0 ) {

	if ( ! $user_id ) {
		$user_id = bp_displayed_user_id();
	}

	// filter on this hook to change the no. of posts allowed.
	// by default no. posts allowed.
	return apply_filters( 'buddyblogvideos_allowed_posts_count', buddyblogvideos_get_option( 'max_allowed_posts' ), $user_id );
}

/**
 * Get remaining no. of posts to be activated
 *
 * @param int $user_id user id.
 *
 * @return int
 */
function buddyblogvideos_get_remaining_posts( $user_id = 0 ) {

	$total_allowed = buddyblogvideos_get_allowed_no_of_posts( $user_id );

	return intval( $total_allowed - buddyblogvideos_get_total_published_posts( $user_id ) );
}

/**
 * Are we viewing the single post listing on user profile?
 *
 * @return bool
 */
function buddyblogvideos_is_single_post() {

	$action  = bp_current_action();
	$post_id = 0;
	// make sure
	// to check the strategy.
	if ( buddyblogvideos_use_slug_in_permalink() ) {
		$slug    = bp_action_variable( 0 );
		$post_id = buddyblogvideos_get_post_id_from_slug( $slug );
	} else {
		$post_id = intval( bp_action_variable( 0 ) );
	}

	if ( bp_is_buddyblogvideos_component() && $action == BUDDYBLOGVIDEOS_ARCHIVE_SLUG && ! empty( $post_id ) ) {
		return true;
	}

	return false;
}

/**
 * Is it posts archive for user?
 *
 * @return bool
 */
function buddyblogvideos_is_posts_archive() {

	$action  = bp_current_action();
	$post_id = bp_action_variable( 0 );

	if ( bp_is_buddyblogvideos_component() && $action == BUDDYBLOGVIDEOS_ARCHIVE_SLUG && empty( $post_id ) ) {
		return true;
	}

	return false;
}

/**
 * Is it Post edit page ?
 *
 * @return bool
 */
function buddyblogvideos_is_edit_post() {

	$action  = bp_current_action();
	$post_id = bp_action_variable( 0 );

	if ( bp_is_buddyblogvideos_component() && $action == 'edit' && ! empty( $post_id ) ) {
		return true;
	}

	return false;
}

/**
 * Is it new Post page
 *
 * @return bool
 */
function buddyblogvideos_is_new_post() {

	$action  = bp_current_action();
	$post_id = bp_action_variable( 0 );

	if ( bp_is_buddyblogvideos_component() && $action == 'edit' && empty( $post_id ) ) {
		return true;
	}

	return false;
}


/**
 * Has user posted
 *
 * @return bool
 */
function buddyblogvideos_user_has_posted() {

	$total_posts = buddyblogvideos_get_total_posted();

	return (bool) $total_posts;
}

/**
 * Get the url of the BuddyBlog component for the given user
 *
 * @param int|bool $user_id id of user.
 *
 * @return string
 */
function buddyblogvideos_get_home_url( $user_id = false ) {

	if ( ! $user_id ) {
		$user_id = bp_displayed_user_id();
	}

	$url = bp_core_get_user_domain( $user_id ) . buddypress()->buddyblogvideos->slug . '/';

	return $url;
}

/**
 * Get the url for publishing/unpublishing the post
 *
 * @param int $post_id post id.
 *
 * @return string
 */
function buddyblogvideos_get_post_publish_unpublish_url( $post_id = 0 ) {

	if ( ! $post_id ) {
		return '';
	}

	$post = get_post( $post_id );
	$url  = '';

	if ( buddyblogvideos_user_can_publish( get_current_user_id(), $post_id ) ) {
		// check if post is published.
		$url = buddyblogvideos_get_home_url( $post->post_author );

		if ( buddyblogvideos_is_post_published( $post_id ) ) {
			$url = $url . 'unpublish/' . $post_id . '/';
		} else {
			$url = $url . 'publish/' . $post_id . '/';
		}
	}

	return $url;

}

/**
 * Get a link that allows to publish/unpublish the post
 *
 * @param int    $post_id post id.
 * @param string $label_ac label activate.
 * @param string $label_de label deactivate.
 *
 * @return string link
 */
function buddyblogvideos_get_post_publish_unpublish_link( $post_id = 0, $label_ac = '', $label_de = '' ) {

	if ( ! $post_id ) {
		return '';
	}

	$is_published = buddyblogvideos_is_post_published( $post_id );

	if ( $is_published && ! buddyblogvideos_user_can_unpublish( get_current_user_id(), $post_id ) ) {
		return '';
	} elseif ( ! $is_published && ! buddyblogvideos_user_can_publish( get_current_user_id(), $post_id ) ) {
		return '';
	}

	$post = get_post( $post_id );

	$url = '';
	$url = buddyblogvideos_get_post_publish_unpublish_url( $post_id );

	if ( empty( $label_ac ) ) {
		$label_ac = __( 'Publish', 'buddyblogvideos' );
	}

	if ( empty( $label_de ) ) {
		$label_de = __( 'Unpublish', 'buddyblogvideos' );
	}

	if ( $is_published ) {
		$link = "<a href='{$url}'>{$label_de}</a>";
	} else {
		$link = "<a href='{$url}'>{$label_ac}</a>";
	}

	return $link;
}

/**
 * Is this post published?
 *
 * @param int $post_id post id.
 *
 * @return bool
 */
function buddyblogvideos_is_post_published( $post_id ) {
	return get_post_field( 'post_status', $post_id ) == 'publish';
}

/**
 * Should we use slug in permalink?
 *
 * @return bool
 */
function buddyblogvideos_use_slug_in_permalink() {
	return apply_filters( 'buddyblogvideos_use_slug_in_permalink', false ); // Whether to use id or slug in permalink.
}

/**
 * Get the id of the post via
 *
 * @param string $slug post slug.
 *
 * @return int ID of Post
 */
function buddyblogvideos_get_post_id_from_slug( $slug ) {

	if ( ! $slug ) {
		return 0;
	}

	$post = get_page_by_path( $slug, false, buddyblogvideos_get_posttype() );

	if ( $post ) {
		return $post->ID;
	}

	return 0;

}

/**
 * Get the id of the post
 *
 * @param int|string $slug_or_id post id or slug.
 *
 * @return int ID of Post
 */
function buddyblogvideos_get_post_id( $slug_or_id ) {

	if ( is_numeric( $slug_or_id ) ) {
		return absint( $slug_or_id );
	}

	// otherwise.
	return buddyblogvideos_get_post_id_from_slug( $slug_or_id );
}

/**
 * Get an option.
 *
 * @param string $option_name option name.
 *
 * @return mixed
 */
function buddyblogvideos_get_option( $option_name ) {

	$settings = buddyblogvideos_get_settings();

	if ( isset( $settings[ $option_name ] ) ) {
		return $settings[ $option_name ];
	}

	return '';
}

/**
 * Was this post posted by buddyblog
 *
 * @param int $post_id post id.
 *
 * @return boolean
 */
function buddyblogvideos_is_buddyblogvideos_post( $post_id ) {
	return get_post_meta( $post_id, '_is_buddyblogvideos_post', true );
}

/**
 * Get BuddyBlog Settings
 *
 * @return array
 */
function buddyblogvideos_get_settings() {

	$default = array(
		//'root_slug'		=> 'buddyblogvideos',
		'post_type'             => 'video',
		'post_status'           => 'publish',
		'comment_status'        => 'open',
		'show_comment_option'   => 1,
		'custom_field_title'    => '',
		'enable_taxonomy'       => 1,
		'allowed_taxonomies'    => '',
		'enable_category'       => 1,
		'enable_tags'           => 1,
		'show_posts_on_profile' => 0,
		'limit_no_of_posts'     => 0,
		'max_allowed_posts'     => 20,
		'publish_cap'           => 'read',
		'allow_unpublishing'    => 1,// subscriber //see https://codex.wordpress.org/Roles_and_Capabilities.
		'post_cap'              => 'read',
		'allow_edit'            => 1,
		'allow_delete'          => 1,
		'allow_upload'          => 1,
		//'enabled_tags'			=> 1,
		//'taxonomies'		=> array( 'category' ),
		'allow_upload'          => false,
		'max_upload_count'      => 2,
		'post_update_redirect'  => 'archive',
	);

	return bp_get_option( 'buddyblogvideos-settings', $default );
}
