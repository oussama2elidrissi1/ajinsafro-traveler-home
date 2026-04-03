<?php
if ( ! defined( 'ABSPATH' ) ) exit;
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
<style>
#aji-root,#aji-root *{box-sizing:border-box;margin:0;padding:0;font-family:inherit}
#aji-root{width:100%;display:block;margin-left:0!important;margin-right:0!important;padding-left:0!important;padding-right:0!important}
.aji-label{font-family:'Poppins',sans-serif!important;font-weight:700!important;font-size:14px!important;color:#0081bc!important;margin-bottom:10px!important;display:block!important;padding:0!important}
#aji-sl{display:flex!important;flex-direction:row!important;width:100%!important;height:160px!important;overflow:hidden!important;border-radius:8px!important;box-shadow:0 4px 20px rgba(0,0,0,.15)!important;padding:0!important;margin:0!important}
#aji-sl .as{display:flex!important;flex-direction:row!important;height:100%!important;flex:0 0 36px!important;min-width:36px!important;overflow:hidden!important;cursor:pointer!important;transition:flex .6s cubic-bezier(.77,0,.18,1)!important}
#aji-sl .as.on{flex:1 1 auto!important}
#aji-sl .tb{width:36px!important;min-width:36px!important;max-width:36px!important;height:100%!important;display:flex!important;align-items:center!important;justify-content:center!important;position:relative!important;flex-shrink:0!important;overflow:hidden!important;z-index:2!important}
#aji-sl .tl{position:absolute!important;left:50%!important;top:50%!important;transform:translate(-50%,-50%) rotate(-90deg)!important;white-space:nowrap!important;font-family:'Poppins',sans-serif!important;font-weight:700!important;color:#fff!important;text-transform:uppercase!important;letter-spacing:.12em!important;font-size:8px!important;pointer-events:none!important;user-select:none!important;display:block!important}
#aji-sl .sc{position:relative!important;height:100%!important;flex:1 1 auto!important;overflow:hidden!important;opacity:0!important;transition:opacity .4s ease!important;min-width:0!important;background:#b8cdd8!important}
#aji-sl .as.on .sc{opacity:1!important}
#aji-sl .sc img{position:absolute!important;top:0!important;left:0!important;right:0!important;bottom:0!important;width:100%!important;height:100%!important;object-fit:cover!important;object-position:center top!important;display:block!important;border:none!important;border-radius:0!important;box-shadow:none!important}
#aji-sl .ph{position:absolute!important;inset:0!important;display:flex!important;align-items:center!important;justify-content:center!important;color:#9ca3af!important;font-weight:700!important;font-size:1.2rem!important}
#aji-root #aji-sl .sc a.btn{position:absolute!important;bottom:12%!important;right:4%!important;display:inline-block!important;background:#ff7200!important;color:#ffffff!important;padding:7px 22px!important;border-radius:50px!important;font-family:'Poppins',sans-serif!important;font-weight:700!important;font-size:10px!important;text-transform:uppercase!important;text-decoration:none!important;letter-spacing:.08em!important;z-index:999!important;box-shadow:0 4px 14px rgba(255,114,0,.55)!important;border:none!important;cursor:pointer!important;line-height:1!important;animation:apulse 2s infinite ease-in-out!important;background-image:none!important;opacity:1!important;visibility:visible!important;pointer-events:all!important}
#aji-root #aji-sl .sc a.btn:hover{background:#e06200!important;color:#fff!important;text-decoration:none!important}
#aji-root #aji-sl .sc a.btn.ar{background:#ffffff!important;color:#0081bc!important;font-family:'Cairo',sans-serif!important;font-weight:900!important;font-size:13px!important;text-transform:none!important;letter-spacing:0!important;right:14%!important}
@keyframes apulse{0%,100%{transform:scale(1)}50%{transform:scale(1.06)}}
</style>

<div id="aji-root">
  <span class="aji-label">Explorez plus, voyagez mieux avec AjiNsafro</span>
  <div id="aji-sl">
    <?php foreach($slides as $i => $s): ?>
    <div class="as" data-i="<?php echo $i; ?>">
      <div class="tb" style="background:<?php echo esc_attr($s['gradient']); ?>">
        <span class="tl"><?php echo esc_html($s['label']); ?></span>
      </div>
      <div class="sc">
        <?php if(!empty($s['image'])): ?>
          <img src="<?php echo esc_url($s['image']); ?>"
               alt="<?php echo esc_attr($s['label']); ?>"
               loading="lazy">
        <?php else: ?>
          <div class="ph">800&times;800</div>
        <?php endif; ?>
        <?php if(!empty($s['cta'])):
          $href = ( '#' === $s['cta']['url'] ) ? '#' : esc_url($s['cta']['url']);
        ?>
          <a href="<?php echo $href; ?>"
             class="btn<?php echo !empty($s['cta']['arabic']) ? ' ar' : ''; ?>"
             target="_blank"
             rel="noopener noreferrer"
             onclick="event.stopPropagation()">
            <?php echo esc_html($s['cta']['label']); ?>
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
