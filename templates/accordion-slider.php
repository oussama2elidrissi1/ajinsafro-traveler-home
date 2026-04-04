<?php
/**
 * Accordion promo slider — integrated shortcode output.
 * Visual & behavior match reference Slide(3).html; dimensions scaled for homepage.
 *
 * @package Ajinsafro_Traveler_Home
 *
 * Shortcode: [ajinsafro_slider]
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ajih_base = AJINSAFRO_HOME_URL . 'assets/img/';

/**
 * Slides config: same content, images, gradients, CTAs as HTML reference.
 * Slide 5 is text placeholder only (reference has no image).
 */
$ajih_slides = array(
	array(
		'title'       => 'PROGRAMME DE FIDÉLITÉ',
		'image'       => $ajih_base . 'slide-1.png',
		'pane_bg'     => '#e5e7eb',
		'tab_bg'      => 'linear-gradient(to bottom, #00a3e0, #0081bc)',
		'overlay'     => 'linear-gradient(to bottom, rgba(0, 163, 224, 0.1), rgba(0, 129, 188, 0.1))',
		'cta'         => array(
			'text'   => "S'inscrire !",
			'url'    => 'https://www.ajinsafro.ma/fidelite',
			'target' => '_top',
			'kind'   => 'orange',
			'wrap'   => 'default',
		),
	),
	array(
		'title'   => 'GROUP DEALS TRAVEL',
		'image'   => $ajih_base . 'slide-2.png',
		'pane_bg' => '#d1d5db',
		'tab_bg'  => 'linear-gradient(to bottom, #4ade80, #16a34a)',
		'overlay' => 'linear-gradient(to bottom, rgba(74, 222, 128, 0.05), rgba(22, 163, 74, 0.05))',
		'cta'     => null,
	),
	array(
		'title'   => "L'7AJZ BKRI B'DHAB MCHRI",
		'image'   => $ajih_base . 'slide-3.png',
		'pane_bg' => '#e5e7eb',
		'tab_bg'  => 'linear-gradient(to bottom, #1b5c8c, #0E3A5A)',
		'overlay' => 'linear-gradient(to bottom, rgba(27, 92, 140, 0.05), rgba(14, 58, 90, 0.05))',
		'cta'     => array(
			'text'   => 'احجز الآن',
			'url'    => '#',
			'target' => '_self',
			'kind'   => 'arabic',
			'wrap'   => 'arabic',
		),
	),
	array(
		'title'   => 'Programme BZTAM eSFAR',
		'image'   => $ajih_base . 'slide-4.png',
		'pane_bg' => '#d1d5db',
		'tab_bg'  => 'linear-gradient(to bottom, #facc15, #f97316)',
		'overlay' => 'linear-gradient(to bottom, rgba(250, 204, 21, 0.1), rgba(249, 115, 22, 0.1))',
		'cta'     => null,
	),
	array(
		'title'   => 'IMPORTANT UPDATES',
		'image'   => '',
		'pane_bg' => '#e5e7eb',
		'tab_bg'  => 'linear-gradient(to bottom, #ef4444, #b91c1c)',
		'overlay' => '',
		'cta'     => null,
	),
);

$ajih_slides = apply_filters( 'ajinsafro_slider_slides', $ajih_slides );

if ( empty( $ajih_slides ) || ! is_array( $ajih_slides ) ) {
	return;
}
?>
<div id="ajih-promo-accordion" class="ajih-promo-accordion" role="region" aria-label="<?php echo esc_attr__( 'Promotions AjiNsafro', 'ajinsafro-traveler-home' ); ?>">
<?php
foreach ( array_values( $ajih_slides ) as $ajih_i => $ajih_s ) :
	$ajih_title = isset( $ajih_s['title'] ) ? (string) $ajih_s['title'] : '';
	$ajih_image = isset( $ajih_s['image'] ) ? (string) $ajih_s['image'] : '';
	$ajih_pane  = isset( $ajih_s['pane_bg'] ) ? (string) $ajih_s['pane_bg'] : '#e5e7eb';
	$ajih_tab   = isset( $ajih_s['tab_bg'] ) ? (string) $ajih_s['tab_bg'] : '';
	$ajih_over  = isset( $ajih_s['overlay'] ) ? (string) $ajih_s['overlay'] : '';
	$ajih_cta   = isset( $ajih_s['cta'] ) && is_array( $ajih_s['cta'] ) ? $ajih_s['cta'] : null;
	?>
	<div class="ajih-acc__slide"
		data-ajih-index="<?php echo esc_attr( (string) $ajih_i ); ?>"
		role="group"
		aria-roledescription="<?php echo esc_attr__( 'slide', 'ajinsafro-traveler-home' ); ?>"
		aria-label="<?php echo esc_attr( $ajih_title ); ?>">
		<div class="ajih-acc__tab" style="<?php echo $ajih_tab ? 'background:' . esc_attr( $ajih_tab ) : ''; ?>">
			<span class="ajih-acc__tabtext"><?php echo esc_html( $ajih_title ); ?></span>
		</div>
		<div class="ajih-acc__pane" style="<?php echo 'background-color:' . esc_attr( $ajih_pane ); ?>">
			<?php if ( $ajih_image !== '' ) : ?>
				<img class="ajih-acc__img"
					src="<?php echo esc_url( $ajih_image ); ?>"
					alt="<?php echo esc_attr( $ajih_title ); ?>"
					loading="<?php echo 0 === $ajih_i ? 'eager' : 'lazy'; ?>"
					<?php echo 0 === $ajih_i ? 'fetchpriority="high"' : ''; ?> />
			<?php else : ?>
				<div class="ajih-acc__placeholder" aria-hidden="true">800×800</div>
			<?php endif; ?>
			<?php if ( $ajih_over !== '' ) : ?>
				<div class="ajih-acc__overlay" style="background:<?php echo esc_attr( $ajih_over ); ?>"></div>
			<?php endif; ?>
			<?php
			if ( $ajih_cta && ! empty( $ajih_cta['text'] ) ) :
				$ajih_href   = isset( $ajih_cta['url'] ) ? (string) $ajih_cta['url'] : '#';
				$ajih_href_o = ( '#' === $ajih_href ) ? '#' : esc_url( $ajih_href );
				$ajih_tgt    = isset( $ajih_cta['target'] ) ? (string) $ajih_cta['target'] : '_self';
				$ajih_kind = isset( $ajih_cta['kind'] ) ? (string) $ajih_cta['kind'] : 'orange';
				$ajih_wrap = isset( $ajih_cta['wrap'] ) ? (string) $ajih_cta['wrap'] : 'default';
				?>
				<div class="ajih-acc__cta-wrap ajih-acc__cta-wrap--<?php echo esc_attr( $ajih_wrap ); ?>">
					<a class="ajih-acc__cta ajih-acc__cta--<?php echo esc_attr( $ajih_kind ); ?>"
						href="<?php echo $ajih_href_o; ?>"
						target="<?php echo esc_attr( $ajih_tgt ); ?>"
						<?php echo ( '_blank' === $ajih_tgt ) ? 'rel="noopener noreferrer"' : ''; ?>
						onclick="event.stopPropagation();">
						<?php echo esc_html( $ajih_cta['text'] ); ?>
					</a>
				</div>
			<?php endif; ?>
		</div>
	</div>
<?php endforeach; ?>
</div>
