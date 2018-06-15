<?php
/**
 * This file is used for listing the posts on profile
 */
?>

<?php if ( buddyblogvideos_user_has_posted() ): ?>
<?php
    //let us build the post query
    if ( is_super_admin() ) {
 		$status = 'any';
	} else {
		$status = 'publish';
	}
	
    $paged = bp_action_variable( 1 );
    $paged = $paged ? $paged : 1;
    
	$query_args = array(
		'author'        => bp_displayed_user_id(),
		'post_type'     => buddyblogvideos_get_posttype(),
		'post_status'   => $status,
		'paged'         => intval( $paged )
    );
	//do the query
    query_posts( $query_args );
	?>
    
	<?php if ( have_posts() ): ?>
		
		<?php while ( have_posts() ): the_post();
			global $post;
		?>

            <div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

                <div class="buddyblog-video-content">
                    
                    <?php if ( function_exists( 'has_post_thumbnail' ) && has_post_thumbnail( get_the_ID() ) ):?>
                        
                        <div class="buddyblog-video-featured-image">
                            <?php  the_post_thumbnail();?>
                        </div>

                    <?php endif;?>

                    <h2 class="buddyblog-video-title"> <a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php _e( 'Permanent Link to', 'buddyblogvideos' ); ?> <?php the_title_attribute(); ?>"><?php the_title(); ?></a> </h2>

                    <p class="buddyblog-video-date"><?php printf( __( '%1$s <span>in %2$s</span>', 'buddyblogvideos' ), get_the_date(), get_the_category_list( ', ' ) ); ?></p>

                    <div class="buddyblog-video-entry">

                        <?php the_content( __( 'Read the rest of this entry &rarr;', 'buddyblogvideos' ) ); ?>
                        <?php wp_link_pages( array( 'before' => '<div class="buddyblog-video-page-link"><p>' . __( 'Pages: ', 'buddyblogvideos' ), 'after' => '</p></div>', 'next_or_number' => 'number' ) ); ?>
                    </div>

                    <p class="buddyblog-video-meta-data"><?php the_tags( '<span class="buddyblog-video-tags">' . __( 'Tags: ', 'buddyblogvideos' ), ', ', '</span>' ); ?> <span class="buddyblog-video-comments"><?php comments_popup_link( __( 'No Comments &#187;', 'buddyblogvideos' ), __( '1 Comment &#187;', 'buddyblogvideos' ), __( '% Comments &#187;', 'buddyblogvideos' ) ); ?></span></p>

                    <div class="buddyblog-video-actions">
                        <?php echo buddyblogvideos_get_post_publish_unpublish_link( get_the_ID() );?>
                        <?php echo buddyblogvideos_get_edit_link();?>
                        <?php echo buddyblogvideos_get_delete_link();?>
                    </div>     
                </div>

			</div>
                   
        <?php endwhile;?>
            <div class="buddyblog-video-pagination">
                <?php buddyblogvideos_paginate(); ?>
            </div>
    <?php else: ?>
            <p><?php _e( 'There are no posts by this user at the moment. Please check back later!', 'buddyblogvideos' );?></p>
    <?php endif; ?>

    <?php 
       wp_reset_postdata();
       wp_reset_query();
    ?>

<?php elseif ( bp_is_my_profile() && buddyblogvideos_user_can_post( get_current_user_id() ) ): ?>
    <p> <?php _e( "You haven't posted anything yet.", 'buddyblogvideos' );?> <a href="<?php echo buddyblogvideos_get_new_url();?>"> <?php _e( 'New Post', 'buddyblogvideos' );?></a></p>

<?php endif; ?>
