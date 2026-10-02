<?php
declare(strict_types=1);
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/functions.php';
$pdo=db();
$name=config_value($pdo,'nombre_negocio',APP_NAME);
$logo=config_value($pdo,'logo','');
$message=config_value($pdo,'mensaje_menu','¡Gracias por tu preferencia! ✨');
$address=config_value($pdo,'direccion','Frontera Piedras Negras #55 a col. Progreso');
$phone=config_value($pdo,'telefono','6271104930');
$whatsapp=config_value($pdo,'whatsapp','526271104930');
$instagram=config_value($pdo,'instagram','@snackliciosos');
$cats=$pdo->query('SELECT id,nombre FROM categorias WHERE activa=1 ORDER BY orden,id')->fetchAll();
$products=$pdo->query('SELECT p.id,p.categoria_id,p.nombre,p.descripcion,p.precio FROM productos p JOIN categorias c ON c.id=p.categoria_id WHERE p.activo=1 AND c.activa=1 ORDER BY c.orden,p.orden,p.id')->fetchAll();
$grouped=[];
foreach($cats as $c){$grouped[(int)$c['id']]=['id'=>(int)$c['id'],'nombre'=>$c['nombre'],'productos'=>[]];}
foreach($products as $p){$cid=(int)$p['categoria_id'];if(isset($grouped[$cid]))$grouped[$cid]['productos'][]=$p;}
$grouped=array_values($grouped);
$toppings=$pdo->query('SELECT id,nombre,precio FROM toppings WHERE activo=1 ORDER BY orden,id')->fetchAll();
function wa_url(string $phone): string {$digits=preg_replace('/\D+/','',$phone)??'';return $digits?'https://wa.me/'.$digits:'';}
$wa=wa_url($whatsapp);
?><!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="theme-color" content="#ec0874"><title><?=h($name)?> · Menú</title><link rel="stylesheet" href="menu.css?v=6"></head><body>
<div class="topCurve"></div>
<header class="header"><div class="logoWrap"><?php if($logo):?><img class="logo" id="businessLogo" src="<?=h($logo)?>" alt="<?=h($name)?>"><?php else:?><div class="logoFallback" id="businessLogoFallback">🍓</div><?php endif;?></div><h1 id="businessName"><?=h($name)?></h1><div class="businessInfo"><span id="businessAddress"><?=h($address)?></span><?php if($phone):?><span id="businessPhone">☎ <?=h($phone)?></span><?php endif;?></div><p class="thanks" id="thanks"><?=h($message)?></p></header>
<nav class="categories" id="categories"><?php foreach($grouped as $i=>$c):?><a class="cat <?=($i===0?'active':'')?>" href="#cat-<?=$c['id']?>"><?=h($c['nombre'])?></a><?php endforeach;?></nav>
<main class="menuGrid" id="menuGrid"><?php foreach($grouped as $c):?><section class="category" id="cat-<?=$c['id']?>"><h2><?=h($c['nombre'])?></h2><div class="items"><?php foreach($c['productos'] as $p):?><article class="item"><div class="itemInfo"><h3><?=h($p['nombre'])?></h3><?php if($p['descripcion']):?><p><?=h($p['descripcion'])?></p><?php endif;?></div><span class="price"><?=money($p['precio'])?></span></article><?php endforeach;?></div></section><?php endforeach;?><section class="toppingsSection"><h2>Complementos / toppings</h2><div class="toppingList"><?php foreach($toppings as $t):?><div><span><?=h($t['nombre'])?></span><b>+<?=money($t['precio'])?></b></div><?php endforeach;?></div></section></main>
<footer><div class="footerInner"><div class="footerBrand"><strong id="footerName"><?=h($name)?></strong><span>Tu antojo, a tu manera.</span></div><div class="footerData"><span id="footerAddress"><?=h($address)?></span><?php if($phone):?><a id="footerPhone" href="tel:<?=h(preg_replace('/\D+/','',$phone)??'')?>">☎ <?=h($phone)?></a><?php endif;?><?php if($instagram):?><span id="footerInstagram">📷 <?=h($instagram)?></span><?php endif;?></div><?php if($wa):?><a id="whatsappBtn" class="whatsappBtn" href="<?=h($wa)?>" target="_blank" rel="noopener">💬 WhatsApp</a><?php endif;?><div class="syncLine" id="syncStatus">Catálogo actualizado</div></div></footer>
<script>window.MENU_DATA=<?=json_encode(['categories'=>$grouped,'toppings'=>$toppings,'business'=>['nombre'=>$name,'direccion'=>$address,'telefono'=>$phone,'whatsapp'=>$whatsapp,'instagram'=>$instagram,'mensaje'=>$message,'logo'=>$logo]],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)?>;</script><script src="menu.js?v=6"></script></body></html>
