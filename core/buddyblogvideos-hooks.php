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
function buddyblogvideos_disable_bp_comment_filter() {

	if ( has_filter( 'comments_open', 'bp_comments_open' ) ) {
		remove_filter( 'comments_open', 'bp_comments_open', 10, 2 );
	}
}

add_action( 'buddyblogvideos_before_blog_post', 'buddyblogvideos_disable_bp_comment_filter' );

/**
 * Re enable buddypress comments filter.
 */
function buddyblogvideos_enable_bp_comment_filter() {

	if ( function_exists( 'bp_comments_open' ) ) {
		add_filter( 'comments_open', 'bp_comments_open', 10, 2 );
	}
}

add_action( 'buddyblogvideos_after_blog_post', 'buddyblogvideos_enable_bp_comment_filter' );
