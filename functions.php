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
        add_editor_style( 'assets/css/site.css' );
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
