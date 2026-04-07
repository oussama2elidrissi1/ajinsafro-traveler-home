<?php
/**
 * Voyages Page Template
 *
 * Displays all trips (st_tours) with integrated search bar.
 *
 * @package AjinsafroTravelerHome
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

$settings = ajth_get_settings();

$paged = max(
    1,
    absint( get_query_var( 'paged' ) ),
    absint( get_query_var( 'page' ) )
);

$location_name = isset( $_GET['location_name'] ) ? sanitize_text_field( wp_unslash( $_GET['location_name'] ) ) : '';
$search_text   = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
$keyword       = $location_name !== '' ? $location_name : $search_text;

// CRUD-aligned filters (synced tour data)
$category_slug   = isset( $_GET['cat'] ) ? sanitize_text_field( wp_unslash( $_GET['cat'] ) ) : '';
$tag_slug        = isset( $_GET['tag'] ) ? sanitize_text_field( wp_unslash( $_GET['tag'] ) ) : '';
$location_id     = isset( $_GET['location_id'] ) ? absint( $_GET['location_id'] ) : 0;
$featured_only   = isset( $_GET['featured'] ) && (string) $_GET['featured'] === '1';
$depart_date     = isset( $_GET['depart_date'] ) ? sanitize_text_field( wp_unslash( $_GET['depart_date'] ) ) : ''; // YYYY-MM-DD
$duration_min    = isset( $_GET['duration_min'] ) ? absint( $_GET['duration_min'] ) : 0;
$duration_max    = isset( $_GET['duration_max'] ) ? absint( $_GET['duration_max'] ) : 0;
$price_min       = isset( $_GET['price_min'] ) ? absint( $_GET['price_min'] ) : 0;
$price_max       = isset( $_GET['price_max'] ) ? absint( $_GET['price_max'] ) : 0;

$query_args = array(
    'post_type'      => 'st_tours',
    'post_status'    => 'publish',
    'posts_per_page' => 12,
    'paged'          => $paged,
    'orderby'        => 'date',
    'order'          => 'DESC',
);

if ( $keyword !== '' ) {
    $query_args['s'] = $keyword;
}

// Taxonomy filters
$tax_query = array();
if ( $category_slug !== '' ) {
    $tax_query[] = array(
        'taxonomy' => 'tours_cat',
        'field'    => 'slug',
        'terms'    => array( $category_slug ),
    );
}
if ( $tag_slug !== '' ) {
    $tax_query[] = array(
        'taxonomy' => 'tour_tag',
        'field'    => 'slug',
        'terms'    => array( $tag_slug ),
    );
}
if ( ! empty( $tax_query ) ) {
    $query_args['tax_query'] = array_merge( array( 'relation' => 'AND' ), $tax_query );
}

// Meta filters (synced from CRUD/Traveler metas)
$meta_query = array();
if ( $featured_only ) {
    $meta_query[] = array(
        'key'     => 'is_featured',
        'value'   => 'on',
        'compare' => '=',
    );
}
if ( $location_id > 0 ) {
    // Traveler location metas can be in st_location_id / location_id / id_location or multi_location
    $meta_query[] = array(
        'relation' => 'OR',
        array( 'key' => 'st_location_id', 'value' => (string) $location_id, 'compare' => '=' ),
        array( 'key' => 'location_id', 'value' => (string) $location_id, 'compare' => '=' ),
        array( 'key' => 'id_location', 'value' => (string) $location_id, 'compare' => '=' ),
        // multi_location format: "_12_,_15_" or CSV
        array( 'key' => 'multi_location', 'value' => '_' . $location_id . '_', 'compare' => 'LIKE' ),
        array( 'key' => 'multi_location', 'value' => (string) $location_id, 'compare' => 'LIKE' ),
    );
}
if ( $duration_min > 0 || $duration_max > 0 ) {
    $min = $duration_min > 0 ? $duration_min : 1;
    $max = $duration_max > 0 ? $duration_max : 9999;
    $meta_query[] = array(
        'key'     => 'duration_day',
        'value'   => array( $min, $max ),
        'type'    => 'NUMERIC',
        'compare' => 'BETWEEN',
    );
}
if ( $price_min > 0 || $price_max > 0 ) {
    $min = $price_min > 0 ? $price_min : 0;
    $max = $price_max > 0 ? $price_max : 999999999;
    // We support several common price metas used in synced tours.
    $meta_query[] = array(
        'relation' => 'OR',
        array(
            'key'     => 'sale_price',
            'value'   => array( $min, $max ),
            'type'    => 'NUMERIC',
            'compare' => 'BETWEEN',
        ),
        array(
            'key'     => 'price',
            'value'   => array( $min, $max ),
            'type'    => 'NUMERIC',
            'compare' => 'BETWEEN',
        ),
        array(
            'key'     => 'adult_price',
            'value'   => array( $min, $max ),
            'type'    => 'NUMERIC',
            'compare' => 'BETWEEN',
        ),
        array(
            'key'     => 'base_price',
            'value'   => array( $min, $max ),
            'type'    => 'NUMERIC',
            'compare' => 'BETWEEN',
        ),
    );
}

// Departure date filter (synced travel dates table). We keep it optional: only apply if table exists.
if ( $depart_date !== '' && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $depart_date ) ) {
    global $wpdb;
    $dates_table = $wpdb->prefix . 'aj_travel_dates';
    $table_exists = (bool) $wpdb->get_var( $wpdb->prepare(
        "SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = %s",
        $dates_table
    ) );
    if ( $table_exists ) {
        $ids = $wpdb->get_col( $wpdb->prepare(
            "SELECT DISTINCT travel_id FROM {$dates_table} WHERE is_active = 1 AND date = %s",
            $depart_date
        ) );
        $ids = array_values( array_filter( array_map( 'absint', (array) $ids ) ) );
        $query_args['post__in'] = ! empty( $ids ) ? $ids : array( 0 );
    }
}

if ( ! empty( $meta_query ) ) {
    $query_args['meta_query'] = array_merge( array( 'relation' => 'AND' ), $meta_query );
}

$q = new WP_Query( $query_args );

$is_search = ! empty( $keyword );
?>

<div class="aj-home-wrap">
    <div id="aj-home" class="aj-home aj-voyages-page">
        <?php if ( ! $is_search ) : ?>
        <section class="aj-voyages-hero">
            <div class="aj-container">
                <div class="aj-voyages-hero__header">
                    <div>
                        <h1 class="aj-voyages-title"><?php esc_html_e( 'Tous les voyages', 'ajinsafro-traveler-home' ); ?></h1>
                        <p class="aj-voyages-subtitle"><?php esc_html_e( 'Trouvez votre prochaine destination et réservez rapidement.', 'ajinsafro-traveler-home' ); ?></p>
                    </div>
                </div>
                <div class="aj-voyages-search">
                    <?php include AJTH_DIR . 'parts/voyages-filters.php'; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <section class="aj-voyages-results">
            <div class="aj-container">
                <?php if ( $is_search ) : ?>
                    <div class="aj-voyages-search-header">
                        <h1 class="aj-voyages-title"><?php esc_html_e( 'Résultats de recherche', 'ajinsafro-traveler-home' ); ?></h1>
                        <?php if ( $keyword ) : ?>
                            <p class="aj-voyages-subtitle">
                                <?php printf( esc_html__( 'Recherche pour : %s', 'ajinsafro-traveler-home' ), '<strong>' . esc_html( $keyword ) . '</strong>' ); ?>
                            </p>
                        <?php endif; ?>
                        <div class="aj-voyages-search">
                            <?php include AJTH_DIR . 'parts/voyages-filters.php'; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="aj-voyages-results__head">
                    <h2 class="aj-section-title"><?php echo $is_search ? esc_html__( 'Voyages trouvés', 'ajinsafro-traveler-home' ) : esc_html__( 'Liste des voyages', 'ajinsafro-traveler-home' ); ?></h2>
                    <p class="aj-voyages-results__count">
                        <?php
                        printf(
                            esc_html( _n( '%d résultat', '%d résultats', intval( $q->found_posts ), 'ajinsafro-traveler-home' ) ),
                            intval( $q->found_posts )
                        );
                        ?>
                    </p>
                </div>

                <?php if ( $q->have_posts() ) : ?>
                    <div class="aj-voyages-grid">
                        <?php while ( $q->have_posts() ) : $q->the_post();
                            $price      = get_post_meta( get_the_ID(), 'price', true );
                            $sale_price = get_post_meta( get_the_ID(), 'sale_price', true );
                            $duration   = get_post_meta( get_the_ID(), 'duration', true );
                            $excerpt    = get_the_excerpt() ? wp_trim_words( get_the_excerpt(), 18, '…' ) : wp_trim_words( get_the_content(), 18, '…' );
                            $display_price = $sale_price ?: $price;
                        ?>
                            <article class="aj-voyages-grid__item">
                                <a href="<?php the_permalink(); ?>" class="aj-card2 aj-hover-glass">
                                    <div class="aj-card2__image">
                                        <?php if ( has_post_thumbnail() ) : ?>
                                            <?php the_post_thumbnail( 'medium_large', array( 'loading' => 'lazy' ) ); ?>
                                        <?php else : ?>
                                            <div class="aj-voyages-image-fallback"></div>
                                        <?php endif; ?>

                                        <?php if ( $duration ) : ?>
                                            <span class="aj-card2__badge aj-card2__badge--info">
                                                <i class="far fa-clock"></i> <?php echo esc_html( $duration ); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="aj-card2__body">
                                        <h3 class="aj-card2__title"><?php the_title(); ?></h3>
                                        <p class="aj-card2__desc"><?php echo esc_html( $excerpt ); ?></p>
                                        <div class="aj-card2__footer">
                                            <div>
                                                <?php if ( $display_price ) : ?>
                                                    <span class="aj-card2__price-label"><?php esc_html_e( 'à partir de', 'ajinsafro-traveler-home' ); ?></span>
                                                    <div class="aj-card2__price">
                                                        <?php echo esc_html( number_format( floatval( $display_price ), 0, ',', ' ' ) ); ?>
                                                        <span class="aj-card2__price-currency">DHS</span>
                                                    </div>
                                                    <span class="aj-card2__price-note"><?php esc_html_e( 'prix par personne', 'ajinsafro-traveler-home' ); ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <span class="aj-card2__cta"><?php esc_html_e( "VOIR L'OFFRE", 'ajinsafro-traveler-home' ); ?></span>
                                        </div>
                                    </div>
                                </a>
                            </article>
                        <?php endwhile; ?>
                    </div>

                    <?php
                    $pagination = paginate_links( array(
                        'base'      => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
                        'format'    => '?paged=%#%',
                        'current'   => $paged,
                        'total'     => max( 1, intval( $q->max_num_pages ) ),
                        'type'      => 'array',
                        'prev_text' => '«',
                        'next_text' => '»',
                    ) );

                    if ( ! empty( $pagination ) ) :
                    ?>
                        <nav class="aj-voyages-pagination" aria-label="Pagination voyages">
                            <?php foreach ( $pagination as $page_link ) : ?>
                                <?php echo wp_kses_post( $page_link ); ?>
                            <?php endforeach; ?>
                        </nav>
                    <?php endif; ?>

                <?php else : ?>
                    <div class="aj-voyages-empty">
                        <i class="fas fa-search" style="font-size: 48px; color: #cbd5e1; margin-bottom: 16px;"></i>
                        <p style="font-size: 18px; font-weight: 600; margin-bottom: 8px;">
                            <?php esc_html_e( 'Aucun voyage trouvé', 'ajinsafro-traveler-home' ); ?>
                        </p>
                        <p style="color: #94a3b8;">
                            <?php echo $keyword ? esc_html__( 'Essayez avec d\'autres mots-clés.', 'ajinsafro-traveler-home' ) : esc_html__( 'Aucun voyage n\'est disponible pour le moment.', 'ajinsafro-traveler-home' ); ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>

<?php
wp_reset_postdata();
get_footer();
