<?php
/**
 * Part: Explorez plus — accordéon horizontal (prototype HTML)
 *
 * @package AjinsafroTravelerHome
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $settings ) || ! is_array( $settings ) ) {
	$settings = array();
}

$promo = isset( $settings['promotions'] ) && is_array( $settings['promotions'] ) ? $settings['promotions'] : array();

$promo_section_on = ! isset( $promo['enabled'] );
if ( isset( $promo['enabled'] ) ) {
	$promo_section_on = function_exists( 'ajth_truthy' ) ? ajth_truthy( $promo['enabled'] ) : ! empty( $promo['enabled'] );
}
if ( ! $promo_section_on ) {
	return;
}

$section_title = ! empty( $promo['title'] ) ? trim( (string) $promo['title'] ) : 'Explorez plus, voyagez mieux avec AjiNsafro';
$raw_items = isset( $promo['items'] ) && is_array( $promo['items'] ) ? $promo['items'] : array();
$max_slides = isset( $promo['max_slides'] ) ? max( 1, min( 20, (int) $promo['max_slides'] ) ) : 8;
$items = array();
foreach ( $raw_items as $row ) {
	if ( ! is_array( $row ) ) {
		continue;
	}
	$active = isset( $row['is_active'] ) ? ( function_exists( 'ajth_truthy' ) ? ajth_truthy( $row['is_active'] ) : ! empty( $row['is_active'] ) ) : true;
	if ( ! $active ) {
		continue;
	}
	$items[] = $row;
	if ( count( $items ) >= $max_slides ) {
		break;
	}
}

if ( empty( $items ) ) {
	return;
}

$autoplay = ! isset( $promo['autoplay'] ) || ( function_exists( 'ajth_truthy' ) ? ajth_truthy( $promo['autoplay'] ) : ! empty( $promo['autoplay'] ) );
$delay_ms = isset( $promo['autoplay_delay_ms'] ) ? max( 2000, min( 60000, (int) $promo['autoplay_delay_ms'] ) ) : 5000;
$def_idx = isset( $promo['default_active_index'] ) ? max( 0, (int) $promo['default_active_index'] ) : 0;
if ( count( $items ) > 0 ) {
	$def_idx = min( $def_idx, count( $items ) - 1 );
} else {
	$def_idx = 0;
}
$arrows = ! isset( $promo['arrows_enabled'] ) || ( function_exists( 'ajth_truthy' ) ? ajth_truthy( $promo['arrows_enabled'] ) : ! empty( $promo['arrows_enabled'] ) );

$uid = 'aj-promo-acc-' . ( function_exists( 'wp_unique_id' ) ? wp_unique_id() : uniqid( '', false ) );
$strip_id = $uid . '-strip';
?>
<section class="aj-promos aj-promos--accordion" id="aj-promos" aria-labelledby="<?php echo esc_attr( $uid ); ?>-heading">
	<div class="aj-container">
		<div class="aj-promos__frame aj-promos__frame--accordion">
			<div class="aj-section-head aj-section-head--accordion">
				<h2 class="aj-section-title" id="<?php echo esc_attr( $uid ); ?>-heading"><?php echo esc_html( $section_title ); ?></h2>
				<?php if ( $arrows && count( $items ) > 1 ) : ?>
					<div class="aj-promo-acc__arrows" role="group" aria-label="<?php esc_attr_e( 'Navigation', 'ajinsafro-traveler-home' ); ?>">
						<button type="button" class="aj-section-arrow aj-promo-acc__prev" aria-controls="<?php echo esc_attr( $strip_id ); ?>" aria-label="<?php esc_attr_e( 'Précédent', 'ajinsafro-traveler-home' ); ?>">
							<i class="fas fa-angle-left" aria-hidden="true"></i>
						</button>
						<button type="button" class="aj-section-arrow aj-promo-acc__next" aria-controls="<?php echo esc_attr( $strip_id ); ?>" aria-label="<?php esc_attr_e( 'Suivant', 'ajinsafro-traveler-home' ); ?>">
							<i class="fas fa-angle-right" aria-hidden="true"></i>
						</button>
					</div>
				<?php endif; ?>
			</div>

			<div
				class="aj-promo-acc"
				id="<?php echo esc_attr( $uid ); ?>"
				data-root="<?php echo esc_attr( $uid ); ?>"
				data-autoplay="<?php echo $autoplay ? '1' : '0'; ?>"
				data-delay="<?php echo esc_attr( (string) $delay_ms ); ?>"
				data-default-index="<?php echo esc_attr( (string) $def_idx ); ?>"
			>
				<div class="aj-promo-acc__strip" id="<?php echo esc_attr( $strip_id ); ?>" role="tablist" aria-label="<?php echo esc_attr( $section_title ); ?>">
					<?php
					foreach ( $items as $i => $item ) :
						if ( ! is_array( $item ) ) {
							continue;
						}
						$image_url = trim( (string) ( $item['image_url'] ?? $item['image'] ?? '' ) );
						if ( function_exists( 'ajth_normalize_storage_url' ) ) {
							$image_url = ajth_normalize_storage_url( $image_url );
						}
						$title = trim( (string) ( $item['title'] ?? '' ) );
						$subtitle = trim( (string) ( $item['subtitle'] ?? $item['description'] ?? '' ) );
						$link_url = function_exists( 'ajth_sanitize_promo_url' ) ? ajth_sanitize_promo_url( (string) ( $item['link_url'] ?? '' ) ) : trim( (string) ( $item['link_url'] ?? '' ) );
						$link_target = ( isset( $item['link_target'] ) && (string) $item['link_target'] === '_blank' ) ? '_blank' : '_self';
						$button_text = trim( (string) ( $item['button_text'] ?? '' ) );
						$button_url = function_exists( 'ajth_sanitize_promo_url' ) ? ajth_sanitize_promo_url( (string) ( $item['button_url'] ?? '' ) ) : trim( (string) ( $item['button_url'] ?? '' ) );
						$btn_on = ! isset( $item['button_enabled'] ) || ( function_exists( 'ajth_truthy' ) ? ajth_truthy( $item['button_enabled'] ) : ! empty( $item['button_enabled'] ) );
						$accent = trim( (string) ( $item['accent_color'] ?? '' ) );
						if ( $accent !== '' && ! preg_match( '/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $accent ) ) {
							$accent = '';
						}
						$show_btn = $btn_on && $button_text !== '' && $button_url !== '';
						$conflict = ( $link_url !== '' && $btn_on && $button_text !== '' && $button_url !== '' && $button_url !== $link_url );
						$wrap_link = ( $link_url !== '' && ! $conflict );

						$rel = ( $link_target === '_blank' ) ? 'noopener noreferrer' : '';
						$is_active = (int) $i === (int) $def_idx;
						$style = '';
						if ( $accent !== '' ) {
							$style = '--aj-promo-accent:' . esc_attr( $accent ) . ';';
						}
						?>
					<div
						class="aj-promo-acc__panel<?php echo $is_active ? ' is-active' : ''; ?>"
						data-index="<?php echo esc_attr( (string) $i ); ?>"
						role="tab"
						aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
						aria-expanded="<?php echo $is_active ? 'true' : 'false'; ?>"
					>
						<?php if ( $wrap_link ) : ?>
						<a class="aj-promo-acc__surface aj-promo-acc__surface--link" style="<?php echo esc_attr( $style ); ?>" href="<?php echo esc_url( $link_url ); ?>"<?php echo $link_target === '_blank' ? ' target="_blank" rel="' . esc_attr( $rel ) . '"' : ''; ?>>
						<?php else : ?>
						<div class="aj-promo-acc__surface aj-promo-acc__surface--static" style="<?php echo esc_attr( $style ); ?>" tabindex="0" role="button" aria-label="<?php echo esc_attr( $title !== '' ? $title : __( 'Voir cette offre', 'ajinsafro-traveler-home' ) ); ?>">
						<?php endif; ?>

							<div class="aj-promo-acc__media">
								<?php if ( $image_url !== '' ) : ?>
									<img src="<?php echo esc_url( $image_url ); ?>" alt="" loading="<?php echo $is_active ? 'eager' : 'lazy'; ?>" decoding="async" width="800" height="520">
								<?php else : ?>
									<span class="aj-promo-acc__fallback" aria-hidden="true"></span>
								<?php endif; ?>
							</div>
							<div class="aj-promo-acc__scrim" aria-hidden="true"></div>
							<div class="aj-promo-acc__content">
								<?php if ( $title !== '' ) : ?>
									<?php if ( $conflict && $link_url !== '' ) : ?>
										<a class="aj-promo-acc__title aj-promo-acc__title--link" href="<?php echo esc_url( $link_url ); ?>"<?php echo $link_target === '_blank' ? ' target="_blank" rel="' . esc_attr( $rel ) . '"' : ''; ?>><?php echo esc_html( $title ); ?></a>
									<?php else : ?>
										<span class="aj-promo-acc__title"><?php echo esc_html( $title ); ?></span>
									<?php endif; ?>
								<?php endif; ?>
								<?php if ( $subtitle !== '' ) : ?>
									<p class="aj-promo-acc__desc"><?php echo esc_html( $subtitle ); ?></p>
								<?php endif; ?>
								<?php if ( $show_btn ) : ?>
									<span class="aj-promo-acc__btn-wrap"><a class="aj-promo-acc__btn" href="<?php echo esc_url( $button_url ); ?>"<?php echo $link_target === '_blank' ? ' target="_blank" rel="' . esc_attr( $rel ) . '"' : ''; ?>><?php echo esc_html( $button_text ); ?></a></span>
								<?php elseif ( $btn_on && $button_text !== '' ) : ?>
									<span class="aj-promo-acc__btn-wrap"><span class="aj-promo-acc__btn aj-promo-acc__btn--text"><?php echo esc_html( $button_text ); ?></span></span>
								<?php endif; ?>
							</div>

						<?php if ( $wrap_link ) : ?>
						</a>
						<?php else : ?>
						</div>
						<?php endif; ?>
					</div>
						<?php endforeach; ?>
				</div>
			</div>
		</div>
	</div>
</section>
