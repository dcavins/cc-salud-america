<?php
/**
 * Shorcodes for use in content blocks.
 *
 * Community Commons Salud America
 *
 * @package   Community_Commons_Salud_America
 * @author    David Cavins
 * @license   GPL-2.0+
 * @link      http://www.communitycommons.org
 * @copyright 2013 Community Commons
 */

/**
 * Create all six advocacy target icons with links to the taxonomy archive
 *
 * @since   1.0.0
 * @param   string $section used to incorporate correct section in link
 * @param   int $columns  number of columns to arrange icons in
 * @param   int $icon_size Size of icons to use, in px. Will be converted to 30, 60 or 90.
 * @return  html used to show icons
 */
function sa_advocacy_target_icon_links_shortcode( $atts ) {
    $a = shortcode_atts( array(
        'section' => 'changes',
        'columns' => 3,
        'icon_size' => 90
        ), $atts );
    ob_start();
    sa_advocacy_target_icon_links( $a['section'], $a['columns'], $a['icon_size'] );
    return ob_get_clean();
}
add_shortcode( 'sa_advocacy_target_icon_links', 'sa_advocacy_target_icon_links_shortcode' );

/**
 * Output policy search form and build search results.
 *
 * @since   1.0.0
 *
 * @return  html
 */
function sa_policy_search_form_shortcode( $atts ) {
    ob_start();
    sa_searchpolicies();
    return ob_get_clean();
}
add_shortcode( 'sa_policy_search_form', 'sa_policy_search_form_shortcode' );

/**
 * Output html for a small recent policy loop, like on the group home page.
 *
 * @since   1.0.0
 *
 * @param   int $columns Number of columns the posts should be displayed in.
 * @param   int $numposts How many posts to fetch.
 * @return  html The blocks and their contents.
 */
function sa_recent_policies_shortcode( $atts ) {
    $a = shortcode_atts( array(
        'columns' => 3,
        'posts' => 3
        ), $atts );
    ob_start();
    sa_recent_posts_loop( 'policies', $a['columns'], $a['posts'] );
    return ob_get_clean();
}
add_shortcode( 'sa_recent_policies', 'sa_recent_policies_shortcode' );

/**
 * Output html for a small recent policy loop, like on the group home page.
 *
 * @since   1.0.0
 *
 * @param   int $columns Number of columns the posts should be displayed in.
 * @param   int $numposts How many posts to fetch.
 * @return  html The blocks and their contents.
 */
function sa_recent_heroes_shortcode( $atts ) {
    $a = shortcode_atts( array(
        'columns' => 3,
        'posts' => 3
        ), $atts );
    ob_start();
    sa_recent_posts_loop( 'heroes', $a['columns'], $a['posts'] );
    return ob_get_clean();
}
add_shortcode( 'sa_recent_heroes', 'sa_recent_heroes_shortcode' );

/**
 * Output html for a small recent hero loop, like on the group home page.
 *
 * @since   1.0.0
 *
 * @param   int $columns Number of columns the posts should be displayed in.
 * @param   int $numposts How many posts to fetch.
 * @return  html The blocks and their contents.
 */
function sa_random_hero_video_shortcode() {
    ob_start();
    sa_get_random_hero_video();
    return ob_get_clean();
}
add_shortcode( 'sa_random_hero_video', 'sa_random_hero_video_shortcode' );