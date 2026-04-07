<?php
/**
 * Part: Voyages filters (CRUD-aligned)
 *
 * Filters st_tours by synced taxonomies/metas:
 * - Category (tours_cat)
 * - Destination (Traveler location metas -> location post type)
 * - Departure date (aj_travel_dates table if present)
 * - Duration (duration_day)
 * - Price range (sale_price / price / adult_price / base_price)
 * - Featured (is_featured)
 * - Tags (tour_tag)
 *
 * @package AjinsafroTravelerHome
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$voyages_page_url = function_exists( 'ajth_get_voyages_page_url' )
    ? ajth_get_voyages_page_url()
    : home_url( '/?post_type=st_tours' );

$search_text   = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
$location_name = isset( $_GET['location_name'] ) ? sanitize_text_field( wp_unslash( $_GET['location_name'] ) ) : '';

$category_slug = isset( $_GET['cat'] ) ? sanitize_text_field( wp_unslash( $_GET['cat'] ) ) : '';
$tag_slug      = isset( $_GET['tag'] ) ? sanitize_text_field( wp_unslash( $_GET['tag'] ) ) : '';
$location_id   = isset( $_GET['location_id'] ) ? absint( $_GET['location_id'] ) : 0;
$featured      = isset( $_GET['featured'] ) && (string) $_GET['featured'] === '1';

$depart_date   = isset( $_GET['depart_date'] ) ? sanitize_text_field( wp_unslash( $_GET['depart_date'] ) ) : '';
$duration_min  = isset( $_GET['duration_min'] ) ? absint( $_GET['duration_min'] ) : 0;
$duration_max  = isset( $_GET['duration_max'] ) ? absint( $_GET['duration_max'] ) : 0;
$price_min     = isset( $_GET['price_min'] ) ? absint( $_GET['price_min'] ) : 0;
$price_max     = isset( $_GET['price_max'] ) ? absint( $_GET['price_max'] ) : 0;

$cats = get_terms([
    'taxonomy'   => 'tours_cat',
    'hide_empty' => true,
]);
$tags = get_terms([
    'taxonomy'   => 'tour_tag',
    'hide_empty' => true,
]);

// Build destination options from tours meta (st_location_id/location_id/id_location/multi_location) -> location post type.
global $wpdb;
$destinations = [];
try {
    $postmeta = $wpdb->postmeta;
    $posts = $wpdb->posts;
    $rows = $wpdb->get_col("
        SELECT DISTINCT CAST(pm.meta_value AS UNSIGNED) AS loc_id
        FROM {$postmeta} pm
        INNER JOIN {$posts} p ON p.ID = pm.post_id
        WHERE p.post_type = 'st_tours'
          AND p.post_status = 'publish'
          AND pm.meta_key IN ('st_location_id','location_id','id_location')
          AND pm.meta_value REGEXP '^[0-9]+$'
          AND CAST(pm.meta_value AS UNSIGNED) > 0
        ORDER BY loc_id ASC
        LIMIT 200
    ");
    $ids = array_values(array_filter(array_map('absint', (array) $rows)));
    if (!empty($ids)) {
        $loc_posts = get_posts([
            'post_type'      => 'location',
            'post__in'       => $ids,
            'orderby'        => 'post__in',
            'posts_per_page' => count($ids),
            'post_status'    => ['publish','private','draft','pending','future'],
        ]);
        $by_id = [];
        foreach ($loc_posts as $lp) { $by_id[(int)$lp->ID] = $lp; }
        foreach ($ids as $id) {
            if (empty($by_id[$id])) continue;
            $p = $by_id[$id];
            // Build "Country > City" style label using post parents.
            $parts = [];
            $cur = $p;
            $depth = 0;
            while ($cur && $depth < 10) {
                $parts[] = (string) $cur->post_title;
                $pid = (int) $cur->post_parent;
                $cur = $pid > 0 ? get_post($pid) : null;
                $depth++;
            }
            $parts = array_reverse(array_filter(array_map('trim', $parts)));
            $destinations[] = ['id' => $id, 'label' => implode(' > ', $parts)];
        }
    }
} catch (\Throwable $e) {
    $destinations = [];
}
?>

<form method="get" action="<?php echo esc_url( $voyages_page_url ); ?>" class="aj-search-card" style="padding:16px;">
    <input type="hidden" name="post_type" value="st_tours">

    <div class="aj-search-form__row" style="display:grid;grid-template-columns:1.2fr 1fr 1fr 1fr;gap:12px;align-items:end;">
        <div class="aj-search-field" style="min-width:0;">
            <div class="aj-search-field__content">
                <span class="aj-search-field__label"><?php esc_html_e('Recherche', 'ajinsafro-traveler-home'); ?></span>
                <input type="text" name="s" class="aj-search-field__input" value="<?php echo esc_attr($search_text); ?>" placeholder="<?php esc_attr_e('Nom, code, mot-clé…', 'ajinsafro-traveler-home'); ?>">
            </div>
        </div>

        <div class="aj-search-field">
            <div class="aj-search-field__content">
                <span class="aj-search-field__label"><?php esc_html_e('Catégorie', 'ajinsafro-traveler-home'); ?></span>
                <select name="cat" class="aj-search-field__input">
                    <option value=""><?php esc_html_e('Toutes', 'ajinsafro-traveler-home'); ?></option>
                    <?php foreach ((array)$cats as $c): ?>
                        <option value="<?php echo esc_attr($c->slug); ?>"<?php selected($category_slug, $c->slug); ?>>
                            <?php echo esc_html($c->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="aj-search-field">
            <div class="aj-search-field__content">
                <span class="aj-search-field__label"><?php esc_html_e('Destination', 'ajinsafro-traveler-home'); ?></span>
                <select name="location_id" class="aj-search-field__input">
                    <option value=""><?php esc_html_e('Toutes', 'ajinsafro-traveler-home'); ?></option>
                    <?php foreach ((array)$destinations as $d): ?>
                        <option value="<?php echo (int) $d['id']; ?>"<?php selected($location_id, (int)$d['id']); ?>>
                            <?php echo esc_html($d['label']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (empty($destinations)): ?>
                    <div class="aj-search-field__hint" style="font-size:12px;color:#64748b;margin-top:6px;">
                        <?php esc_html_e("Destinations indisponibles (aucune donnée 'location' associée).", 'ajinsafro-traveler-home'); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="aj-search-field">
            <div class="aj-search-field__content">
                <span class="aj-search-field__label"><?php esc_html_e('Date de départ', 'ajinsafro-traveler-home'); ?></span>
                <input type="date" name="depart_date" class="aj-search-field__input" value="<?php echo esc_attr($depart_date); ?>">
            </div>
        </div>
    </div>

    <div class="aj-search-form__row" style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:12px;align-items:end;margin-top:12px;">
        <div class="aj-search-field">
            <div class="aj-search-field__content">
                <span class="aj-search-field__label"><?php esc_html_e('Durée (jours)', 'ajinsafro-traveler-home'); ?></span>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                    <input type="number" min="0" name="duration_min" class="aj-search-field__input" value="<?php echo esc_attr($duration_min); ?>" placeholder="<?php esc_attr_e('Min', 'ajinsafro-traveler-home'); ?>">
                    <input type="number" min="0" name="duration_max" class="aj-search-field__input" value="<?php echo esc_attr($duration_max); ?>" placeholder="<?php esc_attr_e('Max', 'ajinsafro-traveler-home'); ?>">
                </div>
            </div>
        </div>

        <div class="aj-search-field">
            <div class="aj-search-field__content">
                <span class="aj-search-field__label"><?php esc_html_e('Prix (DHS)', 'ajinsafro-traveler-home'); ?></span>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                    <input type="number" min="0" name="price_min" class="aj-search-field__input" value="<?php echo esc_attr($price_min); ?>" placeholder="<?php esc_attr_e('Min', 'ajinsafro-traveler-home'); ?>">
                    <input type="number" min="0" name="price_max" class="aj-search-field__input" value="<?php echo esc_attr($price_max); ?>" placeholder="<?php esc_attr_e('Max', 'ajinsafro-traveler-home'); ?>">
                </div>
            </div>
        </div>

        <div class="aj-search-field">
            <div class="aj-search-field__content">
                <span class="aj-search-field__label"><?php esc_html_e('Tag', 'ajinsafro-traveler-home'); ?></span>
                <select name="tag" class="aj-search-field__input">
                    <option value=""><?php esc_html_e('Tous', 'ajinsafro-traveler-home'); ?></option>
                    <?php foreach ((array)$tags as $t): ?>
                        <option value="<?php echo esc_attr($t->slug); ?>"<?php selected($tag_slug, $t->slug); ?>>
                            <?php echo esc_html($t->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="aj-search-field">
            <div class="aj-search-field__content">
                <label style="display:flex;align-items:center;gap:10px;margin-top:22px;">
                    <input type="checkbox" name="featured" value="1"<?php checked($featured); ?>>
                    <span style="font-weight:600;"><?php esc_html_e('Mise en avant', 'ajinsafro-traveler-home'); ?></span>
                </label>
            </div>
        </div>
    </div>

    <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:14px;">
        <a class="aj-search-submit__btn" href="<?php echo esc_url( $voyages_page_url ); ?>" style="text-decoration:none;display:inline-flex;align-items:center;gap:8px;padding:10px 14px;border-radius:12px;background:#e2e8f0;color:#0f172a;font-weight:700;">
            <?php esc_html_e('Réinitialiser', 'ajinsafro-traveler-home'); ?>
        </a>
        <button type="submit" class="aj-search-submit__btn" style="border-radius:12px;padding:10px 14px;">
            <i class="fas fa-search"></i> <?php esc_html_e('Filtrer', 'ajinsafro-traveler-home'); ?>
        </button>
    </div>
</form>

