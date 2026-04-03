<?php
/**
 * Part: Explorez plus - featured banner + right previews (prototype)
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
$def_idx = min( $def_idx, count( $items ) - 1 );
$arrows = ! isset( $promo['arrows_enabled'] ) || ( function_exists( 'ajth_truthy' ) ? ajth_truthy( $promo['arrows_enabled'] ) : ! empty( $promo['arrows_enabled'] ) );

$uid = 'aj-promo-split-' . ( function_exists( 'wp_unique_id' ) ? wp_unique_id() : uniqid( '', false ) );

/** @var array<int, array<string, mixed>> $slides_payload */
$slides_payload = array();
foreach ( $items as $i => $item ) {
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
	$conflict = ( $link_url !== '' && $show_btn && $button_url !== $link_url );

	$slides_payload[] = array(
		'index'          => (int) $i,
		'image_url'      => $image_url,
		'title'          => $title,
		'subtitle'       => $subtitle,
		'link_url'       => $link_url,
		'link_target'    => $link_target,
		'button_text'    => $button_text,
		'button_url'     => $button_url,
		'button_enabled' => (bool) $btn_on,
		'accent_color'   => $accent,
		'conflict_links' => $conflict,
		'wrap_link'      => ( $link_url !== '' && ! $conflict ),
	);
}

$slides_json = wp_json_encode(
	$slides_payload,
	JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE
);
if ( ! is_string( $slides_json ) ) {
	$slides_json = '[]';
}

/**
 * Render featured card markup (same structure as JS render).
 *
 * @param array<string, mixed> $slide Slide payload.
 */
$render_featured = static function ( array $slide ) : string {
	$image_url = (string) ( $slide['image_url'] ?? '' );
	$title = (string) ( $slide['title'] ?? '' );
	$subtitle = (string) ( $slide['subtitle'] ?? '' );
	$link_url = (string) ( $slide['link_url'] ?? '' );
	$link_target = (string) ( $slide['link_target'] ?? '_self' );
	$rel = ( $link_target === '_blank' ) ? 'noopener noreferrer' : '';
	$button_text = (string) ( $slide['button_text'] ?? '' );
	$button_url = (string) ( $slide['button_url'] ?? '' );
	$btn_on = ! empty( $slide['button_enabled'] );
	$accent = (string) ( $slide['accent_color'] ?? '' );
	$conflict = ! empty( $slide['conflict_links'] );
	$wrap_link = ! empty( $slide['wrap_link'] );
	$show_btn = $btn_on && $button_text !== '' && $button_url !== '';

	$style = '';
	if ( $accent !== '' && preg_match( '/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $accent ) ) {
		$style = '--aj-promo-accent:' . esc_attr( $accent ) . ';';
	}

	$media = '';
	if ( $image_url !== '' ) {
		$media = '<div class="aj-promo-split__media"><img src="' . esc_url( $image_url ) . '" alt="" loading="lazy" decoding="async" width="800" height="520"></div>';
	} else {
		$media = '<div class="aj-promo-split__media"><span class="aj-promo-split__fallback" aria-hidden="true"></span></div>';
	}

	$title_inner = '';
	if ( $title !== '' ) {
		if ( $conflict && $link_url !== '' ) {
			$title_inner = '<a class="aj-promo-split__title aj-promo-split__title--link" href="' . esc_url( $link_url ) . '"' . ( $link_target === '_blank' ? ' target="_blank" rel="' . esc_attr( $rel ) . '"' : '' ) . '>' . esc_html( $title ) . '</a>';
		} else {
			$title_inner = '<span class="aj-promo-split__title">' . esc_html( $title ) . '</span>';
		}
	}

	$desc = $subtitle !== '' ? '<p class="aj-promo-split__desc">' . esc_html( $subtitle ) . '</p>' : '';

	$btn = '';
	if ( $show_btn ) {
		$btn = '<span class="aj-promo-split__btn-wrap"><a class="aj-promo-split__btn" href="' . esc_url( $button_url ) . '"' . ( $link_target === '_blank' ? ' target="_blank" rel="' . esc_attr( $rel ) . '"' : '' ) . '>' . esc_html( $button_text ) . '</a></span>';
	} elseif ( $btn_on && $button_text !== '' ) {
		$btn = '<span class="aj-promo-split__btn-wrap"><span class="aj-promo-split__btn aj-promo-split__btn--text">' . esc_html( $button_text ) . '</span></span>';
	}

	$body = '<div class="aj-promo-split__body">' . $media . '<div class="aj-promo-split__scrim" aria-hidden="true"></div><div class="aj-promo-split__content">' . $title_inner . $desc . $btn . '</div></div>';

	if ( $wrap_link ) {
		return '<a class="aj-promo-split__featured-surface aj-promo-split__featured-surface--link" style="' . esc_attr( $style ) . '" href="' . esc_url( $link_url ) . '"' . ( $link_target === '_blank' ? ' target="_blank" rel="' . esc_attr( $rel ) . '"' : '' ) . '>' . $body . '</a>';
	}

	return '<div class="aj-promo-split__featured-surface" style="' . esc_attr( $style ) . '" role="region">' . $body . '</div>';
};

$featured_slide = isset( $slides_payload[ $def_idx ] ) ? $slides_payload[ $def_idx ] : $slides_payload[0];
?>
<section class="aj-promos aj-promos--split" id="aj-promos" aria-labelledby="<?php echo esc_attr( $uid ); ?>-heading">
	<div class="aj-container">
		<div class="aj-promos__frame aj-promos__frame--split">
			<div class="aj-section-head aj-section-head--split">
				<h2 class="aj-section-title" id="<?php echo esc_attr( $uid ); ?>-heading"><?php echo esc_html( $section_title ); ?></h2>
				<?php if ( $arrows && count( $items ) > 1 ) : ?>
					<div class="aj-promo-split__arrows" role="group" aria-label="<?php esc_attr_e( 'Navigation', 'ajinsafro-traveler-home' ); ?>">
						<button type="button" class="aj-section-arrow aj-promo-split__prev" aria-controls="<?php echo esc_attr( $uid ); ?>-root" aria-label="<?php esc_attr_e( 'Précédent', 'ajinsafro-traveler-home' ); ?>">
							<i class="fas fa-angle-left" aria-hidden="true"></i>
						</button>
						<button type="button" class="aj-section-arrow aj-promo-split__next" aria-controls="<?php echo esc_attr( $uid ); ?>-root" aria-label="<?php esc_attr_e( 'Suivant', 'ajinsafro-traveler-home' ); ?>">
							<i class="fas fa-angle-right" aria-hidden="true"></i>
						</button>
					</div>
				<?php endif; ?>
			</div>

			<div
				class="aj-promo-split<?php echo count( $items ) < 2 ? ' aj-promo-split--single' : ''; ?>"
				id="<?php echo esc_attr( $uid ); ?>-root"
				data-slides="<?php echo esc_attr( $slides_json ); ?>"
				data-active="<?php echo esc_attr( (string) $def_idx ); ?>"
				data-autoplay="<?php echo $autoplay ? '1' : '0'; ?>"
				data-delay="<?php echo esc_attr( (string) $delay_ms ); ?>"
			>
				<div class="aj-promo-split__inner">
					<div class="aj-promo-split__featured" data-featured>
						<div class="aj-promo-split__featured-inner">
							<?php echo $render_featured( $featured_slide ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
					</div>

					<?php if ( count( $items ) > 1 ) : ?>
					<div class="aj-promo-split__previews" data-previews role="list">
						<?php foreach ( $slides_payload as $sp ) : ?>
							<?php
							$pi = (int) ( $sp['index'] ?? 0 );
							if ( $pi === (int) $def_idx ) {
								continue;
							}
							$pimg = (string) ( $sp['image_url'] ?? '' );
							$ptitle = (string) ( $sp['title'] ?? '' );
							$paccent = (string) ( $sp['accent_color'] ?? '' );
							$pv_style = '';
							if ( $paccent !== '' && preg_match( '/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $paccent ) ) {
								$pv_style = '--preview-accent:' . esc_attr( $paccent ) . ';';
							}
							?>
							<button type="button" class="aj-promo-split__preview" style="<?php echo esc_attr( $pv_style ); ?>" data-go-index="<?php echo esc_attr( (string) $pi ); ?>" role="listitem" aria-label="<?php echo esc_attr( $ptitle !== '' ? $ptitle : __( 'Voir cette offre', 'ajinsafro-traveler-home' ) ); ?>">
								<span class="aj-promo-split__preview-thumb" aria-hidden="true">
									<?php if ( $pimg !== '' ) : ?>
										<img src="<?php echo esc_url( $pimg ); ?>" alt="" loading="lazy" decoding="async" width="128" height="420">
									<?php else : ?>
										<span class="aj-promo-split__preview-fallback"></span>
									<?php endif; ?>
								</span>
								<span class="aj-promo-split__preview-label"><?php echo esc_html( $ptitle ); ?></span>
							</button>
						<?php endforeach; ?>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</section>
