<?php
/**
 * Misc filters.
 *
 * @package buddyblog.
 */

// we only need this if we don't want posts to be part of the main site blog and show it on user profile instead
// fix the redirect for comments posted on single post page
// comment posting a lil bit better.
/**
 * Fix comment form url for redirect.
 *
 * @param int $post_id post id.
 *
 * @return string
 */
function buddyblogvideos_fix_comment_form_redirect( $post_id ) {

	$post = get_post( $post_id );

	if ( ! buddyblogvideos_show_posts_on_profile( $post ) ) {
		return $post_id;
	}

	if ( $post->post_type != buddyblogvideos_get_posttype() ) {
		return;
	}

	$permalink = get_permalink( $post_id );

	?>
    <input type="hidden" name="redirect_to" value="<?php echo esc_url( $permalink ); ?>"/>

	<?php
}

add_action( 'comment_form', 'buddyblogvideos_fix_comment_form_redirect' );

/**
 * Get the current editable post id
 *
 * Used with buddyPress simple front end post editing plugin
 *
 * @param int $id post id.
 *
 * @return int id of the post to edit
 */
function buddyblogvideos_get_editable_post_id( $id ) {

	$action = bp_current_action();

	if ( bp_is_buddyblogvideos_component() && ( $action == 'edit' ) && bp_action_variable( 0 ) ) {

		$id = bp_action_variable( 0 );
	}

	// intval or absint?
	return intval( $id );
}

add_filter( 'bpsp_editable_post_id', 'buddyblogvideos_get_editable_post_id' );


/**
 * Filter feedback message for Front end post editor.
 *
 * @param string                   $message mesage.
 * @param int                      $post_id post id.
 * @param WP_Post_Type             $post_type_obj post type object.
 * @param BPSimpleBlogPostEditForm $form form object.
 *
 * @return mixed|string|void
 */
function buddyblogvideos_filter_posting_message( $message, $post_id, $post_type_obj, $form ) {

	if ( $form->get_post_type() != buddyblogvideos_get_posttype() ) {
		return $message;
	}

	if ( ! buddyblogvideos_is_post_published( $post_id ) ) {
		// if the job is not active, let us know that to the user.
		$message = __( 'Your post was saved.', 'buddyblogvideos' );
	}

	return $message;
}

add_filter( 'bsfep_post_success_message', 'buddyblogvideos_filter_posting_message', 10, 4 );

/**
 * Update the post status based on current user caps
 * If the current user can publish, we will set the status to publish otherwise the status is set to draft by default
 */
// NOT Needed
// add_action('bsfep_post_saved','buddyblogvideos_publish_unpublish_on_save');
function buddyblogvideos_publish_unpublish_on_save( $post_id ) {

	if ( ! empty( $_POST['post_id'] ) ) {
		return;
	}

	// check if it is job post type.
	$post = get_post( $post_id );

	if ( $post->post_type != buddyblogvideos_get_posttype() ) {
		return;
	}

	// if it is a new post.
	if ( buddyblogvideos_user_can_publish( get_current_user_id() ) ) {
		//    $active_status=1;

	}

	return $post_id;
}

/**
 * Fix the post permalink the_permalink to point to user profile/buddyblog/my-posts/ section for custom post type if we are using custom post type
 */
function buddyblogvideos_fix_permalink( $permalink, $post, $leavename, $sample ) {


	if ( ! buddyblogvideos_show_posts_on_profile( $post ) ) {
		return $permalink;
	}


	$type = buddyblogvideos_get_posttype();

	$user_link = '';

	if ( $post->post_type == $type ) {

		if ( $post->post_status != 'publish' ) {
			$permalink = buddyblogvideos_get_edit_url( $post->ID );
		} else {
			$permalink = buddyblogvideos_get_post_url( $post->ID );
		}
	}

	return $permalink;
}

add_filter( 'post_type_link', 'buddyblogvideos_fix_permalink', 10, 4 );

/**
 * Filter post permalink.
 *
 * @param string  $permalink permalink.
 * @param WP_Post $post post object.
 * @param string  $leavename leavename.
 *
 * @return string
 */
function buddyblogvideos_filter_post_permalink( $permalink, $post, $leavename ) {


	if ( ! buddyblogvideos_show_posts_on_profile( $post ) ) {
		return $permalink;
	}

	if ( $post->post_type != buddyblogvideos_get_posttype() ) {
		return $permalink;
	}

	if ( $post->post_status != 'publish' ) {
		$permalink = buddyblogvideos_get_edit_url( $post->ID );
	} else {
		$permalink = buddyblogvideos_get_post_url( $post->ID );
	}

	return $permalink;
	// if we are here, we need to change that permalink.

}

add_filter( 'post_link', 'buddyblogvideos_filter_post_permalink', 10, 3 );

/**
 * Fix edit post link to point to our intended link.
 *
 * @param string $edit_url original edit url.
 * @param int    $post_id post id.
 * @param string $context context.
 *
 * @return string
 */
function buddyblogvideos_fix_edit_post_link( $edit_url, $post_id, $context ) {

	$post = get_post( $post_id );

	if ( $post->post_type != buddyblogvideos_get_posttype() || is_super_admin() ) {
		return $edit_url;
	}

	if ( $post->post_author == get_current_user_id() && ! current_user_can( buddyblogvideos_get_option( 'dashboard_edit_cap' ) ) ) {
		return buddyblogvideos_get_edit_url( $post_id );
	}

	return $edit_url;

}

add_filter( 'get_edit_post_link', 'buddyblogvideos_fix_edit_post_link', 10, 3 );

/**
 * Cap filtering to show the edit link on posts
 *
 * We filter on the edit_post(or so) cap to check if the current user can edit this post
 * Mostly used for checking existing code
 *
 */
function buddyblogvideos_add_user_can_edit_cap( $allcaps, $cap, $args ) {

	$post_type_obj = get_post_type_object( buddyblogvideos_get_posttype() );

	if ( is_null( $post_type_obj ) || $post_type_obj->cap->edit_post != $args[0] ) {
		return $allcaps;
	}

	$post_id = $args[2];

	if ( buddyblogvideos_user_can_edit( $post_id ) ) {
		$allcaps[ $cap[0] ] = true;
	}

	return $allcaps;
	// give author some permissions
}

add_filter( 'user_has_cap', 'buddyblogvideos_add_user_can_edit_cap', 0, 3 );

/**
 * Should we limit user by no. of posts he has made?
 *
 * @return bool
 */
function buddyblogvideos_limit_no_of_posts() {

	return apply_filters( 'buddyblogvideos_limit_no_of_posts', buddyblogvideos_get_option( 'limit_no_of_posts' ) );
}

/**
 * Should we show the single post on profile
 *
 * @return bool
 */
function buddyblogvideos_show_posts_on_profile( $post ) {
	return apply_filters( 'buddyblogvideos_show_posts_on_profile', buddyblogvideos_get_option( 'show_posts_on_profile' ), $post );
}

/**
 * Modify page title
 *
 * @param array $bp_title Array of title info.
 *
 * @return mixed
 */
function buddyblogvideos_modify_page_title( $bp_title ) {

	if ( ! bp_is_buddyblogvideos_component() ) {
		return $bp_title;
	}

	$post_type_obj = get_post_type_object( buddyblogvideos_get_posttype() );
	$sep           = apply_filters( 'document_title_separator', '-' );

	if ( buddyblogvideos_is_single_post() ) {
		$post_id           = buddyblogvideos_get_post_id( bp_action_variable( 0 ) );
		$post              = get_post( $post_id );
		$bp_title['title'] = $post->post_title . ' ' . $sep . ' ' . $bp_title['title'];
		$bp_title['page']  = '';
	} elseif ( buddyblogvideos_is_edit_post() ) {
		$bp_title['title'] = $post_type_obj->labels->edit_item;
	} elseif ( buddyblogvideos_is_new_post() ) {
		$bp_title['title'] = $post_type_obj->labels->new_item;
	}

	return $bp_title;
}

add_filter( 'bp_modify_document_title_parts', 'buddyblogvideos_modify_page_title' );
