<?php
/**
 * Bubba Hub native block theme bootstrap.
 *
 * @package BubbaHubTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action(
    'after_setup_theme',
    static function () {
        add_theme_support( 'wp-block-styles' );
        add_theme_support( 'responsive-embeds' );
        add_theme_support( 'editor-styles' );
        add_editor_style( array( 'assets/css/site.css', 'assets/css/editor.css' ) );
        add_theme_support( 'post-thumbnails' );
    }
);

add_action(
    'wp_enqueue_scripts',
    static function () {
        wp_enqueue_style(
            'bubba-hub-theme',
            get_theme_file_uri( 'assets/css/site.css' ),
            array(),
            file_exists( get_theme_file_path( 'assets/css/site.css' ) ) ? (string) filemtime( get_theme_file_path( 'assets/css/site.css' ) ) : wp_get_theme()->get( 'Version' )
        );
        wp_enqueue_style(
            'bubba-hub-portal-jump',
            get_theme_file_uri( 'assets/css/portal-jump.css' ),
            array( 'bubba-hub-theme' ),
            (string) filemtime( get_theme_file_path( 'assets/css/portal-jump.css' ) )
        );
        wp_enqueue_script(
            'bubba-hub-portal-jump',
            get_theme_file_uri( 'assets/js/portal-jump.js' ),
            array(),
            (string) filemtime( get_theme_file_path( 'assets/js/portal-jump.js' ) ),
            true
        );
    }
);

/* Load Quicksand on both the public site and in the Site Editor. */
add_action(
    'enqueue_block_assets',
    static function () {
        wp_enqueue_style(
            'bubba-hub-quicksand',
            'https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600;700&display=swap',
            array(),
            null
        );
    }
);

require_once get_theme_file_path( 'inc/activity-renderer.php' );

/** Scope Simple Membership presentation to membership pages without changing plugin behaviour. */
add_filter( 'body_class', static function ( $classes ) {
    if ( ! is_page() ) {
        return $classes;
    }

    $page_id = get_queried_object_id();
    $slug    = (string) get_post_field( 'post_name', $page_id );
    $content = (string) get_post_field( 'post_content', $page_id );

    if ( 0 === strpos( $slug, 'membership-' ) || preg_match( '/\\[swpm_[^\\]]+\\]/', $content ) ) {
        $classes[] = 'bh-simple-membership';
    }

    return $classes;
} );

/** Mark singular pages without a featured image for the default hero artwork. */
add_filter( 'body_class', static function ( $classes ) {
    if ( is_singular() && ! has_post_thumbnail( get_queried_object_id() ) ) {
        $classes[] = 'bh-no-featured-image';
    }
    return $classes;
} );
