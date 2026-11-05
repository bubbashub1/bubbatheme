<?php
/**
 * Dynamic activity page, backed by the BubbaWP activity adapter.
 * Keeps the block-theme header/footer and existing reference design styles.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
$slug = sanitize_title( (string) get_query_var( 'bh_activity_slug', '' ) );
$id = $slug ? absint( apply_filters( 'bh_theme_activity_id_for_slug', 0, $slug ) ) : 0;
if ( ! $id ) {
    global $wp_query;
    $wp_query->set_404();
    status_header( 404 );
    nocache_headers();
}
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class( 'bh-dynamic-activity-page' ); ?>>
<?php wp_body_open(); ?>
<?php block_template_part( 'header' ); ?>
<main id="main-content" class="bh-standard-page bh-activity-page" style="padding:26px 25px">
<?php
if ( $id && shortcode_exists( 'bubba_activity' ) ) {
    echo do_shortcode( '[bubba_activity id="' . $id . '"]' );
} elseif ( $id ) {
    echo '<p class="bh-activity-notice">The activity display is temporarily unavailable.</p>';
} else {
    echo '<section class="bh-reference-panel"><h1>Activity not found</h1><p>This activity may have moved or is not published.</p><a href="' . esc_url( home_url( '/directory/' ) ) . '">Browse activities</a></section>';
}
?>
</main>
<?php block_template_part( 'footer' ); ?>
<?php wp_footer(); ?>
</body>
</html>
