<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$img = AJINSAFRO_HOME_URL . 'assets/img/';
$slides = array(
	array(
		'label'    => 'PROGRAMME DE FIDÉLITÉ',
		'gradient' => 'linear-gradient(to bottom,#00a3e0,#0081bc)',
		'image'    => $img . 'slide-1.png',
		'cta'      => array( 'label' => "S'INSCRIRE !", 'url' => 'https://www.ajinsafro.ma/fidelite' ),
	),
	array(
		'label'    => 'GROUP DEALS TRAVEL',
		'gradient' => 'linear-gradient(to bottom,#4ade80,#16a34a)',
		'image'    => $img . 'slide-2.png',
		'cta'      => null,
	),
	array(
		'label'    => "L'7AJZ BKRI B'DHAB MCHRI",
		'gradient' => 'linear-gradient(to bottom,#1b5c8c,#0E3A5A)',
		'image'    => $img . 'slide-3.png',
		'cta'      => array( 'label' => 'احجز الآن', 'url' => 'https://www.ajinsafro.ma/voyages', 'arabic' => true ),
	),
	array(
		'label'    => 'PROGRAMME BZTAM ESFAR',
		'gradient' => 'linear-gradient(to bottom,#facc15,#f97316)',
		'image'    => $img . 'slide-4.png',
		'cta'      => null,
	),
	array(
		'label'    => 'IMPORTANT UPDATES',
		'gradient' => 'linear-gradient(to bottom,#ef4444,#b91c1c)',
		'image'    => '',
		'cta'      => null,
	),
);
?>
<p class="aji-section-label">Explorez plus, voyagez mieux avec AjiNsafro</p>
<div id="aji-accordion-slider">
<?php foreach ( $slides as $i => $s ) : ?>
<div class="aji-slide" data-index="<?php echo esc_attr( (string) $i ); ?>" role="button" tabindex="0" aria-label="<?php echo esc_attr( $s['label'] ); ?>">
  <div class="aji-tab-bar" style="background:<?php echo esc_attr( $s['gradient'] ); ?>">
    <span class="aji-tab-label"><?php echo esc_html( $s['label'] ); ?></span>
  </div>
  <div class="aji-slide-content">
	<?php if ( ! empty( $s['image'] ) ) : ?>
	  <img src="<?php echo esc_url( $s['image'] ); ?>" alt="<?php echo esc_attr( $s['label'] ); ?>" loading="lazy">
	<?php else : ?>
	  <div class="aji-placeholder">800x800</div>
	<?php endif; ?>
	<?php if ( ! empty( $s['cta'] ) ) : ?>
	  <a href="<?php echo esc_url( $s['cta']['url'] ); ?>"
		 class="aji-cta<?php echo ! empty( $s['cta']['arabic'] ) ? ' aji-arabic' : ''; ?>"
		 target="_blank" rel="noopener noreferrer" onclick="event.stopPropagation();">
		<?php echo esc_html( $s['cta']['label'] ); ?>
	  </a>
	<?php endif; ?>
  </div>
</div>
<?php endforeach; ?>
</div>
