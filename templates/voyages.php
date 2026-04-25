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
$allowed_sorts = ['recommended', 'price_asc', 'price_desc', 'rating_desc', 'popular', 'duration_desc', 'departure_soonest', 'newest', 'title_asc'];
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

    $post_date = get_post_field('post_date', $post_id);
    $address = $meta_value($meta, 'address');
    $catalog_destination = $meta_value($meta, 'aj_catalog_destination');
    $destination_label = $catalog_destination !== '' ? $catalog_destination : $address;
    $duration_days = (int) $meta_value($meta, 'duration_day');
    $duration_text = $meta_value($meta, 'duration');
    $max_people = (int) $meta_value($meta, 'max_people');
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
        'title' => get_the_title($post_id),
        'permalink' => get_permalink($post_id),
        'excerpt' => get_the_excerpt($post_id) !== ''
            ? wp_trim_words(get_the_excerpt($post_id), 24, '...')
            : wp_trim_words(wp_strip_all_tags(get_post_field('post_content', $post_id)), 24, '...'),
        'image' => get_the_post_thumbnail_url($post_id, 'large'),
        'destination' => $destination_label,
        'duration_days' => $duration_days,
        'duration_label' => $build_duration_label($duration_days, $duration_text),
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
    'duration_desc' => 'Sejours les plus longs',
    'departure_soonest' => 'Departs les plus proches',
    'newest' => 'Nouveautes',
    'title_asc' => 'Nom A-Z',
];
?>

<div class="aj-home-wrap">
    <div id="aj-home" class="aj-home aj-voyages-page aj-voyages-page--premium">
        <?php ajth_render_site_header($settings); ?>

        <section class="aj-voyages-search-shell">
            <div class="aj-container">
                <div class="aj-voyages-search-hero">
                    <div class="aj-voyages-search-hero__content">
                        <span class="aj-voyages-search-hero__eyebrow">Catalogue Ajinsafro</span>
                        <h1 class="aj-voyages-search-hero__title">Voyages, sejours et circuits avec une experience de reservation plus fluide.</h1>
                        <p class="aj-voyages-search-hero__text">Comparez rapidement les offres, ciblez votre budget et trouvez le meilleur depart disponible sans quitter la page.</p>
                    </div>

                    <form method="get" action="<?php echo esc_url($voyages_page_url); ?>" class="aj-voyages-searchbar" aria-label="Recherche rapide des voyages">
                        <input type="hidden" name="sort" value="<?php echo esc_attr($sort); ?>">
                        <div class="aj-voyages-searchbar__grid">
                            <label class="aj-voyages-searchbar__field">
                                <span class="aj-voyages-searchbar__label">Destination</span>
                                <input type="text" name="dest" class="aj-voyages-searchbar__input" value="<?php echo esc_attr($dest !== '' ? $dest : $keyword); ?>" placeholder="Marrakech, Istanbul, Omra...">
                            </label>
                            <label class="aj-voyages-searchbar__field">
                                <span class="aj-voyages-searchbar__label">Date de depart</span>
                                <input type="date" name="depart_date" class="aj-voyages-searchbar__input" value="<?php echo esc_attr($depart_date); ?>">
                            </label>
                            <label class="aj-voyages-searchbar__field">
                                <span class="aj-voyages-searchbar__label">Voyageurs min</span>
                                <input type="number" min="1" name="guests_min" class="aj-voyages-searchbar__input" value="<?php echo esc_attr($guests_min > 0 ? (string) $guests_min : ''); ?>" placeholder="2">
                            </label>
                            <label class="aj-voyages-searchbar__field">
                                <span class="aj-voyages-searchbar__label">Budget max</span>
                                <input type="number" min="0" name="price_max" class="aj-voyages-searchbar__input" value="<?php echo esc_attr($price_max > 0 ? (string) $price_max : ''); ?>" placeholder="12000">
                            </label>
                            <button type="submit" class="aj-voyages-searchbar__submit">
                                <i class="fas fa-search" aria-hidden="true"></i>
                                Rechercher
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <section class="aj-voyages-catalog aj-voyages-catalog--premium">
            <div class="aj-container aj-voyages-catalog__container">
                <input type="checkbox" id="aj-voyages-filters-toggle" class="aj-voyages-filters-toggle" tabindex="-1" aria-hidden="true">
                <label for="aj-voyages-filters-toggle" class="aj-voyages-filters-backdrop" aria-hidden="true"></label>

                <div class="aj-voyages-catalog__grid">
                    <aside class="aj-voyages-filters-sidebar" id="aj-voyages-filters-panel" aria-label="Filtres des voyages">
                        <label for="aj-voyages-filters-toggle" class="aj-voyages-filters-close" aria-label="Fermer les filtres"><span aria-hidden="true">&times;</span></label>
                        <?php include AJTH_DIR . 'parts/voyages-filters.php'; ?>
                    </aside>

                    <main class="aj-voyages-catalog__main">
                        <label for="aj-voyages-filters-toggle" class="aj-voyages-filters-mobile-trigger aj-voyages-filters-mobile-trigger--sticky">
                            <i class="fas fa-sliders-h" aria-hidden="true"></i>
                            Filtres
                            <?php if (! empty($active_filters)) { ?>
                                <span class="aj-voyages-filters-mobile-trigger__count"><?php echo esc_html((string) count($active_filters)); ?></span>
                            <?php } ?>
                        </label>

                        <section class="aj-voyages-summary-card">
                            <div class="aj-voyages-summary-card__main">
                                <span class="aj-voyages-summary-card__kicker">Resultats</span>
                                <h2 class="aj-voyages-summary-card__title"><?php echo esc_html($results_headline); ?></h2>
                                <p class="aj-voyages-summary-card__meta">
                                    <?php if ($min_price_found !== null) { ?>
                                        <span>A partir de <?php echo esc_html($format_price($min_price_found)); ?> DH / pers</span>
                                    <?php } else { ?>
                                        <span>Tarifs disponibles sur demande</span>
                                    <?php } ?>
                                    <span><?php echo esc_html($total_results > 0 ? sprintf('%d page(s) de resultats', $total_pages) : 'Aucun resultat pour les criteres actuels'); ?></span>
                                </p>
                            </div>
                            <div class="aj-voyages-summary-card__stats">
                                <div class="aj-voyages-summary-card__stat">
                                    <strong><?php echo esc_html((string) $total_results); ?></strong>
                                    <span>Offres visibles</span>
                                </div>
                                <div class="aj-voyages-summary-card__stat">
                                    <strong><?php echo esc_html((string) count($destinations)); ?></strong>
                                    <span>Destinations</span>
                                </div>
                                <div class="aj-voyages-summary-card__stat">
                                    <strong><?php echo esc_html((string) count($available_departure_dates)); ?></strong>
                                    <span>Dates actives</span>
                                </div>
                            </div>
                        </section>

                        <?php if (! empty($active_filters)) { ?>
                            <section class="aj-voyages-active-filters" aria-label="Filtres actifs">
                                <div class="aj-voyages-active-filters__row">
                                    <?php foreach ($active_filters as $active_filter) { ?>
                                        <a href="<?php echo esc_url($active_filter['url']); ?>" class="aj-voyages-active-filters__chip">
                                            <span><?php echo esc_html($active_filter['label']); ?></span>
                                            <i class="fas fa-times" aria-hidden="true"></i>
                                        </a>
                                    <?php } ?>
                                </div>
                                <a href="<?php echo esc_url($voyages_page_url); ?>" class="aj-voyages-active-filters__reset">Reinitialiser tous les filtres</a>
                            </section>
                        <?php } ?>

                        <div class="aj-voyages-toolbar aj-voyages-toolbar--premium">
                            <div class="aj-voyages-toolbar__left">
                                <h2 class="aj-voyages-toolbar__title"><?php echo esc_html($total_results > 0 ? 'Offres disponibles' : 'Ajustez votre recherche'); ?></h2>
                                <p class="aj-voyages-toolbar__count"><?php echo esc_html($total_results > 0 ? 'Tri, comparaison et reservation depuis une seule vue.' : 'Essayez d elargir vos dates, votre budget ou votre destination.'); ?></p>
                            </div>
                            <div class="aj-voyages-toolbar__sort">
                                <form method="get" class="aj-voyages-sort-form" action="<?php echo esc_url($voyages_page_url); ?>">
                                    <?php foreach ($current_filters as $key => $value) { ?>
                                        <?php if ($key === 'sort' || $key === 'paged') { continue; } ?>
                                        <input type="hidden" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($value); ?>">
                                    <?php } ?>
                                    <label class="aj-voyages-sort-form__label" for="aj-voyages-catalog-sort">Trier par</label>
                                    <select name="sort" id="aj-voyages-catalog-sort" class="aj-voyages-sort-form__select" onchange="this.form.submit()">
                                        <?php foreach ($sort_options as $sort_value => $sort_label) { ?>
                                            <option value="<?php echo esc_attr($sort_value); ?>" <?php selected($sort, $sort_value); ?>><?php echo esc_html($sort_label); ?></option>
                                        <?php } ?>
                                    </select>
                                </form>
                            </div>
                        </div>

                        <?php if (! empty($cards_page)) { ?>
                            <div class="aj-voyages-results-list">
                                <?php foreach ($cards_page as $card) { ?>
                                    <article class="aj-voyages-result-card">
                                        <a href="<?php echo esc_url($card['permalink']); ?>" class="aj-voyages-result-card__media" aria-label="<?php echo esc_attr($card['title']); ?>">
                                            <?php if (! empty($card['image'])) { ?>
                                                <img src="<?php echo esc_url($card['image']); ?>" alt="<?php echo esc_attr($card['title']); ?>" loading="lazy">
                                            <?php } else { ?>
                                                <div class="aj-voyages-result-card__fallback">
                                                    <i class="fas fa-image" aria-hidden="true"></i>
                                                    <span>Visuel a venir</span>
                                                </div>
                                            <?php } ?>

                                            <div class="aj-voyages-result-card__badges">
                                                <?php if ($card['is_featured']) { ?>
                                                    <span class="aj-voyages-badge aj-voyages-badge--brand">Ajinsafro recommande</span>
                                                <?php } ?>
                                                <?php if ($card['is_promo']) { ?>
                                                    <span class="aj-voyages-badge aj-voyages-badge--promo">Promo</span>
                                                <?php } ?>
                                                <?php if (! empty($card['stock_badge'])) { ?>
                                                    <span class="aj-voyages-badge aj-voyages-badge--<?php echo esc_attr($card['stock_badge']['tone']); ?>"><?php echo esc_html($card['stock_badge']['label']); ?></span>
                                                <?php } ?>
                                            </div>
                                        </a>

                                        <div class="aj-voyages-result-card__body">
                                            <div class="aj-voyages-result-card__content">
                                                <div class="aj-voyages-result-card__topline">
                                                    <?php if (! empty($card['themes'])) { ?>
                                                        <span class="aj-voyages-result-card__theme"><?php echo esc_html(implode(' · ', $card['themes'])); ?></span>
                                                    <?php } else { ?>
                                                        <span class="aj-voyages-result-card__theme">Selection Ajinsafro</span>
                                                    <?php } ?>
                                                </div>

                                                <h3 class="aj-voyages-result-card__title"><a href="<?php echo esc_url($card['permalink']); ?>"><?php echo esc_html($card['title']); ?></a></h3>

                                                <div class="aj-voyages-result-card__meta-line">
                                                    <span><i class="fas fa-map-marker-alt" aria-hidden="true"></i><?php echo esc_html($card['destination'] !== '' ? $card['destination'] : 'Destination a confirmer'); ?></span>
                                                    <span><i class="far fa-clock" aria-hidden="true"></i><?php echo esc_html($card['duration_label']); ?></span>
                                                    <?php if ($card['max_people'] > 0) { ?>
                                                        <span><i class="fas fa-users" aria-hidden="true"></i>Jusqu a <?php echo esc_html((string) $card['max_people']); ?> voyageurs</span>
                                                    <?php } ?>
                                                </div>

                                                <div class="aj-voyages-result-card__rating-row">
                                                    <?php if ($card['rating'] > 0) { ?>
                                                        <span class="aj-voyages-rating-pill">
                                                            <strong><?php echo esc_html(number_format((float) $card['rating'], 1, ',', ' ')); ?></strong>
                                                            <span><?php echo esc_html($card['rating'] >= 9 ? 'Exceptionnel' : ($card['rating'] >= 8 ? 'Excellent' : 'Tres bien')); ?></span>
                                                        </span>
                                                    <?php } else { ?>
                                                        <span class="aj-voyages-rating-pill aj-voyages-rating-pill--muted">
                                                            <strong>Nouveau</strong>
                                                            <span>Sans note client</span>
                                                        </span>
                                                    <?php } ?>
                                                    <span class="aj-voyages-result-card__reviews"><?php echo esc_html($card['reviews'] > 0 ? sprintf('%d avis', $card['reviews']) : 'Aucun avis publie'); ?></span>
                                                </div>

                                                <p class="aj-voyages-result-card__excerpt"><?php echo esc_html($card['excerpt']); ?></p>

                                                <div class="aj-voyages-result-card__chips">
                                                    <?php foreach ($card['tags'] as $tag_name) { ?>
                                                        <span class="aj-voyages-result-card__chip"><?php echo esc_html($tag_name); ?></span>
                                                    <?php } ?>
                                                    <?php if ($card['next_departure_label'] !== '') { ?>
                                                        <span class="aj-voyages-result-card__chip aj-voyages-result-card__chip--date">Depart: <?php echo esc_html($card['next_departure_label']); ?></span>
                                                    <?php } ?>
                                                </div>

                                                <div class="aj-voyages-result-card__trust">
                                                    <span>Confirmation rapide</span>
                                                    <span>Support Ajinsafro</span>
                                                    <span>Places verifiees</span>
                                                </div>
                                            </div>

                                            <div class="aj-voyages-result-card__pricing">
                                                <button type="button" class="aj-voyages-result-card__favorite" aria-label="Ajouter aux favoris">
                                                    <i class="far fa-heart" aria-hidden="true"></i>
                                                </button>

                                                <?php if ($card['price_reference_label'] !== '') { ?>
                                                    <span class="aj-voyages-result-card__old-price"><?php echo esc_html($card['price_reference_label']); ?> DH</span>
                                                <?php } ?>

                                                <?php if ($card['price_from_label'] !== '') { ?>
                                                    <span class="aj-voyages-result-card__price-prefix">A partir de</span>
                                                    <div class="aj-voyages-result-card__price"><?php echo esc_html($card['price_from_label']); ?> <span>DH</span></div>
                                                    <span class="aj-voyages-result-card__price-note">par personne</span>
                                                <?php } else { ?>
                                                    <span class="aj-voyages-result-card__price-prefix">Tarif</span>
                                                    <div class="aj-voyages-result-card__price aj-voyages-result-card__price--small">Sur demande</div>
                                                    <span class="aj-voyages-result-card__price-note">selon disponibilite</span>
                                                <?php } ?>

                                                <div class="aj-voyages-result-card__actions">
                                                    <a href="<?php echo esc_url($card['permalink']); ?>" class="aj-voyages-result-card__cta aj-voyages-result-card__cta--ghost">Voir les details</a>
                                                    <a href="<?php echo esc_url($card['permalink']); ?>#ajtb-v1-summary-card" class="aj-voyages-result-card__cta aj-voyages-result-card__cta--primary">Reserver</a>
                                                </div>
                                            </div>
                                        </div>
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
                            <section class="aj-voyages-empty aj-voyages-empty--premium">
                                <div class="aj-voyages-empty__icon"><i class="fas fa-compass" aria-hidden="true"></i></div>
                                <h3>Aucun voyage ne correspond a vos criteres</h3>
                                <p>Elargissez la destination, les dates ou le budget pour retrouver des offres disponibles.</p>
                                <div class="aj-voyages-empty__actions">
                                    <a href="<?php echo esc_url($voyages_page_url); ?>" class="aj-voyages-result-card__cta aj-voyages-result-card__cta--primary">Reinitialiser les filtres</a>
                                    <?php if ($dest !== '' || $keyword !== '') { ?>
                                        <a href="<?php echo esc_url($build_url(['dest' => '', 's' => '', 'location_name' => ''])); ?>" class="aj-voyages-result-card__cta aj-voyages-result-card__cta--ghost">Retirer la recherche</a>
                                    <?php } ?>
                                </div>
                            </section>
                        <?php } ?>
                    </main>
                </div>
            </div>
        </section>
    </div>
</div>

<?php
wp_reset_postdata();
get_footer();
