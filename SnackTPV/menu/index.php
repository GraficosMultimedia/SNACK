<?php
declare(strict_types=1);
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/functions.php';
require_once __DIR__.'/../includes/topping_price.php';

$pdo = db();
$name = config_value($pdo,'nombre_negocio',APP_NAME);
$logo = config_value($pdo,'logo','');
$message = config_value($pdo,'mensaje_menu','¡Gracias por tu preferencia! ✨');
$address = config_value($pdo,'direccion','Frontera Piedras Negras #55 a col. Progreso');
$phone = config_value($pdo,'telefono','6271104930');
$whatsapp = config_value($pdo,'whatsapp','526271104930');
$instagram = config_value($pdo,'instagram','@snackliciosos');
$transferBank = config_value($pdo,'transfer_banco','');
$transferHolder = config_value($pdo,'transfer_titular','');
$transferAccount = config_value($pdo,'transfer_cuenta','');
$transferClabe = config_value($pdo,'transfer_clabe','');
$transferInstructions = config_value($pdo,'transfer_instrucciones','');

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
foreach($toppings as &$t){ $t['precio']=topping_effective_price($pdo,$t); }
unset($t);
function wa_url(string $phone): string { $digits=preg_replace('/\D+/','',$phone)??''; return $digits?'https://wa.me/'.$digits:''; }
$wa=wa_url($whatsapp ?: $phone);
$menuData=['categories'=>$grouped,'toppings'=>$toppings,'business'=>['nombre'=>$name,'direccion'=>$address,'telefono'=>$phone,'whatsapp'=>$whatsapp,'instagram'=>$instagram,'mensaje'=>$message,'logo'=>$logo,'transferencia'=>['banco'=>$transferBank,'titular'=>$transferHolder,'cuenta'=>$transferAccount,'clabe'=>$transferClabe,'instrucciones'=>$transferInstructions]]];
?><!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="theme-color" content="#ed0875">
<meta name="description" content="Menú digital de <?=h($name)?>">
<meta property="og:type" content="website">
<meta property="og:url" content="https://colibriprint.com.mx/SnackTPV/menu/">
<meta property="og:title" content="Snackliciosos · Menú">
<meta property="og:description" content="Antojitos que sacan sonrisas 💗 Descubre nuestro menú y haz tu pedido por WhatsApp.">
<meta property="og:image" content="https://colibriprint.com.mx/SnackTPV/menu/assets/social-preview.jpg">
<meta property="og:image:secure_url" content="https://colibriprint.com.mx/SnackTPV/menu/assets/social-preview.jpg">
<meta property="og:image:type" content="image/jpeg">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="Snackliciosos · Menú">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Snackliciosos · Menú">
<meta name="twitter:description" content="Antojitos que sacan sonrisas 💗">
<meta name="twitter:image" content="https://colibriprint.com.mx/SnackTPV/menu/assets/social-preview.jpg">

<title><?=h($name)?> · Menú</title>
<link rel="stylesheet" href="menu.css?v=25">
<link rel="stylesheet" href="order.css?v=1">
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
      <?php if($wa): ?><button class="heroCta" id="openOrderHero" type="button">🛒 Hacer mi pedido</button><?php endif; ?>
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
        
      ?>
        <article class="item" data-product-id="<?=$p['id']?>" data-product-name="<?=h($p['nombre'])?>" data-category-id="<?=$c['id']?>" data-category-name="<?=h($c['nombre'])?>" data-search="<?=h(strtolower($p['nombre'].' '.$p['descripcion']))?>">
          <?php if(!empty($p['imagen'])): ?><div class="imageWrap"><img class="itemImage" src="<?=h($p['imagen'])?>" alt="<?=h($p['nombre'])?>" loading="lazy"></div><?php endif; ?>
          <div class="itemInfo">
            <h3><?=h($p['nombre'])?></h3>
            <?php if($p['descripcion']): ?><p><?=h($p['descripcion'])?></p><?php endif; ?>
            <?php if((int)($p['toppings_gratis']??0)>0): ?><p class="includedToppings">Incluye <?=((int)$p['toppings_gratis'])?> topping<?=((int)$p['toppings_gratis'])===1?'':'s'?> gratis</p><?php endif; ?>
          </div>
          <div class="itemActions"><span class="price"><?=money($p['precio'])?></span><button class="addMenuProduct" type="button" data-product-id="<?=$p['id']?>" aria-label="Agregar <?=h($p['nombre'])?> al pedido">＋</button></div>
        </article>
      <?php endforeach; ?>
      </div>
    </section>
  <?php endforeach; ?>
  <section class="toppingsSection"><div class="categoryTitle"><h2>Complementos / toppings</h2><span>Hazlo a tu manera</span></div><div class="toppingList"><?php foreach($toppings as $t): ?><div><span><?=h($t['nombre'])?></span><b>+<?=money($t['precio'])?></b></div><?php endforeach; ?></div></section>
  </div>
  <div class="noResults" id="noResults" hidden>😋 No encontramos ese antojo. Prueba con otra palabra.</div>
</main>

<section class="afterMenuCta"><span>💗</span><div><strong>¿Ya encontraste tu antojo?</strong><small>Arma tu pedido y envíalo directo por WhatsApp.</small></div><button type="button" id="openOrderBottom">Ver mi pedido →</button></section>

<footer>
  <div class="footerInner">
    <div class="footerBrand"><strong id="footerName"><?=h($name)?></strong><span>Tu antojo, a tu manera. 💗</span></div>
    <div class="footerData"><span id="footerAddress"><?=h($address)?></span><?php if($phone): ?><a id="footerPhone" href="tel:<?=h(preg_replace('/\D+/','',$phone)??'')?>">☎ <?=h($phone)?></a><?php endif; ?><?php if($instagram): ?><span id="footerInstagram">📷 <?=h($instagram)?></span><?php endif; ?></div>
    <?php if($wa): ?><a id="whatsappBtn" class="whatsappBtn" href="<?=h($wa)?>" target="_blank" rel="noopener">💬 WhatsApp</a><?php endif; ?>
    <div class="syncLine" id="syncStatus">Catálogo actualizado</div>
  </div>
</footer>



<!-- PEDIDO DIRECTO DEL MENÚ -->
<div class="orderBackdrop" id="orderBackdrop" hidden></div>
<section class="orderModal" id="orderModal" role="dialog" aria-modal="true" aria-labelledby="orderTitle" hidden>
  <button class="orderClose" id="closeOrder" type="button" aria-label="Cerrar pedido">×</button>
  <div class="orderHeader">
    <span class="orderKicker">PEDIDO DIRECTO</span>
    <h2 id="orderTitle">Tu pedido 🛒</h2>
    <p>Revisa tus antojos, elige cómo pagar y envíalos por WhatsApp.</p>
  </div>
  <div class="orderBody">
    <div id="orderItems" class="orderItems"></div>
    <div class="orderEmpty" id="orderEmpty" hidden>🍓 Tu pedido está vacío.<br><small>Toca <b>＋</b> en cualquier producto para comenzar.</small></div>
    <div class="orderTotalRow"><span>Total</span><strong id="orderTotal">$0.00</strong></div>

    <div class="customerFields">
      <label>Tu nombre <span>opcional</span><input id="customerName" type="text" maxlength="80" placeholder="Ej. María"></label>
      <label>Nota para el negocio <span>opcional</span><textarea id="customerNote" rows="2" maxlength="250" placeholder="Ej. Pasaré por mi pedido a las 7:00 pm"></textarea></label>
    </div>

    <div class="orderPayment" id="orderPayment">
      <div class="paymentTitle"><span>FORMA DE PAGO</span><b>Elige una opción</b></div>
      <div class="paymentChoices">
        <button class="paymentChoice" type="button" data-order-payment="efectivo"><span>💵</span><b>Efectivo</b><small>Indica con qué billete pagarás</small></button>
        <button class="paymentChoice" type="button" data-order-payment="transferencia"><span>🏦</span><b>Transferencia</b><small>Te mostramos los datos de pago</small></button>
      </div>

      <div class="paymentDetail" id="cashDetail" hidden>
        <div class="detailTitle">💵 Pagaré con</div>
        <div class="billChoices">
          <button type="button" data-bill="50">$50</button>
          <button type="button" data-bill="100">$100</button>
          <button type="button" data-bill="200">$200</button>
          <button type="button" data-bill="500">$500</button>
          <button type="button" data-bill="1000">$1,000</button>
        </div>
        <div class="cashSummary" id="cashSummary">Selecciona la denominación con la que pagarás.</div>
      </div>

      <div class="paymentDetail transferDetail" id="transferDetail" hidden>
        <div class="detailTitle">🏦 Datos para transferencia</div>
        <div class="bankData" id="bankData"></div>
        <div class="transferInstructions" id="transferInstructions"></div>
        <label class="transferReady"><input id="transferReady" type="checkbox"> <span>Ya revisé los datos y enviaré el comprobante por WhatsApp.</span></label>
      </div>
    </div>
  </div>
  <div class="orderFooter">
    <button class="orderSecondary" id="continueShopping" type="button">Seguir comprando</button>
    <button class="orderWhatsapp" id="sendOrderWhatsapp" type="button" disabled>💬 ENVIAR PEDIDO POR WHATSAPP</button>
  </div>
</section>

<div class="toppingBackdrop" id="toppingBackdrop" hidden></div>
<section class="menuToppingModal" id="menuToppingModal" role="dialog" aria-modal="true" aria-labelledby="menuToppingTitle" hidden>
  <button class="orderClose" id="closeTopping" type="button" aria-label="Cerrar personalización">×</button>
  <div class="orderHeader">
    <span class="orderKicker">PERSONALIZA TU PEDIDO</span>
    <h2 id="menuToppingTitle">Hazlo a tu gusto ✨</h2>
    <p id="menuToppingDesc"></p>
  </div>
  <div id="menuToppingGrid" class="menuToppingGrid"></div>
  <div class="menuSelectedToppings" id="menuSelectedToppings"></div>
  <div class="toppingModalFooter">
    <div><span>Total</span><strong id="menuToppingTotal">$0.00</strong></div>
    <button class="orderWhatsapp" id="addCustomizedProduct" type="button">AGREGAR AL PEDIDO</button>
  </div>
</section>

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

<!-- VISOR DE FOTO DEL PRODUCTO -->
<div class="photoZoomBackdrop" id="photoZoomBackdrop" hidden></div>
<section class="photoZoomModal" id="photoZoomModal" role="dialog" aria-modal="true" aria-label="Foto del producto" hidden>
  <button class="photoZoomClose" id="closePhotoZoom" type="button" aria-label="Cerrar foto">×</button>
  <div class="photoZoomFrame">
    <button class="photoZoomNav photoZoomPrev" id="photoZoomPrev" type="button" aria-label="Foto anterior">‹</button>
    <img id="photoZoomImage" src="" alt="">
    <button class="photoZoomNav photoZoomNext" id="photoZoomNext" type="button" aria-label="Foto siguiente">›</button>
  </div>
  <div class="photoZoomThumbs" id="photoZoomThumbs" hidden></div>
  <div class="photoZoomCaption" id="photoZoomCaption"></div>
</section>

<button class="floatingMenuCart" id="menuCartButton" type="button" aria-label="Abrir mi pedido"><span>🛒</span><b>Mi pedido</b><span class="menuCartCount" id="menuCartCount">0</span><small id="menuCartTotal">$0.00</small></button>
<button class="floatingCraving" id="floatingCraving" type="button" aria-label="Abrir sugerencias de antojos"><span>💗</span><b>¿De qué tienes antojo?</b><i>⌃</i></button>

<script>window.MENU_DATA=<?=json_encode($menuData,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)?>;</script>
<script src="menu.js?v=25"></script>
<script src="order.js?v=1"></script>
</body></html>
