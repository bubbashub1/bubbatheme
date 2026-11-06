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
            'bubba-hub-coastal-explorer',
            get_theme_file_uri( 'assets/css/coastal-explorer.css' ),
            array( 'bubba-hub-theme' ),
            file_exists( get_theme_file_path( 'assets/css/coastal-explorer.css' ) ) ? (string) filemtime( get_theme_file_path( 'assets/css/coastal-explorer.css' ) ) : wp_get_theme()->get( 'Version' )
        );
        wp_enqueue_style(
            'bubba-hub-booking-reference',
            get_theme_file_uri( 'assets/css/booking-reference.css' ),
            array( 'bubba-hub-theme' ),
            file_exists( get_theme_file_path( 'assets/css/booking-reference.css' ) ) ? (string) filemtime( get_theme_file_path( 'assets/css/booking-reference.css' ) ) : wp_get_theme()->get( 'Version' )
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

/**
 * Use the real activity renderer for /activity/{slug}/.
 * The Activity Design Preview pattern remains an untouched editor demo.
 */
add_filter( 'template_include', static function ( $template ) {
    if ( is_admin() || ! is_page( 'activity' ) ) { return $template; }
    $slug = sanitize_title( (string) get_query_var( 'bh_activity_slug', '' ) );
    if ( ! $slug ) { return $template; }
    return get_theme_file_path( 'inc/activity-page.php' );
}, 99 );

/**
 * Staging-only activity comparison page. Stores no family data on the server.
 * This page is created once, without changing existing pages.
 */
add_action( 'init', static function () {
    if ( wp_parse_url( home_url(), PHP_URL_HOST ) !== 'staging.bubbahub.co.uk' || get_option( 'bh_compare_page_setup_v1' ) ) {
        return;
    }
    $existing = get_page_by_path( 'compare-activities' );
    if ( ! $existing ) {
        $created = wp_insert_post( array(
            'post_type' => 'page',
            'post_status' => 'publish',
            'post_title' => 'Compare Activities',
            'post_name' => 'compare-activities',
            'post_content' => '<!-- wp:shortcode -->[bh_compare_activities]<!-- /wp:shortcode -->',
        ), true );
        if ( is_wp_error( $created ) ) return;
    }
    update_option( 'bh_compare_page_setup_v1', 1, false );
}, 30 );

add_shortcode( 'bh_compare_activities', static function () {
    ob_start();
    ?>
    <section class="bh-compare-page" style="max-width:1200px;margin:36px auto;padding:0 25px;color:#173c34">
      <h1>Compare Activities</h1>
      <p>Choose up to four activities using the Compare button on an activity page. Your shortlist stays in this browser.</p>
      <div id="bh-compare-page-items" aria-live="polite"></div>
      <p><a href="<?php echo esc_url( home_url( '/directory/' ) ); ?>">Find more activities</a></p>
    </section>
    <script>
    (function () {
      var target=document.getElementById('bh-compare-page-items');if(!target)return;
      var key='bh_activity_compare_v1',items=[];
      try { var saved=JSON.parse(localStorage.getItem(key)||'[]');if(Array.isArray(saved))items=saved.filter(function(x){return x&&x.id&&x.title&&x.url;}).slice(0,4); }catch(e){}
      function draw(){
        target.replaceChildren();
        if(!items.length){var p=document.createElement('p');p.textContent='No activities selected yet.';target.appendChild(p);return;}
        var grid=document.createElement('div');grid.style.cssText='display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:18px;margin:24px 0';
        items.forEach(function(item){
          var card=document.createElement('article');card.style.cssText='padding:22px;border:1px solid #d5e5db;border-radius:16px;background:white';
          var title=document.createElement('h2');title.style.cssText='font-size:1.25rem;margin:0 0 16px';title.textContent=item.title;
          var link=document.createElement('a');link.href=item.url;link.textContent='View activity';link.style.cssText='color:#175c46;font-weight:700';
          var remove=document.createElement('button');remove.type='button';remove.textContent='Remove';remove.style.cssText='display:block;margin-top:20px;border:1px solid #175c46;border-radius:8px;padding:9px 15px;background:#fff;color:#175c46;cursor:pointer';
          remove.addEventListener('click',function(){items=items.filter(function(x){return x.id!==item.id;});try{localStorage.setItem(key,JSON.stringify(items));}catch(e){}draw();});
          card.append(title,link,remove);grid.appendChild(card);
        });
        target.appendChild(grid);
      }
      draw();
    })();
    </script>
    <?php
    return ob_get_clean();
} );

/**
 * Saved Site Editor header template parts override parts/header.html.
 * Append the search to the rendered header when that saved template lacks it.
 * This does not alter or reset the user's navigation customisations.
 */
add_filter( 'render_block_core/template-part', static function ( $content, $block ) {
    $attrs = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();
    if ( ( $attrs['slug'] ?? '' ) !== 'header' || strpos( $content, 'bh-header-search' ) !== false ) {
        return $content;
    }
    if ( ! shortcode_exists( 'bh_header_search' ) ) {
        return $content;
    }
    $search = do_shortcode( '[bh_header_search]' );
    if ( ! $search || strpos( $search, 'bh-header-search' ) === false ) {
        return $content;
    }
    // Saved header templates: inject next to the actual navigation, not below the header.
    $inline = '<div class="bh-header-search-inline bh-header-search-fallback">' . $search . '</div>';
    if ( strpos( $content, '</nav>' ) !== false ) {
        return preg_replace( '~</nav>~i', '</nav>' . $inline, $content, 1 );
    }
    // Keep the search accessible if a customised header contains no navigation.
    return $content . $inline;
}, 20, 2 );

/**
 * Personalised header greeting, placed immediately before Log out.
 * Applies to both theme header and saved Site Editor header templates.
 */
add_filter( 'render_block_core/template-part', static function ( $content, $block ) {
    $attrs = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();
    if ( ( $attrs['slug'] ?? '' ) !== 'header' || ! is_user_logged_in() || strpos( $content, 'bh-header-greeting' ) !== false ) {
        return $content;
    }
    $user = wp_get_current_user();
    $name = $user->display_name ? $user->display_name : $user->user_login;
    $greeting = '<span class="bh-header-greeting">Hello ' . esc_html( $name ) . '</span>';
    // Match a single logout anchor, including nested WordPress navigation label spans.
    $updated = preg_replace_callback(
        '~<a\\b[^>]*>(?:(?!</a>).)*?(?:Log\\s*out|Logout|Sign\\s*out)(?:(?!</a>).)*?</a>~is',
        static function ( $match ) use ( $greeting ) {
            return $greeting . $match[0];
        },
        $content,
        1,
        $count
    );
    if ( $count ) {
        return $updated;
    }
    // A customised header may not expose a logout anchor.
    return $content;
}, 30, 2 );

/** Compact site-wide directory search; filter names match BH_Directory_3. */
add_shortcode( 'bh_header_search', static function () {
    $selected = static function ( $key ) {
        return isset( $_GET[ $key ] ) && is_scalar( $_GET[ $key ] ) ? sanitize_text_field( wp_unslash( $_GET[ $key ] ) ) : '';
    };
    ob_start();
    ?>
    <div class="bh-header-search-widget">
      <button type="button" class="bh-header-search-toggle" aria-expanded="false" aria-label="Open activity search" title="Search activities">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-4-4"></path></svg>
        <span class="screen-reader-text">Search activities</span>
      </button>
      <div class="bh-header-search-panel" hidden>
    <form class="bh-header-search" role="search" method="get" action="<?php echo esc_url( home_url( '/directory/' ) ); ?>">
      <label class="screen-reader-text" for="bh-header-keyword">Search activities</label>
      <input id="bh-header-keyword" name="bh_search" type="search" placeholder="Keyword" value="<?php echo esc_attr( $selected( 'bh_search' ) ); ?>">
      <label class="screen-reader-text" for="bh-header-region">Region</label>
      <select id="bh-header-region" name="bh_region">
        <option value="">Region</option>
        <?php foreach ( array( 'Devon', 'Cornwall' ) as $region ) : ?>
        <option value="<?php echo esc_attr( $region ); ?>" <?php selected( $selected( 'bh_region' ), $region ); ?>><?php echo esc_html( $region ); ?></option>
        <?php endforeach; ?>
      </select>
      <label class="screen-reader-text" for="bh-header-town">Town</label>
      <input id="bh-header-town" name="bh_town" type="text" placeholder="Town" value="<?php echo esc_attr( $selected( 'bh_town' ) ); ?>">
      <label class="screen-reader-text" for="bh-header-category">Category</label>
      <input id="bh-header-category" name="bh_category" type="text" placeholder="Category" value="<?php echo esc_attr( $selected( 'bh_category' ) ); ?>">
      <label class="screen-reader-text" for="bh-header-day">Day</label>
      <select id="bh-header-day" name="bh_day">
        <option value="">Day</option>
        <?php foreach ( array( 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday' ) as $number => $name ) : ?>
        <option value="<?php echo esc_attr( $number ); ?>" <?php selected( $selected( 'bh_day' ), (string) $number ); ?>><?php echo esc_html( $name ); ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit">Search</button>
    </form>
      </div>
    </div>
    <script>
    (function(){
      if(window.bhHeaderSearchBound)return;
      window.bhHeaderSearchBound=true;
      document.addEventListener('click',function(event){
        var button=event.target.closest('.bh-header-search-toggle');
        if(button){
          var widget=button.closest('.bh-header-search-widget'),panel=widget&&widget.querySelector('.bh-header-search-panel');
          if(!panel)return;
          var opening=panel.hidden;
          document.querySelectorAll('.bh-header-search-panel').forEach(function(p){p.hidden=true;});
          document.querySelectorAll('.bh-header-search-toggle').forEach(function(b){b.setAttribute('aria-expanded','false');});
          panel.hidden=!opening;button.setAttribute('aria-expanded',opening?'true':'false');
          if(opening){var field=panel.querySelector('input[type="search"]');if(field)field.focus();}
          return;
        }
        if(!event.target.closest('.bh-header-search-widget')){
          document.querySelectorAll('.bh-header-search-panel').forEach(function(p){p.hidden=true;});
          document.querySelectorAll('.bh-header-search-toggle').forEach(function(b){b.setAttribute('aria-expanded','false');});
        }
      });
      document.addEventListener('keydown',function(event){if(event.key==='Escape'){document.querySelectorAll('.bh-header-search-panel').forEach(function(p){p.hidden=true;});document.querySelectorAll('.bh-header-search-toggle').forEach(function(b){b.setAttribute('aria-expanded','false');});}});
    })();
    </script>
    <?php
    return ob_get_clean();
} );
