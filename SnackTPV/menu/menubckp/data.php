<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/functions.php';
try {
 $pdo=db();
 $cats=$pdo->query('SELECT id,nombre FROM categorias WHERE activa=1 ORDER BY orden,id')->fetchAll();
 $products=$pdo->query('SELECT p.id,p.categoria_id,p.nombre,p.descripcion,p.imagen,p.precio,p.permite_toppings,p.max_toppings,p.toppings_gratis FROM productos p JOIN categorias c ON c.id=p.categoria_id WHERE p.activo=1 AND c.activa=1 ORDER BY c.orden,p.orden,p.id')->fetchAll();
 $pt=$pdo->query('SELECT producto_id,topping_id FROM producto_toppings ORDER BY producto_id,topping_id')->fetchAll();
 $allowed=[]; foreach($pt as $row){$allowed[(int)$row['producto_id']][]=(int)$row['topping_id'];}
 foreach($products as &$pr){$pr['toppings_ids']=$allowed[(int)$pr['id']]??[];} unset($pr);
 $g=[];
 foreach($cats as $c){$g[(int)$c['id']]=['id'=>(int)$c['id'],'nombre'=>$c['nombre'],'productos'=>[]];}
 foreach($products as $p){$id=(int)$p['categoria_id'];if(isset($g[$id]))$g[$id]['productos'][]=$p;}
 $toppings=$pdo->query('SELECT id,nombre,precio FROM toppings WHERE activo=1 ORDER BY orden,id')->fetchAll();
 $business=['nombre'=>config_value($pdo,'nombre_negocio',APP_NAME),'direccion'=>config_value($pdo,'direccion','Frontera Piedras Negras #55 a col. Progreso'),'telefono'=>config_value($pdo,'telefono','6271104930'),'whatsapp'=>config_value($pdo,'whatsapp','526271104930'),'instagram'=>config_value($pdo,'instagram','@snackliciosos'),'mensaje'=>config_value($pdo,'mensaje_menu','¡Gracias por tu preferencia! ✨'),'logo'=>config_value($pdo,'logo','')];
 echo json_encode(['ok'=>true,'categories'=>array_values($g),'toppings'=>$toppings,'business'=>$business],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
} catch(Throwable $e) {http_response_code(500);echo json_encode(['ok'=>false,'error'=>'No se pudo actualizar el catálogo.'],JSON_UNESCAPED_UNICODE);}
