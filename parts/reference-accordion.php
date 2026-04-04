<?php
/**
 * Part: standalone homepage accordion copied from the approved reference slider.
 *
 * This block is intentionally independent from the existing promotions section.
 *
 * @package AjinsafroTravelerHome
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$base_url = AJTH_URL . 'assets/img/';

$slides = array(
	array(
		'title'   => "PROGRAMME DE FID\u{00C9}LIT\u{00C9}",
		'image'   => $base_url . 'slide-1.png',
		'pane_bg' => '#e5e7eb',
		'tab_bg'  => 'linear-gradient(to bottom, #00a3e0, #0081bc)',
		'overlay' => 'linear-gradient(to bottom, rgba(0, 163, 224, 0.10), rgba(0, 129, 188, 0.10))',
		'cta'     => array(
			'text'   => "S'inscrire !",
			'url'    => 'https://www.ajinsafro.ma/fidelite',
			'target' => '_top',
			'kind'   => 'orange',
			'wrap'   => 'default',
		),
	),
	array(
		'title'   => 'GROUP DEALS TRAVEL',
		'image'   => $base_url . 'slide-2.png',
		'pane_bg' => '#d1d5db',
		'tab_bg'  => 'linear-gradient(to bottom, #4ade80, #16a34a)',
		'overlay' => 'linear-gradient(to bottom, rgba(74, 222, 128, 0.05), rgba(22, 163, 74, 0.05))',
		'cta'     => null,
	),
	array(
		'title'   => "L'7AJZ BKRI B'DHAB MCHRI",
		'image'   => $base_url . 'slide-3.png',
		'pane_bg' => '#e5e7eb',
		'tab_bg'  => 'linear-gradient(to bottom, #1b5c8c, #0e3a5a)',
		'overlay' => 'linear-gradient(to bottom, rgba(27, 92, 140, 0.05), rgba(14, 58, 90, 0.05))',
		'cta'     => array(
			'text'   => "\u{0627}\u{062D}\u{062C}\u{0632} \u{0627}\u{0644}\u{0622}\u{0646}",
			'url'    => '#',
			'target' => '_self',
			'kind'   => 'arabic',
			'wrap'   => 'arabic',
		),
	),
	array(
		'title'   => 'Programme BZTAM eSFAR',
		'image'   => $base_url . 'slide-4.png',
		'pane_bg' => '#d1d5db',
		'tab_bg'  => 'linear-gradient(to bottom, #facc15, #f97316)',
		'overlay' => 'linear-gradient(to bottom, rgba(250, 204, 21, 0.10), rgba(249, 115, 22, 0.10))',
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

$slides = apply_filters( 'ajth_reference_accordion_slides', $slides );

if ( empty( $slides ) || ! is_array( $slides ) ) {
	return;
}
?>
<section class="aj-section ajha-ref-section" id="aj-reference-accordion">
	<div class="aj-container">
		<div
			class="ajha-ref-accordion"
			data-ajha-ref-accordion="1"
			data-delay="5000"
			data-start-index="0"
			role="region"
			aria-label="<?php echo esc_attr__( 'Promotions AjiNsafro', 'ajinsafro-traveler-home' ); ?>"
		>
			<?php foreach ( array_values( $slides ) as $index => $slide ) : ?>
				<?php
				$title = isset( $slide['title'] ) ? (string) $slide['title'] : '';
				$image = isset( $slide['image'] ) ? (string) $slide['image'] : '';
				$pane_bg = isset( $slide['pane_bg'] ) ? (string) $slide['pane_bg'] : '#e5e7eb';
				$tab_bg = isset( $slide['tab_bg'] ) ? (string) $slide['tab_bg'] : '';
				$overlay = isset( $slide['overlay'] ) ? (string) $slide['overlay'] : '';
				$cta = isset( $slide['cta'] ) && is_array( $slide['cta'] ) ? $slide['cta'] : null;
				?>
				<div
					class="ajha-ref-accordion__slide"
					data-index="<?php echo esc_attr( (string) $index ); ?>"
					role="button"
					tabindex="0"
					aria-expanded="<?php echo 0 === $index ? 'true' : 'false'; ?>"
					aria-label="<?php echo esc_attr( $title ); ?>"
				>
					<div class="ajha-ref-accordion__tab" style="<?php echo $tab_bg ? 'background:' . esc_attr( $tab_bg ) . ';' : ''; ?>">
						<span class="ajha-ref-accordion__tabtext"><?php echo esc_html( $title ); ?></span>
					</div>
					<div class="ajha-ref-accordion__panel" style="<?php echo 'background-color:' . esc_attr( $pane_bg ) . ';'; ?>">
						<?php if ( '' !== $image ) : ?>
							<img
								class="ajha-ref-accordion__img"
								src="<?php echo esc_url( $image ); ?>"
								alt="<?php echo esc_attr( $title ); ?>"
								loading="<?php echo 0 === $index ? 'eager' : 'lazy'; ?>"
								decoding="async"
								<?php echo 0 === $index ? 'fetchpriority="high"' : ''; ?>
							>
						<?php else : ?>
							<div class="ajha-ref-accordion__placeholder" aria-hidden="true">800x800</div>
						<?php endif; ?>

						<?php if ( '' !== $overlay ) : ?>
							<div class="ajha-ref-accordion__overlay" style="background:<?php echo esc_attr( $overlay ); ?>;"></div>
						<?php endif; ?>

						<?php if ( $cta && ! empty( $cta['text'] ) ) : ?>
							<?php
							$cta_url = isset( $cta['url'] ) ? (string) $cta['url'] : '#';
							$cta_target = isset( $cta['target'] ) ? (string) $cta['target'] : '_self';
							$cta_kind = isset( $cta['kind'] ) ? (string) $cta['kind'] : 'orange';
							$cta_wrap = isset( $cta['wrap'] ) ? (string) $cta['wrap'] : 'default';
							?>
							<div class="ajha-ref-accordion__cta-wrap ajha-ref-accordion__cta-wrap--<?php echo esc_attr( $cta_wrap ); ?>">
								<a
									class="ajha-ref-accordion__cta ajha-ref-accordion__cta--<?php echo esc_attr( $cta_kind ); ?>"
									href="<?php echo '#' === $cta_url ? '#' : esc_url( $cta_url ); ?>"
									target="<?php echo esc_attr( $cta_target ); ?>"
									<?php echo '_blank' === $cta_target ? 'rel="noopener noreferrer"' : ''; ?>
									onclick="event.stopPropagation();"
								>
									<?php echo esc_html( (string) $cta['text'] ); ?>
								</a>
							</div>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
