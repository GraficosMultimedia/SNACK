<?php
declare(strict_types=1);
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/functions.php';

$pdo = db();
$name = config_value($pdo,'nombre_negocio',APP_NAME);
$logo = config_value($pdo,'logo','');
$message = config_value($pdo,'mensaje_menu','¡Gracias por tu preferencia! ✨');
$address = config_value($pdo,'direccion','Frontera Piedras Negras #55 a col. Progreso');
$phone = config_value($pdo,'telefono','6271104930');
$whatsapp = config_value($pdo,'whatsapp','526271104930');
$instagram = config_value($pdo,'instagram','@snackliciosos');

$cats = $pdo->query('SELECT id,nombre FROM categorias WHERE activa=1 ORDER BY orden,id')->fetchAll();
$products = $pdo->query('SELECT p.id,p.categoria_id,p.nombre,p.descripcion,p.imagen,p.precio,p.permite_toppings,p.max_toppings,p.toppings_gratis FROM productos p JOIN categorias c ON c.id=p.categoria_id WHERE p.activo=1 AND c.activa=1 ORDER BY c.orden,p.orden,p.id')->fetchAll();
$pt = $pdo->query('SELECT producto_id,topping_id FROM producto_toppings ORDER BY producto_id,topping_id')->fetchAll();
$allowed=[];
foreach($pt as $row){ $allowed[(int)$row['producto_id']][]=(int)$row['topping_id']; }
foreach($products as &$pr){ $pr['toppings_ids']=$allowed[(int)$pr['id']]??[]; }
unset($pr);
$grouped=[];
foreach($cats as $c){ $grouped[(int)$c['id']]=['id'=>(int)$c['id'],'nombre'=>$c['nombre'],'productos'=>[]]; }
foreach($products as $p){ $cid=(int)$p['categoria_id']; if(isset($grouped[$cid])) $grouped[$cid]['productos'][]=$p; }
$grouped=array_values($grouped);
$toppings=$pdo->query('SELECT id,nombre,precio FROM toppings WHERE activo=1 ORDER BY orden,id')->fetchAll();
function wa_url(string $phone): string { $digits=preg_replace('/\D+/','',$phone)??''; return $digits?'https://wa.me/'.$digits:''; }
$wa=wa_url($whatsapp ?: $phone);
$menuData=['categories'=>$grouped,'toppings'=>$toppings,'business'=>['nombre'=>$name,'direccion'=>$address,'telefono'=>$phone,'whatsapp'=>$whatsapp,'instagram'=>$instagram,'mensaje'=>$message,'logo'=>$logo]];
?><!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="theme-color" content="#ed0875">
<meta name="description" content="Menú digital de <?=h($name)?>">
<title><?=h($name)?> · Menú</title>
<link rel="stylesheet" href="menu.css?v=20">
</head>
<body>
<div class="topCurve"></div>

<header class="hero" id="inicio">
  <div class="heroImage" aria-hidden="true"></div>
  <div class="heroOverlay"></div>
  <div class="heroContent">
    <div class="heroLogoWrap">
      <?php if($logo): ?><img class="heroLogo" id="businessLogo" src="<?=h($logo)?>" alt="<?=h($name)?>">
      <?php else: ?><div class="heroLogoFallback" id="businessLogoFallback">🍓</div><?php endif; ?>
    </div>
    <div class="heroCopy">
      <span class="eyebrow">ANTOJITOS QUE SACAN SONRISAS</span>
      <h1 id="businessName"><?=h($name)?></h1>
      <p class="heroTagline">Más que snacks, son buenos momentos 💗</p>
      <p class="heroMessage" id="thanks"><?=h($message)?></p>
      <div class="heroData">
        <span id="businessAddress">📍 <?=h($address)?></span>
        <?php if($phone): ?><span id="businessPhone">☎ <?=h($phone)?></span><?php endif; ?>
      </div>
      <?php if($wa): ?><a class="heroCta" href="<?=h($wa)?>" target="_blank" rel="noopener">💬 Pedir por WhatsApp</a><?php endif; ?>
    </div>
  </div>
</header>

<section class="quickBenefits" aria-label="Beneficios">
  <div><b>🚚</b><span>Pedidos al instante<br><small>por WhatsApp</small></span></div>
  <div><b>♡</b><span>Calidad en cada<br><small>bocado</small></span></div>
  <div><b>✦</b><span>Antojitos que<br><small>enamoran</small></span></div>
  <div><b>🍓</b><span>Hechos para<br><small>disfrutar</small></span></div>
</section>

<section class="menuTools" aria-label="Buscar y navegar">
  <div class="searchBox"><span>⌕</span><input id="menuSearch" type="search" placeholder="¿Qué se te antoja hoy?" autocomplete="off"></div>
  <nav class="categories" id="categories" aria-label="Categorías">
    <a class="cat active" href="#menu" data-cat="all">▦ <span>Todos</span></a>
    <?php foreach($grouped as $c): ?><a class="cat" href="#cat-<?=$c['id']?>" data-cat="<?=$c['id']?>"><span class="catIcon">✦</span><span><?=h($c['nombre'])?></span></a><?php endforeach; ?>
  </nav>
</section>

<main class="menuShell" id="menu">
  <div class="menuHeading"><div><span>✨ NUESTRO MENÚ</span><h2>Elige tu antojo</h2></div><button class="antojoMini" id="openCravingTop" type="button">💗 ¿De qué tienes antojo?</button></div>
  <div class="menuGrid" id="menuGrid">
  <?php foreach($grouped as $c): ?>
    <section class="category" id="cat-<?=$c['id']?>" data-category-id="<?=$c['id']?>" data-category-name="<?=h($c['nombre'])?>">
      <div class="categoryTitle"><h2><?=h($c['nombre'])?></h2><span><?=count($c['productos'])?> opciones</span></div>
      <div class="items">
      <?php foreach($c['productos'] as $p):
        $productWa = wa_url($whatsapp ?: $phone);
        $messageWa = 'Hola, quiero pedir '.$p['nombre'].' ('.money($p['precio']).')';
        $productWaUrl = $productWa ? $productWa.'?text='.rawurlencode($messageWa) : '#';
      ?>
        <article class="item" data-product-id="<?=$p['id']?>" data-product-name="<?=h($p['nombre'])?>" data-category-id="<?=$c['id']?>" data-category-name="<?=h($c['nombre'])?>" data-search="<?=h(strtolower($p['nombre'].' '.$p['descripcion']))?>">
          <?php if(!empty($p['imagen'])): ?><div class="imageWrap"><img class="itemImage" src="<?=h($p['imagen'])?>" alt="<?=h($p['nombre'])?>" loading="lazy"></div><?php endif; ?>
          <div class="itemInfo">
            <h3><?=h($p['nombre'])?></h3>
            <?php if($p['descripcion']): ?><p><?=h($p['descripcion'])?></p><?php endif; ?>
            <?php if((int)($p['toppings_gratis']??0)>0): ?><p class="includedToppings">Incluye <?=((int)$p['toppings_gratis'])?> topping<?=((int)$p['toppings_gratis'])===1?'':'s'?> gratis</p><?php endif; ?>
          </div>
          <div class="itemActions"><span class="price"><?=money($p['precio'])?></span><?php if($productWaUrl!=='#'): ?><a class="productWa" href="<?=h($productWaUrl)?>" target="_blank" rel="noopener" aria-label="Pedir <?=h($p['nombre'])?> por WhatsApp">⌕<span>WhatsApp</span></a><?php endif; ?></div>
        </article>
      <?php endforeach; ?>
      </div>
    </section>
  <?php endforeach; ?>
  <section class="toppingsSection"><div class="categoryTitle"><h2>Complementos / toppings</h2><span>Hazlo a tu manera</span></div><div class="toppingList"><?php foreach($toppings as $t): ?><div><span><?=h($t['nombre'])?></span><b>+<?=money($t['precio'])?></b></div><?php endforeach; ?></div></section>
  </div>
  <div class="noResults" id="noResults" hidden>😋 No encontramos ese antojo. Prueba con otra palabra.</div>
</main>

<section class="afterMenuCta"><span>💗</span><div><strong>¿Ya encontraste tu antojo?</strong><small>Haz tu pedido en segundos por WhatsApp.</small></div><?php if($wa): ?><a href="<?=h($wa)?>" target="_blank" rel="noopener">Pedir ahora →</a><?php endif; ?></section>

<footer>
  <div class="footerInner">
    <div class="footerBrand"><strong id="footerName"><?=h($name)?></strong><span>Tu antojo, a tu manera. 💗</span></div>
    <div class="footerData"><span id="footerAddress"><?=h($address)?></span><?php if($phone): ?><a id="footerPhone" href="tel:<?=h(preg_replace('/\D+/','',$phone)??'')?>">☎ <?=h($phone)?></a><?php endif; ?><?php if($instagram): ?><span id="footerInstagram">📷 <?=h($instagram)?></span><?php endif; ?></div>
    <?php if($wa): ?><a id="whatsappBtn" class="whatsappBtn" href="<?=h($wa)?>" target="_blank" rel="noopener">💬 WhatsApp</a><?php endif; ?>
    <div class="syncLine" id="syncStatus">Catálogo actualizado</div>
  </div>
</footer>

<div class="cravingBackdrop" id="cravingBackdrop" hidden></div>
<section class="cravingModal" id="cravingModal" role="dialog" aria-modal="true" aria-labelledby="cravingTitle" hidden>
  <button class="modalClose" id="closeCraving" type="button" aria-label="Cerrar">×</button>
  <div class="modalSpark">💗</div>
  <p class="modalKicker">UN ANTOJO SIEMPRE ES BUENA IDEA</p>
  <h2 id="cravingTitle">¿De qué tienes<br><em>antojo hoy?</em> ♡</h2>
  <p class="modalIntro">Te escogimos algunas delicias al azar. Si una te guiña el ojo, tócala.</p>
  <div class="cravingGrid" id="cravingGrid"></div>
  <button class="viewAllBtn" id="viewAllCravings" type="button">Ver todo el menú →</button>
</section>

<button class="floatingCraving" id="floatingCraving" type="button" aria-label="Abrir sugerencias de antojos"><span>💗</span><b>¿De qué tienes antojo?</b><i>⌃</i></button>

<script>window.MENU_DATA=<?=json_encode($menuData,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)?>;</script>
<script src="menu.js?v=20"></script>
</body></html>
