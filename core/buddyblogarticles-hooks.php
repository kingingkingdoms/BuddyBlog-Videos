<?php
/**
 * Miscellaneous hooks.
 *
 * @package buddyblog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fix to disable/re-enable buddypress comment open/close filter.
 */
function buddyblogphotos_disable_bp_comment_filter() {

	if ( has_filter( 'comments_open', 'bp_comments_open' ) ) {
		remove_filter( 'comments_open', 'bp_comments_open', 10, 2 );
	}
}

add_action( 'buddyblogphotos_before_blog_post', 'buddyblogphotos_disable_bp_comment_filter' );

/**
 * Re enable buddypress comments filter.
 */
function buddyblogphotos_enable_bp_comment_filter() {

	if ( function_exists( 'bp_comments_open' ) ) {
		add_filter( 'comments_open', 'bp_comments_open', 10, 2 );
	}
}

add_action( 'buddyblogphotos_after_blog_post', 'buddyblogphotos_enable_bp_comment_filter' );
