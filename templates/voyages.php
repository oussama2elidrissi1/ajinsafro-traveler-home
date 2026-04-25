<?php
/**
 * Voyages Page Template
 *
 * Professional catalog experience for st_tours with shareable GET filters.
 */
if (! defined('ABSPATH')) {
    exit;
}

get_header();

$settings = ajth_get_settings();
$paged = max(1, absint(get_query_var('paged')), absint(get_query_var('page')), isset($_GET['paged']) ? absint($_GET['paged']) : 0);
$per_page = 12;
$today = current_time('Y-m-d');
$voyages_page_url = function_exists('ajth_get_voyages_page_url')
    ? ajth_get_voyages_page_url()
    : home_url('/voyages/');

$get_text = static function (string $key, string $default = ''): string {
    if (! isset($_GET[$key])) {
        return $default;
    }

    $value = wp_unslash($_GET[$key]);
    if (is_array($value)) {
        return $default;
    }

    return sanitize_text_field((string) $value);
};

$get_int = static function (string $key): int {
    if (! isset($_GET[$key])) {
        return 0;
    }

    return absint($_GET[$key]);
};

$get_bool = static function (string $key): bool {
    return isset($_GET[$key]) && (string) wp_unslash($_GET[$key]) === '1';
};

$search_text = $get_text('s');
$location_name = $get_text('location_name');
$keyword = $location_name !== '' ? $location_name : $search_text;
$category_slug = $get_text('cat');
$tag_slug = $get_text('tag');
$location_id = $get_int('location_id');
$dest = $get_text('dest');
$depart_date = $get_text('depart_date');
$duration_min = $get_int('duration_min');
$duration_max = $get_int('duration_max');
$price_min = $get_int('price_min');
$price_max = $get_int('price_max');
$guests_min = $get_int('guests_min');
$featured_only = $get_bool('featured');
$promo_only = $get_bool('promo_only');
$available_only = $get_bool('available_only');
$min_rating = (float) $get_text('min_rating');
$sort = $get_text('sort', $get_text('catalog_orderby', 'recommended'));
$allowed_sorts = ['recommended', 'price_asc', 'price_desc', 'rating_desc', 'popular', 'promo_first', 'duration_asc', 'duration_desc', 'departure_soonest', 'newest', 'title_asc'];
if (! in_array($sort, $allowed_sorts, true)) {
    $sort = 'recommended';
}
if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $depart_date)) {
    $depart_date = '';
}

$current_filters = array_filter([
    's' => $search_text,
    'location_name' => $location_name,
    'cat' => $category_slug,
    'tag' => $tag_slug,
    'location_id' => $location_id > 0 ? (string) $location_id : '',
    'dest' => $dest,
    'depart_date' => $depart_date,
    'duration_min' => $duration_min > 0 ? (string) $duration_min : '',
    'duration_max' => $duration_max > 0 ? (string) $duration_max : '',
    'price_min' => $price_min > 0 ? (string) $price_min : '',
    'price_max' => $price_max > 0 ? (string) $price_max : '',
    'guests_min' => $guests_min > 0 ? (string) $guests_min : '',
    'min_rating' => $min_rating > 0 ? rtrim(rtrim(number_format($min_rating, 1, '.', ''), '0'), '.') : '',
    'featured' => $featured_only ? '1' : '',
    'promo_only' => $promo_only ? '1' : '',
    'available_only' => $available_only ? '1' : '',
    'sort' => $sort,
], static fn ($value) => $value !== '' && $value !== null);

$build_url = static function (array $overrides = [], array $remove = []) use ($current_filters, $voyages_page_url): string {
    $args = $current_filters;

    foreach ($remove as $key) {
        unset($args[$key]);
    }

    foreach ($overrides as $key => $value) {
        if ($value === null || $value === '') {
            unset($args[$key]);
            continue;
        }
        $args[$key] = (string) $value;
    }

    return empty($args) ? $voyages_page_url : add_query_arg($args, $voyages_page_url);
};

$query_args = [
    'post_type' => 'st_tours',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'fields' => 'ids',
    'no_found_rows' => true,
    'ignore_sticky_posts' => true,
    'orderby' => 'date',
    'order' => 'DESC',
];

if ($keyword !== '') {
    $query_args['s'] = $keyword;
}

$tax_query = [];
if ($category_slug !== '') {
    $tax_query[] = [
        'taxonomy' => 'tours_cat',
        'field' => 'slug',
        'terms' => [$category_slug],
    ];
}
if ($tag_slug !== '') {
    $tax_query[] = [
        'taxonomy' => 'tour_tag',
        'field' => 'slug',
        'terms' => [$tag_slug],
    ];
}
if (! empty($tax_query)) {
    $query_args['tax_query'] = array_merge(['relation' => 'AND'], $tax_query);
}

$meta_query = [];
if ($featured_only) {
    $meta_query[] = [
        'key' => 'is_featured',
        'value' => 'on',
        'compare' => '=',
    ];
}
if ($location_id > 0) {
    $meta_query[] = [
        'relation' => 'OR',
        ['key' => 'st_location_id', 'value' => (string) $location_id, 'compare' => '='],
        ['key' => 'location_id', 'value' => (string) $location_id, 'compare' => '='],
        ['key' => 'id_location', 'value' => (string) $location_id, 'compare' => '='],
        ['key' => 'multi_location', 'value' => '_' . $location_id . '_', 'compare' => 'LIKE'],
        ['key' => 'multi_location', 'value' => (string) $location_id, 'compare' => 'LIKE'],
    ];
}
if ($dest !== '') {
    $meta_query[] = [
        'relation' => 'OR',
        ['key' => 'address', 'value' => $dest, 'compare' => 'LIKE'],
        ['key' => 'aj_catalog_destination', 'value' => $dest, 'compare' => 'LIKE'],
    ];
}
if ($duration_min > 0 || $duration_max > 0) {
    $meta_query[] = [
        'key' => 'duration_day',
        'value' => [max(1, $duration_min), $duration_max > 0 ? $duration_max : 365],
        'type' => 'NUMERIC',
        'compare' => 'BETWEEN',
    ];
}
if (! empty($meta_query)) {
    $query_args['meta_query'] = array_merge(['relation' => 'AND'], $meta_query);
}

$base_query = new WP_Query($query_args);
$post_ids = array_values(array_filter(array_map('absint', (array) $base_query->posts)));

$departure_index = [];
$available_departure_dates = [];
if (! empty($post_ids)) {
    global $wpdb;

    $dates_table = $wpdb->prefix . 'aj_travel_dates';
    $table_exists = (bool) $wpdb->get_var($wpdb->prepare(
        'SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = %s',
        $dates_table
    ));

    if ($table_exists) {
        $column_rows = $wpdb->get_results("SHOW COLUMNS FROM {$dates_table}", ARRAY_A);
        $date_columns = [];
        foreach ((array) $column_rows as $column_row) {
            if (! empty($column_row['Field'])) {
                $date_columns[] = (string) $column_row['Field'];
            }
        }

        $select_fields = ['travel_id', 'date'];
        foreach (['is_active', 'stock', 'specific_price'] as $optional_column) {
            if (in_array($optional_column, $date_columns, true)) {
                $select_fields[] = $optional_column;
            }
        }

        $ids_sql = implode(',', array_map('intval', $post_ids));
        if ($ids_sql !== '') {
            $date_rows = $wpdb->get_results(
                "SELECT " . implode(', ', $select_fields) . " FROM {$dates_table} WHERE travel_id IN ({$ids_sql}) ORDER BY date ASC",
                ARRAY_A
            );

            foreach ((array) $date_rows as $date_row) {
                $travel_id = isset($date_row['travel_id']) ? (int) $date_row['travel_id'] : 0;
                $date_value = isset($date_row['date']) ? trim((string) $date_row['date']) : '';
                if ($travel_id <= 0 || $date_value === '') {
                    continue;
                }

                $is_active = ! array_key_exists('is_active', $date_row) || (int) $date_row['is_active'] === 1;
                $stock_value = array_key_exists('stock', $date_row) && $date_row['stock'] !== null && $date_row['stock'] !== ''
                    ? (int) $date_row['stock']
                    : null;
                $specific_price = array_key_exists('specific_price', $date_row) && $date_row['specific_price'] !== null && $date_row['specific_price'] !== ''
                    ? (float) $date_row['specific_price']
                    : null;

                $normalized_row = [
                    'date' => $date_value,
                    'is_active' => $is_active,
                    'stock' => $stock_value,
                    'specific_price' => $specific_price,
                ];

                $departure_index[$travel_id][] = $normalized_row;
                if ($is_active) {
                    $available_departure_dates[$date_value] = $date_value;
                }
            }
        }
    }

    update_meta_cache('post', $post_ids);
}

$catalog_themes = function_exists('ajth_get_catalog_tour_category_terms_ordered')
    ? ajth_get_catalog_tour_category_terms_ordered()
    : [];
$catalog_tags = get_terms([
    'taxonomy' => 'tour_tag',
    'hide_empty' => true,
]);

$destinations = [];
if (! empty($post_ids)) {
    global $wpdb;
    $ids_sql = implode(',', array_map('intval', $post_ids));
    if ($ids_sql !== '') {
        $rows = $wpdb->get_col(
            "SELECT DISTINCT TRIM(pm.meta_value) AS dest_label
             FROM {$wpdb->postmeta} pm
             WHERE pm.post_id IN ({$ids_sql})
               AND pm.meta_key IN ('address', 'aj_catalog_destination')
               AND pm.meta_value IS NOT NULL
               AND TRIM(pm.meta_value) <> ''
             ORDER BY dest_label ASC"
        );
        foreach ((array) $rows as $label) {
            $label = is_string($label) ? trim($label) : '';
            if ($label === '') {
                continue;
            }
            $destinations[$label] = [
                'value' => $label,
                'label' => $label,
            ];
        }
    }
}
$destinations = array_values($destinations);
sort($available_departure_dates);
$upcoming_departure_dates = array_values(array_filter(
    $available_departure_dates,
    static fn (string $date_value): bool => $date_value >= $today
));

$format_price = static function (?float $price): string {
    if ($price === null || $price <= 0) {
        return '';
    }

    return number_format($price, 0, ',', ' ');
};

$format_date = static function (string $date_value): string {
    $timestamp = strtotime($date_value);
    if (! $timestamp) {
        return $date_value;
    }

    return date_i18n('d M Y', $timestamp);
};

$build_duration_label = static function (int $days, string $duration_text): string {
    if ($days > 0) {
        return sprintf('%d jours / %d nuits', $days, max(0, $days - 1));
    }
    if ($duration_text !== '') {
        return $duration_text;
    }

    return 'Programme a confirmer';
};

$cards = [];
foreach ($post_ids as $post_id) {
    $meta = get_post_meta($post_id);
    $meta_value = static function (array $source, string $key): string {
        if (! isset($source[$key][0])) {
            return '';
        }
        return trim((string) $source[$key][0]);
    };
    $first_meta_value = static function (array $source, array $keys) use ($meta_value): string {
        foreach ($keys as $key) {
            $value = $meta_value($source, $key);
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    };

    $post_date = get_post_field('post_date', $post_id);
    $title = get_the_title($post_id);
    $address = $meta_value($meta, 'address');
    $catalog_destination = $meta_value($meta, 'aj_catalog_destination');
    $destination_label = $catalog_destination !== '' ? $catalog_destination : $address;
    $duration_days = (int) $meta_value($meta, 'duration_day');
    $duration_text = $meta_value($meta, 'duration');
    $max_people = (int) $meta_value($meta, 'max_people');
    $departure_city = $first_meta_value($meta, ['departure_city', 'depart_city', 'start_city', 'origin_city', 'flight_departure_city', 'aj_departure_city', 'aj_tour_departure_city']);
    $review_score = (float) $meta_value($meta, 'review_score');
    $rate_review = (float) $meta_value($meta, 'rate_review');
    $rating_score = max($review_score, $rate_review);
    $comment_count = (int) get_comments_number($post_id);
    $price_regular_candidates = array_filter([
        (float) $meta_value($meta, 'adult_price'),
        (float) $meta_value($meta, 'price'),
        (float) $meta_value($meta, 'base_price'),
    ], static fn ($value) => $value > 0);
    $sale_price = (float) $meta_value($meta, 'sale_price');
    $regular_price = ! empty($price_regular_candidates) ? min($price_regular_candidates) : 0.0;

    $date_rows = $departure_index[$post_id] ?? [];
    $active_dates = array_values(array_filter($date_rows, static fn ($row) => ! empty($row['is_active'])));
    $matching_departure = null;
    $next_departure = null;
    $min_specific_price = null;
    $has_available_departure = false;

    foreach ($active_dates as $date_row) {
        if ($depart_date !== '' && $date_row['date'] === $depart_date) {
            $matching_departure = $date_row;
        }

        if ($date_row['specific_price'] !== null && $date_row['specific_price'] > 0) {
            $min_specific_price = $min_specific_price === null
                ? (float) $date_row['specific_price']
                : min($min_specific_price, (float) $date_row['specific_price']);
        }

        $stock_known = $date_row['stock'] !== null;
        $is_available_row = ! $stock_known || (int) $date_row['stock'] > 0;
        if ($is_available_row) {
            $has_available_departure = true;
        }

        if ($next_departure === null && $date_row['date'] >= $today) {
            $next_departure = $date_row;
        }
    }

    if ($matching_departure === null && $depart_date !== '') {
        continue;
    }

    if ($next_departure === null && ! empty($active_dates)) {
        $next_departure = $active_dates[0];
    }

    $price_from = null;
    if ($matching_departure !== null && $matching_departure['specific_price'] !== null && $matching_departure['specific_price'] > 0) {
        $price_from = (float) $matching_departure['specific_price'];
    } elseif ($sale_price > 0) {
        $price_from = $sale_price;
    } elseif ($min_specific_price !== null && $min_specific_price > 0) {
        $price_from = $min_specific_price;
    } elseif ($regular_price > 0) {
        $price_from = $regular_price;
    }

    $price_reference = $regular_price > 0 ? $regular_price : null;
    if ($price_reference !== null && $price_from !== null && $price_reference <= $price_from) {
        $price_reference = null;
    }

    $is_promo = $price_reference !== null && $price_from !== null && $price_reference > $price_from;
    $theme_terms = wp_get_post_terms($post_id, 'tours_cat', ['fields' => 'all']);
    $tag_terms = wp_get_post_terms($post_id, 'tour_tag', ['fields' => 'all']);
    $theme_names = [];
    foreach ((array) $theme_terms as $term) {
        if ($term instanceof WP_Term) {
            $theme_names[] = $term->name;
        }
    }
    $tag_names = [];
    foreach ((array) $tag_terms as $term) {
        if ($term instanceof WP_Term) {
            $tag_names[] = $term->name;
        }
    }
    $discovery_pool = strtolower(implode(' ', array_filter(array_merge($theme_names, $tag_names, [$title]))));
    $service_chips = [];
    $service_keywords = [
        'Vol inclus' => ['vol', 'flight'],
        'Hotel inclus' => ['hotel', 'hebergement', 'riad', 'resort'],
        'Transfert' => ['transfert', 'transfer', 'navette'],
        'Guide' => ['guide', 'accompagne'],
        'Visa' => ['visa'],
        'Activites' => ['activite', 'excursion', 'visite'],
        'Low cost' => ['low cost', 'economique', 'budget'],
        'Premium' => ['premium', 'luxe', 'vip'],
        'Famille' => ['famille', 'family'],
        'Couple' => ['couple', 'honeymoon'],
        'Groupe' => ['groupe', 'group'],
        'Omra' => ['omra'],
        'Hajj' => ['hajj'],
    ];
    foreach ($service_keywords as $service_label => $keywords) {
        foreach ($keywords as $keyword_match) {
            if ($keyword_match !== '' && strpos($discovery_pool, $keyword_match) !== false) {
                $service_chips[] = $service_label;
                break;
            }
        }
    }
    $service_chips = array_values(array_slice(array_unique($service_chips), 0, 4));

    if ($price_min > 0 && ($price_from === null || $price_from < $price_min)) {
        continue;
    }
    if ($price_max > 0 && ($price_from === null || $price_from > $price_max)) {
        continue;
    }
    if ($guests_min > 0 && $max_people > 0 && $max_people < $guests_min) {
        continue;
    }
    if ($min_rating > 0 && $rating_score < $min_rating) {
        continue;
    }
    if ($promo_only && ! $is_promo) {
        continue;
    }
    if ($available_only && ! $has_available_departure) {
        continue;
    }

    $stock_badge = null;
    $stock_source = $matching_departure ?? $next_departure;
    if ($stock_source !== null) {
        if ($stock_source['stock'] === null) {
            $stock_badge = ['label' => 'Depart sur demande', 'tone' => 'neutral'];
        } elseif ((int) $stock_source['stock'] <= 0) {
            $stock_badge = ['label' => 'Complet', 'tone' => 'muted'];
        } elseif ((int) $stock_source['stock'] <= 5) {
            $stock_badge = ['label' => 'Places limitees', 'tone' => 'warning'];
        } else {
            $stock_badge = ['label' => 'Disponible', 'tone' => 'success'];
        }
    }

    $cards[] = [
        'id' => $post_id,
        'title' => $title,
        'permalink' => get_permalink($post_id),
        'excerpt' => get_the_excerpt($post_id) !== ''
            ? wp_trim_words(get_the_excerpt($post_id), 22, '...')
            : wp_trim_words(wp_strip_all_tags(get_post_field('post_content', $post_id)), 22, '...'),
        'destination' => $destination_label,
        'duration_days' => $duration_days,
        'duration_label' => $build_duration_label($duration_days, $duration_text),
        'departure_city' => $departure_city,
        'max_people' => $max_people,
        'rating' => $rating_score,
        'reviews' => $comment_count,
        'price_from' => $price_from,
        'price_from_label' => $format_price($price_from),
        'price_reference' => $price_reference,
        'price_reference_label' => $format_price($price_reference),
        'is_promo' => $is_promo,
        'is_featured' => strtolower($meta_value($meta, 'is_featured')) === 'on',
        'next_departure' => $next_departure,
        'next_departure_label' => $next_departure ? $format_date((string) $next_departure['date']) : '',
        'has_available_departure' => $has_available_departure,
        'stock_badge' => $stock_badge,
        'themes' => array_slice($theme_names, 0, 2),
        'tags' => array_slice($tag_names, 0, 3),
        'service_chips' => $service_chips,
        'post_timestamp' => $post_date ? strtotime($post_date) : 0,
        'departure_timestamp' => $next_departure ? strtotime((string) $next_departure['date']) : 0,
    ];
}

usort($cards, static function (array $left, array $right) use ($sort): int {
    $compare_numbers = static function ($a, $b, bool $asc = true): int {
        $a = $a ?? 0;
        $b = $b ?? 0;
        return $asc ? ($a <=> $b) : ($b <=> $a);
    };

    switch ($sort) {
        case 'price_asc':
            return $compare_numbers($left['price_from'] ?? PHP_INT_MAX, $right['price_from'] ?? PHP_INT_MAX, true)
                ?: strcasecmp($left['title'], $right['title']);

        case 'price_desc':
            return $compare_numbers($left['price_from'], $right['price_from'], false)
                ?: strcasecmp($left['title'], $right['title']);

        case 'rating_desc':
            return $compare_numbers($left['rating'], $right['rating'], false)
                ?: $compare_numbers($left['reviews'], $right['reviews'], false);

        case 'popular':
            return $compare_numbers($left['reviews'], $right['reviews'], false)
                ?: $compare_numbers($left['rating'], $right['rating'], false);

        case 'promo_first':
            return $compare_numbers((int) $left['is_promo'], (int) $right['is_promo'], false)
                ?: $compare_numbers($left['price_from'] ?? PHP_INT_MAX, $right['price_from'] ?? PHP_INT_MAX, true);

        case 'duration_asc':
            return $compare_numbers($left['duration_days'] ?: PHP_INT_MAX, $right['duration_days'] ?: PHP_INT_MAX, true)
                ?: $compare_numbers($left['price_from'] ?? PHP_INT_MAX, $right['price_from'] ?? PHP_INT_MAX, true);

        case 'duration_desc':
            return $compare_numbers($left['duration_days'], $right['duration_days'], false)
                ?: $compare_numbers($left['price_from'], $right['price_from'], true);

        case 'departure_soonest':
            $left_departure = ! empty($left['departure_timestamp']) ? (int) $left['departure_timestamp'] : PHP_INT_MAX;
            $right_departure = ! empty($right['departure_timestamp']) ? (int) $right['departure_timestamp'] : PHP_INT_MAX;
            return $compare_numbers($left_departure, $right_departure, true)
                ?: $compare_numbers($left['price_from'], $right['price_from'], true);

        case 'newest':
            return $compare_numbers($left['post_timestamp'], $right['post_timestamp'], false)
                ?: strcasecmp($left['title'], $right['title']);

        case 'title_asc':
            return strcasecmp($left['title'], $right['title']);

        case 'recommended':
        default:
            return $compare_numbers((int) $left['is_featured'], (int) $right['is_featured'], false)
                ?: $compare_numbers((int) $left['has_available_departure'], (int) $right['has_available_departure'], false)
                ?: $compare_numbers((int) $left['is_promo'], (int) $right['is_promo'], false)
                ?: $compare_numbers($left['rating'], $right['rating'], false)
                ?: $compare_numbers($left['price_from'] ?? PHP_INT_MAX, $right['price_from'] ?? PHP_INT_MAX, true)
                ?: strcasecmp($left['title'], $right['title']);
    }
});

$total_results = count($cards);
$total_pages = max(1, (int) ceil($total_results / $per_page));
$paged = min($paged, $total_pages);
$cards_page = array_slice($cards, ($paged - 1) * $per_page, $per_page);
$min_price_found = null;
foreach ($cards as $card) {
    if (($card['price_from'] ?? 0) > 0) {
        $min_price_found = $min_price_found === null ? (float) $card['price_from'] : min($min_price_found, (float) $card['price_from']);
    }
}

$results_target = $dest !== '' ? $dest : ($keyword !== '' ? $keyword : 'votre selection');
$results_headline = sprintf('%d voyages trouves', $total_results);
if ($results_target !== 'votre selection') {
    $results_headline .= ' pour ' . $results_target;
}

$active_filters = [];
if ($keyword !== '') {
    $active_filters[] = ['label' => 'Recherche: ' . $keyword, 'url' => $build_url(['s' => '', 'location_name' => ''])];
}
if ($category_slug !== '') {
    foreach ((array) $catalog_themes as $term) {
        if ($term instanceof WP_Term && $term->slug === $category_slug) {
            $active_filters[] = ['label' => 'Theme: ' . $term->name, 'url' => $build_url(['cat' => ''])];
            break;
        }
    }
}
if ($tag_slug !== '') {
    foreach ((array) $catalog_tags as $term) {
        if ($term instanceof WP_Term && $term->slug === $tag_slug) {
            $active_filters[] = ['label' => 'Tag: ' . $term->name, 'url' => $build_url(['tag' => ''])];
            break;
        }
    }
}
if ($dest !== '') {
    $active_filters[] = ['label' => 'Destination: ' . $dest, 'url' => $build_url(['dest' => ''])];
}
if ($depart_date !== '') {
    $active_filters[] = ['label' => 'Depart: ' . $format_date($depart_date), 'url' => $build_url(['depart_date' => ''])];
}
if ($price_min > 0 || $price_max > 0) {
    $budget_label = 'Budget';
    if ($price_min > 0 && $price_max > 0) {
        $budget_label .= ': ' . number_format($price_min, 0, ',', ' ') . ' - ' . number_format($price_max, 0, ',', ' ') . ' DH';
    } elseif ($price_min > 0) {
        $budget_label .= ': min ' . number_format($price_min, 0, ',', ' ') . ' DH';
    } else {
        $budget_label .= ': max ' . number_format($price_max, 0, ',', ' ') . ' DH';
    }
    $active_filters[] = ['label' => $budget_label, 'url' => $build_url(['price_min' => '', 'price_max' => ''])];
}
if ($duration_min > 0 || $duration_max > 0) {
    $duration_filter_label = 'Duree';
    if ($duration_min > 0 && $duration_max > 0) {
        $duration_filter_label .= ': ' . $duration_min . '-' . $duration_max . ' j';
    } elseif ($duration_min > 0) {
        $duration_filter_label .= ': min ' . $duration_min . ' j';
    } else {
        $duration_filter_label .= ': max ' . $duration_max . ' j';
    }
    $active_filters[] = ['label' => $duration_filter_label, 'url' => $build_url(['duration_min' => '', 'duration_max' => ''])];
}
if ($guests_min > 0) {
    $active_filters[] = ['label' => 'Voyageurs: ' . $guests_min . '+', 'url' => $build_url(['guests_min' => ''])];
}
if ($min_rating > 0) {
    $active_filters[] = ['label' => 'Note: ' . $min_rating . '+', 'url' => $build_url(['min_rating' => ''])];
}
if ($featured_only) {
    $active_filters[] = ['label' => 'Recommande Ajinsafro', 'url' => $build_url(['featured' => ''])];
}
if ($promo_only) {
    $active_filters[] = ['label' => 'Promotions uniquement', 'url' => $build_url(['promo_only' => ''])];
}
if ($available_only) {
    $active_filters[] = ['label' => 'Disponibilite immediate', 'url' => $build_url(['available_only' => ''])];
}

$sort_options = [
    'recommended' => 'Recommandes',
    'price_asc' => 'Prix croissant',
    'price_desc' => 'Prix decroissant',
    'rating_desc' => 'Meilleures notes',
    'popular' => 'Plus populaires',
    'promo_first' => 'Promotions d abord',
    'duration_asc' => 'Duree courte',
    'duration_desc' => 'Duree longue',
    'departure_soonest' => 'Departs les plus proches',
    'newest' => 'Nouveautes',
    'title_asc' => 'Nom A-Z',
];

$rating_label = static function (float $rating): string {
    if ($rating >= 9) {
        return 'Exceptionnel';
    }
    if ($rating >= 8) {
        return 'Excellent';
    }
    if ($rating >= 7) {
        return 'Tres bien';
    }

    return 'Correct';
};
?>

<div class="aj-home-wrap">
    <div id="aj-home" class="aj-home aj-voyages-page aj-voyages-booking-page">
        <?php ajth_render_site_header($settings); ?>

        <div class="aj-voyages-booking" id="aj-voyages-booking">
            <section class="hero">
                <div class="container">
                    <h1 class="hero-title">Voyages, sejours et circuits</h1>
                    <p class="hero-subtitle">Comparez nos offres, choisissez votre destination et reservez votre prochain voyage avec Ajinsafro.</p>

                    <form class="search-panel" method="get" action="<?php echo esc_url($voyages_page_url); ?>">
                        <input type="hidden" name="sort" value="<?php echo esc_attr($sort); ?>">
                        <div class="search-field">
                            <label for="ajvb-destination">Destination</label>
                            <input id="ajvb-destination" name="dest" type="text" value="<?php echo esc_attr($dest !== '' ? $dest : $keyword); ?>" placeholder="Marrakech, Istanbul, Omra...">
                        </div>
                        <div class="search-field">
                            <label for="ajvb-depart-date">Date de depart</label>
                            <input id="ajvb-depart-date" name="depart_date" type="date" value="<?php echo esc_attr($depart_date); ?>">
                        </div>
                        <div class="search-field">
                            <label for="ajvb-travelers">Voyageurs</label>
                            <input id="ajvb-travelers" name="guests_min" type="number" min="1" value="<?php echo esc_attr($guests_min > 0 ? (string) $guests_min : ''); ?>" placeholder="2">
                        </div>
                        <div class="search-field">
                            <label for="ajvb-budget">Budget max</label>
                            <input id="ajvb-budget" name="price_max" type="number" min="0" value="<?php echo esc_attr($price_max > 0 ? (string) $price_max : ''); ?>" placeholder="12000">
                        </div>
                        <button class="search-btn" type="submit">Rechercher</button>
                    </form>
                </div>
            </section>

            <main class="container main-grid">
                <aside class="filters" aria-label="Filtres voyages">
                    <div class="map-card">
                        <button type="button">Conseils Ajinsafro</button>
                    </div>
                    <div class="filter-title">
                        <h2>Filtrer par</h2>
                        <a class="clear-link" href="<?php echo esc_url($voyages_page_url); ?>">Tout effacer</a>
                    </div>
                    <?php include AJTH_DIR . 'parts/voyages-filters.php'; ?>
                </aside>

                <section class="results">
                    <div class="results-head">
                        <div class="results-topline">
                            <div>
                                <h2><?php echo esc_html($results_headline); ?></h2>
                                <div class="result-count">
                                    <?php if ($min_price_found !== null) { ?>
                                        A partir de <?php echo esc_html($format_price($min_price_found)); ?> DH par personne
                                    <?php } else { ?>
                                        Tarifs disponibles sur demande
                                    <?php } ?>
                                </div>
                            </div>
                            <form method="get" action="<?php echo esc_url($voyages_page_url); ?>" class="sort-wrap">
                                <?php foreach ($current_filters as $key => $value) { ?>
                                    <?php if ($key === 'sort' || $key === 'paged') { continue; } ?>
                                    <input type="hidden" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($value); ?>">
                                <?php } ?>
                                Trier par
                                <select name="sort" id="ajvb-sort-select" onchange="this.form.submit()">
                                    <?php foreach ($sort_options as $sort_value => $sort_label) { ?>
                                        <option value="<?php echo esc_attr($sort_value); ?>" <?php selected($sort, $sort_value); ?>><?php echo esc_html($sort_label); ?></option>
                                    <?php } ?>
                                </select>
                            </form>
                        </div>
                        <div class="chips">
                            <span class="chip"><?php echo esc_html((string) $total_results); ?> voyages visibles</span>
                            <span class="chip"><?php echo esc_html((string) count($destinations)); ?> destinations</span>
                            <span class="chip"><?php echo esc_html((string) count($upcoming_departure_dates)); ?> departs a venir</span>
                            <?php foreach ($active_filters as $active_filter) { ?>
                                <a href="<?php echo esc_url($active_filter['url']); ?>" class="chip">
                                    <span><?php echo esc_html($active_filter['label']); ?></span>
                                </a>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="deal-strip">
                        <div>
                            <strong>Voyages selectionnes avec l'accompagnement Ajinsafro</strong>
                            <span>Departures, tarifs et disponibilites mis a jour depuis vos offres WordPress.</span>
                        </div>
                        <a href="<?php echo esc_url($voyages_page_url); ?>" class="deal-strip__link">Explorer</a>
                    </div>

                    <?php if (! empty($cards_page)) { ?>
                        <div class="hotel-list">
                            <?php foreach ($cards_page as $card) { ?>
                                <article class="hotel-card aj-voyage-card">
                                    <a href="<?php echo esc_url($card['permalink']); ?>" class="photo-wrap" aria-label="<?php echo esc_attr($card['title']); ?>">
                                        <?php ajth_render_catalog_card_image($card['id']); ?>
                                        <button class="fav" type="button" aria-label="Ajouter aux favoris">♡</button>
                                        <div class="photo-badges">
                                            <?php if ($card['is_featured']) { ?>
                                                <span class="photo-badge">Selection Ajinsafro</span>
                                            <?php } ?>
                                            <?php if ($card['is_promo']) { ?>
                                                <span class="photo-badge photo-badge--promo">Promo</span>
                                            <?php } ?>
                                            <?php if (! empty($card['stock_badge'])) { ?>
                                                <span class="photo-badge photo-badge--stock"><?php echo esc_html($card['stock_badge']['label']); ?></span>
                                            <?php } ?>
                                        </div>
                                    </a>

                                    <div class="hotel-main">
                                        <div class="meta meta--caps">
                                            <span><?php echo esc_html(! empty($card['themes']) ? implode(' / ', $card['themes']) : 'Selection Ajinsafro'); ?></span>
                                        </div>
                                        <h3><a href="<?php echo esc_url($card['permalink']); ?>"><?php echo esc_html($card['title']); ?></a></h3>
                                        <div class="location">
                                            <?php if ($card['destination'] !== '') { ?><span>Destination: <?php echo esc_html($card['destination']); ?></span><?php } ?>
                                            <?php if ($card['duration_days'] > 0 || $card['duration_label'] !== '') { ?><span><?php echo esc_html($card['duration_label']); ?></span><?php } ?>
                                        </div>
                                        <div class="meta">
                                            <?php if ($card['departure_city'] !== '') { ?><span>Depart: <?php echo esc_html($card['departure_city']); ?></span><?php } ?>
                                            <?php if ($card['next_departure_label'] !== '') { ?><span>Date: <?php echo esc_html($card['next_departure_label']); ?></span><?php } ?>
                                            <?php if ($card['max_people'] > 0) { ?><span><?php echo esc_html((string) $card['max_people']); ?> voyageurs max</span><?php } ?>
                                        </div>
                                        <p class="description"><?php echo esc_html($card['excerpt']); ?></p>
                                        <div class="amenities">
                                            <?php foreach ($card['service_chips'] as $service_chip) { ?>
                                                <span class="amenity"><?php echo esc_html($service_chip); ?></span>
                                            <?php } ?>
                                            <?php foreach ($card['tags'] as $tag_name) { ?>
                                                <span class="amenity amenity--subtle"><?php echo esc_html($tag_name); ?></span>
                                            <?php } ?>
                                        </div>
                                        <div class="good-note">Support Ajinsafro · Disponibilites verifiees · Reservation rapide</div>
                                    </div>

                                    <aside class="hotel-side">
                                        <div class="rating-box">
                                            <div class="rating-text">
                                                <?php if ($card['rating'] > 0) { ?>
                                                    <strong><?php echo esc_html($rating_label((float) $card['rating'])); ?></strong>
                                                    <span><?php echo esc_html($card['reviews'] > 0 ? sprintf('%d avis', $card['reviews']) : 'Sans avis'); ?></span>
                                                <?php } else { ?>
                                                    <strong>Nouveau</strong>
                                                    <span>Sans note client</span>
                                                <?php } ?>
                                            </div>
                                            <div class="rating-score"><?php echo esc_html($card['rating'] > 0 ? number_format((float) $card['rating'], 1, '.', '') : 'New'); ?></div>
                                        </div>

                                        <div class="price-area">
                                            <small>A partir de</small>
                                            <div>
                                                <?php if ($card['price_reference_label'] !== '') { ?>
                                                    <span class="old-price"><?php echo esc_html($card['price_reference_label']); ?> DH</span>
                                                <?php } ?>
                                                <span class="price"><?php echo esc_html($card['price_from_label'] !== '' ? $card['price_from_label'] . ' DH' : 'Prix sur demande'); ?></span>
                                            </div>
                                            <div class="tax"><?php echo esc_html($card['price_from_label'] !== '' ? 'par personne' : 'selon disponibilite'); ?></div>
                                        </div>

                                        <div class="card-actions">
                                            <a class="secondary-btn" href="<?php echo esc_url($card['permalink']); ?>">Voir le voyage</a>
                                            <a class="primary-btn" href="<?php echo esc_url($card['permalink']); ?>#ajtb-v1-summary-card">Reserver</a>
                                        </div>
                                    </aside>
                                </article>
                            <?php } ?>
                        </div>

                        <?php
                        $pagination_base = str_replace(
                            '%25%23%25',
                            '%#%',
                            add_query_arg(array_merge($current_filters, ['paged' => '%#%']), $voyages_page_url)
                        );
                        $pagination = paginate_links([
                            'base' => $pagination_base,
                            'format' => '',
                            'current' => $paged,
                            'total' => $total_pages,
                            'type' => 'array',
                            'prev_text' => '&laquo;',
                            'next_text' => '&raquo;',
                        ]);
                        ?>

                        <?php if (! empty($pagination)) { ?>
                            <nav class="aj-voyages-pagination" aria-label="Pagination voyages">
                                <?php foreach ($pagination as $page_link) { ?>
                                    <?php echo wp_kses_post($page_link); ?>
                                <?php } ?>
                            </nav>
                        <?php } ?>
                    <?php } else { ?>
                        <div class="empty-state" style="display:block;">
                            <h3>Aucun voyage trouve</h3>
                            <p>Essayez de modifier votre budget, vos dates ou votre destination.</p>
                            <a class="primary-btn" href="<?php echo esc_url($voyages_page_url); ?>">Reinitialiser les filtres</a>
                        </div>
                    <?php } ?>
                </section>

                <aside class="ad-col" aria-label="Promotions voyages">
                    <div class="ad-box">
                        <strong>Departs verifies sur nos sejours phares</strong>
                        <button type="button">Voir les offres</button>
                    </div>
                    <div class="ad-box">
                        <strong>Circuits, omra et escapades selectionnes par Ajinsafro</strong>
                        <button type="button">Reserver</button>
                    </div>
                </aside>
            </main>

            <button class="mobile-filter-btn" type="button" id="ajvb-open-filters">Filtres</button>
            <div class="drawer-backdrop" id="ajvb-drawer-backdrop"></div>
            <aside class="mobile-drawer" id="ajvb-mobile-drawer" aria-label="Filtres mobile">
                <div class="drawer-head">
                    <h3>Filtres</h3>
                    <button type="button" id="ajvb-close-filters">x</button>
                </div>
                <?php include AJTH_DIR . 'parts/voyages-filters.php'; ?>
            </aside>
        </div>
    </div>
</div>

<?php
wp_reset_postdata();
get_footer();
