<?php
/**
 * Part: Voyages filters.
 *
 * Expected variables from template scope:
 * - $voyages_page_url
 * - $sort
 * - $search_text
 * - $dest
 * - $depart_date
 * - $duration_min
 * - $duration_max
 * - $price_min
 * - $price_max
 * - $guests_min
 * - $min_rating
 * - $category_slug
 * - $tag_slug
 * - $featured_only
 * - $promo_only
 * - $available_only
 * - $catalog_themes
 * - $catalog_tags
 * - $destinations
 */
if (! defined('ABSPATH')) {
    exit;
}
?>

<form method="get" action="<?php echo esc_url($voyages_page_url); ?>" class="aj-voyages-filters-form">
    <input type="hidden" name="sort" value="<?php echo esc_attr($sort); ?>">

    <div class="aj-voyages-filters__intro aj-voyages-filters__intro--premium">
        <h3 class="aj-voyages-filters__heading">Affiner la recherche</h3>
        <p class="aj-voyages-filters__hint">Des filtres simples, regroupes et compatibles avec une URL partageable.</p>
    </div>

    <details class="aj-voyages-filter-group" open>
        <summary class="aj-voyages-filter-group__summary">
            <span>Votre recherche</span>
            <i class="fas fa-chevron-down" aria-hidden="true"></i>
        </summary>
        <div class="aj-voyages-filter-group__body">
            <label class="aj-voyages-filters__field">
                <span class="aj-voyages-filters__label">Mot-cle ou voyage</span>
                <input type="text" name="s" class="aj-voyages-filters__input" value="<?php echo esc_attr($search_text); ?>" placeholder="Nom du voyage, destination, theme">
            </label>

            <label class="aj-voyages-filters__field">
                <span class="aj-voyages-filters__label">Destination</span>
                <select name="dest" class="aj-voyages-filters__select">
                    <option value="">Toutes les destinations</option>
                    <?php foreach ((array) $destinations as $destination_option) { ?>
                        <option value="<?php echo esc_attr($destination_option['value']); ?>" <?php selected($dest, $destination_option['value']); ?>>
                            <?php echo esc_html($destination_option['label']); ?>
                        </option>
                    <?php } ?>
                </select>
            </label>

            <label class="aj-voyages-filters__field">
                <span class="aj-voyages-filters__label">Date de depart</span>
                <input type="date" name="depart_date" class="aj-voyages-filters__input" value="<?php echo esc_attr($depart_date); ?>">
            </label>
        </div>
    </details>

    <details class="aj-voyages-filter-group" open>
        <summary class="aj-voyages-filter-group__summary">
            <span>Budget</span>
            <i class="fas fa-chevron-down" aria-hidden="true"></i>
        </summary>
        <div class="aj-voyages-filter-group__body">
            <div class="aj-voyages-filters__row">
                <label class="aj-voyages-filters__field aj-voyages-filters__field--half">
                    <span class="aj-voyages-filters__label">Prix min (DH)</span>
                    <input type="number" min="0" name="price_min" class="aj-voyages-filters__input" value="<?php echo esc_attr($price_min > 0 ? (string) $price_min : ''); ?>" placeholder="0">
                </label>
                <label class="aj-voyages-filters__field aj-voyages-filters__field--half">
                    <span class="aj-voyages-filters__label">Prix max (DH)</span>
                    <input type="number" min="0" name="price_max" class="aj-voyages-filters__input" value="<?php echo esc_attr($price_max > 0 ? (string) $price_max : ''); ?>" placeholder="15000">
                </label>
            </div>
        </div>
    </details>

    <details class="aj-voyages-filter-group" open>
        <summary class="aj-voyages-filter-group__summary">
            <span>Duree et voyageurs</span>
            <i class="fas fa-chevron-down" aria-hidden="true"></i>
        </summary>
        <div class="aj-voyages-filter-group__body">
            <div class="aj-voyages-filters__row">
                <label class="aj-voyages-filters__field aj-voyages-filters__field--half">
                    <span class="aj-voyages-filters__label">Duree min (jours)</span>
                    <input type="number" min="0" name="duration_min" class="aj-voyages-filters__input" value="<?php echo esc_attr($duration_min > 0 ? (string) $duration_min : ''); ?>" placeholder="3">
                </label>
                <label class="aj-voyages-filters__field aj-voyages-filters__field--half">
                    <span class="aj-voyages-filters__label">Duree max (jours)</span>
                    <input type="number" min="0" name="duration_max" class="aj-voyages-filters__input" value="<?php echo esc_attr($duration_max > 0 ? (string) $duration_max : ''); ?>" placeholder="10">
                </label>
            </div>

            <label class="aj-voyages-filters__field">
                <span class="aj-voyages-filters__label">Nombre minimum de voyageurs</span>
                <input type="number" min="1" name="guests_min" class="aj-voyages-filters__input" value="<?php echo esc_attr($guests_min > 0 ? (string) $guests_min : ''); ?>" placeholder="2">
            </label>
        </div>
    </details>

    <details class="aj-voyages-filter-group" open>
        <summary class="aj-voyages-filter-group__summary">
            <span>Theme et tags</span>
            <i class="fas fa-chevron-down" aria-hidden="true"></i>
        </summary>
        <div class="aj-voyages-filter-group__body">
            <label class="aj-voyages-filters__field">
                <span class="aj-voyages-filters__label">Theme du voyage</span>
                <select name="cat" class="aj-voyages-filters__select">
                    <option value="">Tous les themes</option>
                    <?php foreach ((array) $catalog_themes as $theme_term) { ?>
                        <?php if (! $theme_term instanceof WP_Term) { continue; } ?>
                        <option value="<?php echo esc_attr($theme_term->slug); ?>" <?php selected($category_slug, $theme_term->slug); ?>>
                            <?php echo esc_html($theme_term->name); ?>
                        </option>
                    <?php } ?>
                </select>
            </label>

            <label class="aj-voyages-filters__field">
                <span class="aj-voyages-filters__label">Tag</span>
                <select name="tag" class="aj-voyages-filters__select">
                    <option value="">Tous les tags</option>
                    <?php foreach ((array) $catalog_tags as $tag_term) { ?>
                        <?php if (! $tag_term instanceof WP_Term) { continue; } ?>
                        <option value="<?php echo esc_attr($tag_term->slug); ?>" <?php selected($tag_slug, $tag_term->slug); ?>>
                            <?php echo esc_html($tag_term->name); ?>
                        </option>
                    <?php } ?>
                </select>
            </label>
        </div>
    </details>

    <details class="aj-voyages-filter-group" open>
        <summary class="aj-voyages-filter-group__summary">
            <span>Note client</span>
            <i class="fas fa-chevron-down" aria-hidden="true"></i>
        </summary>
        <div class="aj-voyages-filter-group__body">
            <div class="aj-voyages-filters__checks">
                <?php foreach ([0 => 'Toutes', 8 => 'Tres bien 8+', 9 => 'Excellent 9+'] as $rating_value => $rating_label) { ?>
                    <label class="aj-voyages-filters__choice">
                        <input type="radio" name="min_rating" value="<?php echo esc_attr((string) $rating_value); ?>" <?php checked((float) $min_rating, (float) $rating_value); ?>>
                        <span><?php echo esc_html($rating_label); ?></span>
                    </label>
                <?php } ?>
            </div>
        </div>
    </details>

    <details class="aj-voyages-filter-group" open>
        <summary class="aj-voyages-filter-group__summary">
            <span>Offres speciales</span>
            <i class="fas fa-chevron-down" aria-hidden="true"></i>
        </summary>
        <div class="aj-voyages-filter-group__body">
            <label class="aj-voyages-filters__checkbox">
                <input type="checkbox" name="featured" value="1" <?php checked($featured_only); ?>>
                <span>Recommande par Ajinsafro</span>
            </label>
            <label class="aj-voyages-filters__checkbox">
                <input type="checkbox" name="promo_only" value="1" <?php checked($promo_only); ?>>
                <span>Promotions uniquement</span>
            </label>
            <label class="aj-voyages-filters__checkbox">
                <input type="checkbox" name="available_only" value="1" <?php checked($available_only); ?>>
                <span>Disponibilite immediate</span>
            </label>
        </div>
    </details>

    <div class="aj-voyages-filters__actions aj-voyages-filters__actions--sticky">
        <button type="submit" class="aj-voyages-filters__submit">
            <i class="fas fa-search" aria-hidden="true"></i>
            Appliquer les filtres
        </button>
        <a class="aj-voyages-filters__reset" href="<?php echo esc_url($voyages_page_url); ?>">
            Reinitialiser
        </a>
    </div>
</form>
