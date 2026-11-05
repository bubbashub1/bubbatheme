<?php
/**
 * Activity presentation boundary for Bubba Hub 3.0.
 *
 * The data provider (plugin) can supply a normalized array through
 * bh_theme_activity_data. The theme does not query legacy bh_ tables.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_shortcode( 'bubba_activity', static function ( $atts ) {
    $atts = shortcode_atts( array( 'id' => 0 ), $atts, 'bubba_activity' );
    $id = absint( $atts['id'] );
    if ( ! $id ) {
        return current_user_can( 'edit_pages' ) ? '<p class="bh-activity-notice">Set an activity ID in the shortcode to preview an activity.</p>' : '';
    }
    $activity = apply_filters( 'bh_theme_activity_data', null, $id );
    if ( ! is_array( $activity ) || empty( $activity['title'] ) ) {
        return current_user_can( 'edit_pages' ) ? '<p class="bh-activity-notice">Activity data is not connected for ID ' . esc_html( (string) $id ) . '. The Bubba Hub activity plugin must provide the bh_theme_activity_data filter.</p>' : '';
    }
    $field = static function ( $key, $default = '' ) use ( $activity ) {
        return isset( $activity[ $key ] ) && is_scalar( $activity[ $key ] ) ? (string) $activity[ $key ] : $default;
    };
    $title = $field( 'title' );
    $facts = array(
        'Price' => $field( 'price' ), 'Age range' => $field( 'age_range' ),
        'Location' => $field( 'location' ), 'Sessions' => $field( 'sessions_count' ),
        'Term time' => $field( 'term_time' ), 'Session length' => $field( 'session_length' ),
    );
    $categories = isset( $activity['categories'] ) && is_array( $activity['categories'] ) ? array_filter( array_map( 'sanitize_text_field', $activity['categories'] ) ) : array();
    $schedule = isset( $activity['schedule'] ) && is_array( $activity['schedule'] ) ? $activity['schedule'] : array();
    $image = $field( 'image_url' );
    $booking = $field( 'booking_url' );
    $directions = $field( 'directions_url' );
    ob_start();
    ?>
    <article class="bh-activity-dynamic bh-activity-reference">
      <header class="bh-dynamic-hero"<?php if ( $image ) : ?> style="background-image:linear-gradient(0deg,rgba(0,0,0,.74),rgba(0,0,0,.08)),url('<?php echo esc_url( $image ); ?>')"<?php endif; ?>>
        <div class="bh-dynamic-hero-copy">
          <?php if ( $categories ) : ?><p class="bh-reference-kicker"><?php echo esc_html( implode( ' · ', array_slice( $categories, 0, 2 ) ) ); ?></p><?php endif; ?>
          <h1><?php echo esc_html( $title ); ?></h1>
          <?php if ( $field( 'summary' ) ) : ?><p><?php echo esc_html( $field( 'summary' ) ); ?></p><?php endif; ?>
        </div>
        <?php if ( $booking ) : ?><a class="bh-dynamic-book" href="<?php echo esc_url( $booking ); ?>" rel="noopener noreferrer">Book now</a><?php endif; ?>
      </header>
      <div class="bh-reference-facts bh-dynamic-facts">
        <?php foreach ( $facts as $label => $value ) : if ( '' === $value ) { continue; } ?>
          <div><small><?php echo esc_html( $label ); ?></small><strong><?php echo esc_html( $value ); ?></strong></div>
        <?php endforeach; ?>
      </div>
      <div class="bh-dynamic-grid">
        <section class="bh-reference-panel">
          <?php if ( $categories ) : ?><p class="bh-reference-tags"><?php echo esc_html( implode( ' · ', $categories ) ); ?></p><?php endif; ?>
          <h2>About <?php echo esc_html( $title ); ?></h2>
          <div class="bh-dynamic-description"><?php echo wp_kses_post( wpautop( $field( 'description' ) ) ); ?></div>
          <?php if ( $field( 'organiser' ) ) : ?><p><strong>Organiser:</strong> <?php echo esc_html( $field( 'organiser' ) ); ?></p><?php endif; ?>
          <?php if ( $field( 'website_url' ) ) : ?><p><a href="<?php echo esc_url( $field( 'website_url' ) ); ?>" rel="noopener noreferrer">Organiser website</a></p><?php endif; ?>
        </section>
        <div>
          <section class="bh-reference-panel">
            <h2>Location &amp; directions</h2>
            <?php if ( $field( 'venue' ) ) : ?><p><?php echo esc_html( $field( 'venue' ) ); ?></p><?php endif; ?>
            <?php if ( $field( 'address' ) ) : ?><p><?php echo esc_html( $field( 'address' ) ); ?></p><?php endif; ?>
            <?php if ( $directions ) : ?><p><a href="<?php echo esc_url( $directions ); ?>" rel="noopener noreferrer">Directions</a></p><?php endif; ?>
            <?php if ( $field( 'map_html' ) ) : ?><div class="bh-dynamic-map"><?php echo wp_kses_post( $field( 'map_html' ) ); ?></div><?php endif; ?>
          </section>
          <?php if ( $schedule ) : ?>
          <section class="bh-reference-panel">
            <p class="bh-dynamic-eyebrow">WHEN</p><h2>Timetable</h2>
            <div class="bh-session-rows" role="list" aria-label="Upcoming class sessions">
              <?php foreach ( $schedule as $session ) :
                  if ( ! is_array( $session ) ) { continue; }
                  $value = static function ( $key ) use ( $session ) {
                      return isset( $session[ $key ] ) && is_scalar( $session[ $key ] ) ? (string) $session[ $key ] : '';
                  };
                  $session_name = $value( 'name' );
                  $session_day  = $value( 'date' ) ?: $value( 'day' );
                  $session_start = $value( 'start' );
                  $session_end   = $value( 'end' );
                  $session_price = $value( 'price' );
                  $session_booking = $value( 'booking_url' );
                  $session_enquiry = $value( 'enquiry_url' );
                  $session_term = $value( 'term_time' );
                  if ( '' === $session_term && ! empty( $session['term_time_only'] ) ) { $session_term = 'Term time'; }
              ?>
              <div class="bh-session-row" role="listitem">
                <div class="bh-session-row-date">
                  <?php if ( $session_day ) : ?><strong><?php echo esc_html( $session_day ); ?></strong><?php endif; ?>
                  <?php if ( $session_start || $session_end ) : ?><span><?php echo esc_html( trim( $session_start . ( $session_end ? ' – ' . $session_end : '' ) ) ); ?></span><?php endif; ?>
                </div>
                <div class="bh-session-row-details">
                  <?php if ( $session_name ) : ?><strong><?php echo esc_html( $session_name ); ?></strong><?php endif; ?>
                  <?php if ( $session_term ) : ?><span class="bh-session-term"><?php echo esc_html( $session_term ); ?></span><?php endif; ?>
                  <?php if ( $session_price ) : ?><small><?php echo esc_html( $session_price ); ?></small><?php endif; ?>
                </div>
                <?php if ( $session_enquiry || $session_booking ) : ?>
                <div class="bh-session-row-actions">
                  <?php if ( $session_enquiry ) : ?><a class="bh-session-enquire" href="<?php echo esc_url( $session_enquiry ); ?>">Enquire</a><?php endif; ?>
                  <?php if ( $session_booking ) : ?><a class="bh-session-book" href="<?php echo esc_url( $session_booking ); ?>">Book now</a><?php endif; ?>
                </div>
                <?php endif; ?>
              </div>
              <?php endforeach; ?>
            </div>
          </section>
          <?php endif; ?>
        </div>
      </div>
      <?php if ( ! empty( $activity['enquiry_enabled'] ) && shortcode_exists( 'fluentform' ) ) : ?>
      <section id="bh-activity-enquiry" class="bh-reference-panel bh-activity-enquiry">
        <h2>Interested in joining?</h2>
        <p>Send an enquiry to the class leader. This is not a confirmed booking.</p>
        <?php echo do_shortcode( '[fluentform id="6"]' ); ?>
      </section>
      <?php endif; ?>
    </article>
    <?php
    return (string) ob_get_clean();
} );
