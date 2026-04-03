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
		'cta'      => array( 'label' => "S'INSCRIRE !", 'url' => 'https://www.ajinsafro.ma/fidelite', 'arabic' => false ),
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
		'cta'      => array( 'label' => 'احجز الآن', 'url' => '#', 'arabic' => true ),
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

<!-- STYLES INLINE : bypass total du thème Traveler -->
<style>
#aji-wrap{width:100%;margin:0;padding:0;box-sizing:border-box}
#aji-wrap .aji-label{font-family:Poppins,sans-serif;font-weight:700;font-size:14px;color:#0081bc;margin-bottom:10px;display:block}
#aji-sl{display:flex;flex-direction:row;width:100%;height:180px;overflow:hidden;border-radius:8px;box-shadow:0 4px 20px rgba(0,0,0,.15);margin:0;padding:0;box-sizing:border-box}
#aji-sl .as{display:flex;flex-direction:row;height:100%;flex:0 0 38px;min-width:38px;overflow:hidden;cursor:pointer;transition:flex .6s cubic-bezier(.77,0,.18,1)}
#aji-sl .as.on{flex:1 1 auto}
#aji-sl .tb{width:38px;min-width:38px;max-width:38px;height:100%;display:flex;align-items:center;justify-content:center;position:relative;flex-shrink:0;overflow:hidden;z-index:2}
#aji-sl .tl{position:absolute;left:50%;top:50%;transform:translate(-50%,-50%) rotate(-90deg);white-space:nowrap;font-family:Poppins,sans-serif;font-weight:700;color:#fff;text-transform:uppercase;letter-spacing:.12em;font-size:8px;pointer-events:none}
#aji-sl .sc{position:relative;height:100%;flex:1 1 auto;overflow:hidden;opacity:0;transition:opacity .4s ease;min-width:0;background:#b8cdd8}
#aji-sl .as.on .sc{opacity:1}
#aji-sl .sc img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:center;display:block}
#aji-sl .sc .ph{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:#9ca3af;font-weight:700;font-size:1.4rem;font-family:Poppins,sans-serif}
#aji-sl .sc .btn{position:absolute;bottom:14%;right:5%;display:inline-block;background:#ff7200;color:#fff;padding:8px 24px;border-radius:50px;font-family:Poppins,sans-serif;font-weight:700;font-size:10px;text-transform:uppercase;text-decoration:none;letter-spacing:.08em;z-index:99;box-shadow:0 4px 16px rgba(255,114,0,.5);border:none;cursor:pointer;animation:apulse 2s infinite ease-in-out}
#aji-sl .sc .btn.ar{background:#fff;color:#0081bc;font-family:Cairo,sans-serif;font-weight:900;font-size:13px;text-transform:none;letter-spacing:0;right:15%}
@keyframes apulse{0%,100%{transform:scale(1)}50%{transform:scale(1.06)}}
</style>

<div id="aji-wrap">
  <span class="aji-label">Explorez plus, voyagez mieux avec AjiNsafro</span>
  <div id="aji-sl">
	<?php foreach ( $slides as $i => $s ) : ?>
	<div class="as" data-i="<?php echo esc_attr( (string) $i ); ?>">
	  <div class="tb" style="background:<?php echo esc_attr( $s['gradient'] ); ?>">
		<span class="tl"><?php echo esc_html( $s['label'] ); ?></span>
	  </div>
	  <div class="sc">
		<?php if ( ! empty( $s['image'] ) ) : ?>
		  <img src="<?php echo esc_url( $s['image'] ); ?>" alt="<?php echo esc_attr( $s['label'] ); ?>" loading="lazy">
		<?php else : ?>
		  <div class="ph">800x800</div>
		<?php endif; ?>
		<?php if ( ! empty( $s['cta'] ) ) : ?>
			<?php
			$aji_cta_u = isset( $s['cta']['url'] ) ? (string) $s['cta']['url'] : '';
			$aji_cta_h = ( '#' === $aji_cta_u ) ? '#' : esc_url( $aji_cta_u );
			?>
		  <a href="<?php echo $aji_cta_h; ?>"
			 class="btn<?php echo ! empty( $s['cta']['arabic'] ) ? ' ar' : ''; ?>"
			 target="_blank" rel="noopener noreferrer"
			 onclick="event.stopPropagation()">
			<?php echo esc_html( $s['cta']['label'] ); ?>
		  </a>
		<?php endif; ?>
	  </div>
	</div>
	<?php endforeach; ?>
  </div>
</div>

<script>
(function(){
  var sl=document.getElementById('aji-sl');
  if(!sl)return;
  var items=sl.querySelectorAll('.as');
  var cur=0,dir=1,tmr=null;
  function go(i){
    cur=i;
    items.forEach(function(el,j){
      j===i?el.classList.add('on'):el.classList.remove('on');
    });
  }
  function auto(){
    clearInterval(tmr);
    tmr=setInterval(function(){
      if(cur>=items.length-1)dir=-1;
      else if(cur<=0)dir=1;
      go(cur+dir);
    },5000);
  }
  items.forEach(function(el){
    el.addEventListener('click',function(){
      go(parseInt(el.getAttribute('data-i'),10));
    });
  });
  sl.addEventListener('mouseenter',function(){clearInterval(tmr);});
  sl.addEventListener('mouseleave',auto);
  go(0);
  auto();
})();
</script>
