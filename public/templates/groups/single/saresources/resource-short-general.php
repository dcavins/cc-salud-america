<?php
$post_id = get_the_ID();
?>

<article id="post-<?php echo $post_id; ?>" <?php post_class( array( 'resource-short-general', 'sa-item-short-form', 'clear' ) ); ?>>
    <div class="entry-content">
        <header class="entry-header">
            <h3 class="entry-title">
            <?php cc_post_type_flag(); ?>
            <a href="<?php the_permalink(); ?>" title="<?php echo esc_attr( sprintf( __( 'Permalink to %s', 'twentytwelve' ), the_title_attribute( 'echo=0' ) ) ); ?>" rel="bookmark"><?php the_title(); ?></a>
            </h3>
            <?php
            // Featured image or fallback.
            if ( has_post_thumbnail() ) {
                the_post_thumbnail( 'feature-front-sub', array( 'class' => 'attachment-feature-front-sub alignleft' ) );
            } else {
                echo sa_get_advo_target_fallback_image_for_post( $post_id, 'feature-front-sub', 'alignleft' );
            }
            ?>
            <?php salud_the_target_icons(); ?>
            <?php /* Resources do not have locations, at the moment ?>
            <p class="location"><?php salud_the_location( 'saresources' ); ?></p>
            <?php */ ?>
        </header>

        <p><?php
        $excerpt = get_the_excerpt();
        if ( ! empty( $excerpt ) ) {
            echo $excerpt;
        } else {
            the_content();
        }
        ?></p>
        <?php twentytwelve_entry_meta(); ?>
    </div><!-- .entry-content -->
</article><!-- #post -->