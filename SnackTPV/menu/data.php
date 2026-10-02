<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/functions.php';
require_once __DIR__.'/../includes/topping_price.php';
try {
 $pdo=db();
 $cats=$pdo->query('SELECT id,nombre FROM categorias WHERE activa=1 ORDER BY orden,id')->fetchAll();
 $products=$pdo->query('SELECT p.id,p.categoria_id,p.nombre,p.descripcion,p.imagen,p.precio,p.permite_toppings,p.max_toppings,p.toppings_gratis FROM productos p JOIN categorias c ON c.id=p.categoria_id WHERE p.activo=1 AND c.activa=1 ORDER BY c.orden,p.orden,p.id')->fetchAll();
 $pt=$pdo->query('SELECT producto_id,topping_id FROM producto_toppings ORDER BY producto_id,topping_id')->fetchAll();
 $allowed=[]; foreach($pt as $row){$allowed[(int)$row['producto_id']][]=(int)$row['topping_id'];}

 // Galería: una o X imágenes activas por producto. Se conserva productos.imagen
 // como imagen principal/compatibilidad. Si todavía no hay registros en la
 // galería, el menú sigue funcionando con la imagen existente.
 $galleryByProduct=[];
 $gi=$pdo->query('SELECT producto_id,imagen,orden FROM producto_imagenes WHERE activa=1 ORDER BY producto_id,orden,id')->fetchAll();
 foreach($gi as $row){
   $pid=(int)$row['producto_id'];
   $url=trim((string)$row['imagen']);
   if($url!=='') $galleryByProduct[$pid][]=$url;
 }
 foreach($products as &$pr){
   $pid=(int)$pr['id'];
   $pr['toppings_ids']=$allowed[$pid]??[];
   $main=trim((string)($pr['imagen']??''));
   $imgs=$galleryByProduct[$pid]??[];
   if($main!=='') array_unshift($imgs,$main);
   $imgs=array_values(array_unique(array_filter($imgs,fn($v)=>trim((string)$v)!=='')));
   $pr['imagenes']=$imgs;
   // La primera imagen de la galería activa se convierte en la principal visual.
   if($imgs) $pr['imagen']=$imgs[0];
 } unset($pr);
 $g=[];
 foreach($cats as $c){$g[(int)$c['id']]=['id'=>(int)$c['id'],'nombre'=>$c['nombre'],'productos'=>[]];}
 foreach($products as $p){$id=(int)$p['categoria_id'];if(isset($g[$id]))$g[$id]['productos'][]=$p;}
 $toppings=$pdo->query('SELECT id,nombre,precio FROM toppings WHERE activo=1 ORDER BY orden,id')->fetchAll(); foreach($toppings as &$t){$t['precio']=topping_effective_price($pdo,$t);} unset($t);
 $business=['nombre'=>config_value($pdo,'nombre_negocio',APP_NAME),'direccion'=>config_value($pdo,'direccion','Frontera Piedras Negras #55 a col. Progreso'),'telefono'=>config_value($pdo,'telefono','6271104930'),'whatsapp'=>config_value($pdo,'whatsapp','526271104930'),'instagram'=>config_value($pdo,'instagram','@snackliciosos'),'mensaje'=>config_value($pdo,'mensaje_menu','¡Gracias por tu preferencia! ✨'),'logo'=>config_value($pdo,'logo',''),'transferencia'=>['banco'=>config_value($pdo,'transfer_banco',''),'titular'=>config_value($pdo,'transfer_titular',''),'cuenta'=>config_value($pdo,'transfer_cuenta',''),'clabe'=>config_value($pdo,'transfer_clabe',''),'instrucciones'=>config_value($pdo,'transfer_instrucciones','')]];
 echo json_encode(['ok'=>true,'categories'=>array_values($g),'toppings'=>$toppings,'business'=>$business],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
} catch(Throwable $e) {http_response_code(500);echo json_encode(['ok'=>false,'error'=>'No se pudo actualizar el catálogo.'],JSON_UNESCAPED_UNICODE);}
