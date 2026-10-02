<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/topping_price.php';
try {
 $input=json_decode(file_get_contents('php://input'),true,512,JSON_THROW_ON_ERROR);
 $cart=$input['cart']??[];
 $method=(string)($input['metodo_pago']??'efectivo');
 if(!in_array($method,['efectivo','transferencia'],true)) throw new Exception('Método de pago inválido.');
 $cash=(float)($input['efectivo']??0);
 if(!is_array($cart)||!$cart) throw new Exception('El carrito está vacío.');
 $pdo=db(); $pdo->beginTransaction();
 $ps=$pdo->prepare('SELECT id,nombre,precio,activo,permite_toppings,max_toppings,toppings_gratis FROM productos WHERE id=? LIMIT 1');
 $ts=$pdo->prepare('SELECT id,nombre,precio,activo FROM toppings WHERE id=? LIMIT 1');
 $as=$pdo->prepare('SELECT topping_id FROM producto_toppings WHERE producto_id=?');
 $items=[]; $total=0.0;
 foreach($cart as $item){
   $pid=(int)($item['product_id']??0); if($pid<=0) throw new Exception('Producto inválido.');
   $ps->execute([$pid]); $product=$ps->fetch();
   if(!$product||!(int)$product['activo']) throw new Exception('Producto no disponible.');
   $qty=max(1,(int)($item['cantidad']??1)); $subtotal=(float)$product['precio']*$qty; $rows=[];
   $sent=is_array($item['toppings']??null)?array_slice($item['toppings'],0,20):[];
   if(count($sent)>0 && !(int)$product['permite_toppings']) throw new Exception('Este producto no permite toppings.');
   $max=(int)$product['max_toppings'];
   $gratisPorUnidad=(int)($product['toppings_gratis'] ?? 0);
   if($gratisPorUnidad<=0) $gratisPorUnidad=$max>0?$max:2;
   $allowed=[]; if((int)$product['permite_toppings']){ $as->execute([$pid]); $allowed=array_map('intval',$as->fetchAll(PDO::FETCH_COLUMN)); }
   $position=0;
   foreach($sent as $top){
     $tid=(int)($top['id']??0); if($tid<=0) continue;
     if(!in_array($tid,$allowed,true)) throw new Exception('Ese topping no está disponible para '.$product['nombre'].'.');
     $ts->execute([$tid]); $t=$ts->fetch(); if(!$t||!(int)$t['activo']) throw new Exception('Uno de los toppings ya no está disponible.');
     $freeSlots=$gratisPorUnidad*$qty;
     $isFree=$position<$freeSlots;
     $unitPrice=topping_effective_price($pdo, $t);
     $chargedPrice=$isFree?0.0:$unitPrice; $sub=$chargedPrice*$qty;
     $subtotal+=$sub; $rows[]=['id'=>(int)$t['id'],'nombre'=>$t['nombre'],'precio'=>$unitPrice,'cantidad'=>$qty,'subtotal'=>$sub,'es_gratis'=>$isFree?1:0]; $position++;
   }
   $total+=$subtotal; $items[]=['producto'=>$product,'cantidad'=>$qty,'subtotal'=>$subtotal,'toppings'=>$rows];
 }
 $total=round($total,2);
 if($method==='efectivo'){
   $cash=round($cash,2);
   if($cash<$total) throw new Exception('El efectivo es insuficiente.');
 } else { $cash=0.00; }
 $change=$method==='efectivo'?round($cash-$total,2):0.00;
 $folio='V-'.date('Ymd-His').'-'.random_int(100,999);
 $s=$pdo->prepare('INSERT INTO ventas(folio,total,metodo_pago,efectivo,cambio) VALUES(?,?,?,?,?)');
 $s->execute([$folio,$total,$method,$cash,$change]); $saleId=(int)$pdo->lastInsertId();
 $d=$pdo->prepare('INSERT INTO venta_detalle(venta_id,producto_id,nombre,precio,cantidad,subtotal) VALUES(?,?,?,?,?,?)');
 $td=$pdo->prepare('INSERT INTO venta_toppings(venta_detalle_id,topping_id,nombre,precio,cantidad,subtotal,es_gratis) VALUES(?,?,?,?,?,?,?)');
 foreach($items as $it){$p=$it['producto'];$d->execute([$saleId,(int)$p['id'],$p['nombre'],(float)$p['precio'],(int)$it['cantidad'],(float)$it['subtotal']]);$did=(int)$pdo->lastInsertId();foreach($it['toppings'] as $t)$td->execute([$did,$t['id'],$t['nombre'],$t['precio'],$t['cantidad'],$t['subtotal'],$t['es_gratis']]);}
 $pdo->commit();
 echo json_encode(['ok'=>true,'id'=>$saleId,'folio'=>$folio,'total'=>$total,'metodo_pago'=>$method,'efectivo'=>$cash,'cambio'=>$change],JSON_UNESCAPED_UNICODE);
} catch(Throwable $e){if(isset($pdo)&&$pdo instanceof PDO&&$pdo->inTransaction())$pdo->rollBack();http_response_code(400);echo json_encode(['ok'=>false,'error'=>$e->getMessage()],JSON_UNESCAPED_UNICODE);}
