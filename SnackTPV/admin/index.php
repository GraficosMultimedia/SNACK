<?php
declare(strict_types=1);
require_once __DIR__.'/../includes/auth.php'; require_admin(); require_once __DIR__.'/../includes/functions.php'; require_once __DIR__.'/../includes/topping_price.php';
$pdo=db();
$products=$pdo->query('SELECT p.*,c.nombre categoria FROM productos p JOIN categorias c ON c.id=p.categoria_id ORDER BY c.orden,c.id,p.orden,p.id')->fetchAll();
$toppings=$pdo->query('SELECT * FROM toppings ORDER BY orden,id')->fetchAll();
foreach($toppings as &$topping){ $topping['precio_operativo']=topping_effective_price($pdo,$topping); }
unset($topping);
$sales=$pdo->query('SELECT * FROM ventas ORDER BY id DESC LIMIT 20')->fetchAll();
$cats=$pdo->query('SELECT * FROM categorias ORDER BY orden,id')->fetchAll();
$catCounts=[]; foreach($pdo->query('SELECT categoria_id,COUNT(*) total FROM productos GROUP BY categoria_id') as $cr){$catCounts[(int)$cr['categoria_id']]=(int)$cr['total'];}
$today=$pdo->query("SELECT COUNT(*) n,COALESCE(SUM(total),0) total FROM ventas WHERE DATE(fecha)=CURDATE()")->fetch();
$activeProducts=0; foreach($products as $p){$activeProducts += (int)$p['activo'];}
?><!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin · Snackliciosos</title><link rel="stylesheet" href="style.css?v=11"><link rel="stylesheet" href="catalogo.css?v=13"></head><body>
<?php require __DIR__.'/sidebar.php'; ?>
<main class="wrap admin-content">
<section class="hero hero-dashboard"><div><span class="eyebrow">PANEL DE CONTROL</span><h1>Administración</h1><p>Gestiona tu catálogo con una experiencia rápida, visual y táctil.</p></div><div class="heroActions"><a class="btn btn-light" href="../menu/" target="_blank">👁 Ver menú</a><a class="btn btn-primary" href="corte.php">💵 CORTE DE CAJA</a></div></section>
<section class="stats four"><div class="stat stat-pop"><span>Ventas de hoy</span><strong><?=number_format((int)$today['n'])?></strong></div><div class="stat pink stat-pop"><span>Vendido hoy</span><strong><?=money($today['total'])?></strong></div><div class="stat stat-pop"><span>Productos activos</span><strong><?=$activeProducts?> <small>/ <?=count($products)?></small></strong></div><div class="stat stat-pop"><span>Toppings</span><strong><?=count($toppings)?></strong></div></section>

<section class="catalog-shell card" id="productos">
  <div class="sectionTitle catalog-head"><div><span class="eyebrow">CATÁLOGO</span><h2>Productos</h2><p>Filtra, busca y entra a editar sin perder el contexto.</p></div><a class="btn btn-primary" href="producto.php">＋ Nuevo producto</a></div>
  <div class="catalog-toolbar">
    <label class="searchBox"><span>⌕</span><input id="productSearch" type="search" placeholder="Buscar producto..." autocomplete="off"></label>
    <div class="filter-scroll" id="categoryFilters"><button class="filter-chip active" type="button" data-category="all">Todos <b><?=count($products)?></b></button><?php foreach($cats as $c):?><button class="filter-chip" type="button" data-category="<?=h((string)$c['id'])?>"><?=h((string)$c['id'])?>"><?=h($c['nombre'])?> <b><?=number_format($catCounts[(int)$c['id']]??0)?></b></button><?php endforeach;?></div>
  </div>
  <div class="empty-state" id="productEmpty" hidden><div class="empty-icon">🔎</div><h3>No encontramos ese producto</h3><p>Prueba con otro nombre o cambia la categoría.</p></div>
  <div class="product-grid" id="productGrid">
  <?php foreach($products as $p): $img=trim((string)($p['imagen']??'')); ?>
    <article class="product-card-admin" data-category="<?=h((string)$p['categoria_id'])?>" data-search="<?=h(strtolower($p['nombre'].' '.$p['categoria']))?>">
      <div class="product-card-image"><?php if($img):?><img src="<?=h($img)?>" alt="<?=h($p['nombre'])?>" loading="lazy"><?php else:?><div class="image-placeholder">🍓</div><?php endif;?><span class="status-dot <?=((int)$p['activo']?'is-on':'is-off')?>"></span></div>
      <div class="product-card-body"><div class="product-card-meta"><span><?=h($p['categoria'])?></span><span class="badge <?=((int)$p['activo']?'on':'off')?>"><?=((int)$p['activo']?'Activo':'Inactivo')?></span></div><h3><?=h($p['nombre'])?></h3><div class="product-card-bottom"><strong><?=money($p['precio'])?></strong><a class="quick-edit" href="producto.php?id=<?=$p['id']?>">Editar <span>→</span></a></div><?php if((int)$p['permite_toppings']):?><small class="topping-note">🍫 Hasta <?=((int)$p['max_toppings'])?> toppings · primeros 2 gratis</small><?php endif;?></div>
    </article>
  <?php endforeach; ?>
  </div>
</section>

<section class="split-admin" id="categorias">
<section class="card dynamic-panel"><div class="sectionTitle"><div><span class="eyebrow">ORGANIZACIÓN</span><h2>Categorías</h2><p>Selecciona una categoría para ver su contenido.</p></div><a class="btn btn-secondary" href="categoria.php">＋ Nueva</a></div><div class="category-stack" id="categoryStack"><?php foreach($cats as $c):?><a class="category-card" href="categoria.php?id=<?=$c['id']?>"><span class="category-icon">🗂️</span><span class="category-info"><strong><?=h($c['nombre'])?></strong><small><?=number_format($catCounts[(int)$c['id']]??0)?> producto(s)</small></span><span class="category-arrow">→</span></a><?php endforeach;?></div></section>
<section class="card dynamic-panel" id="toppings"><div class="sectionTitle"><div><span class="eyebrow">COMPLEMENTOS</span><h2>Toppings</h2><p>Precios y disponibilidad de complementos.</p></div><a class="btn btn-secondary" href="topping.php">＋ Nuevo</a></div><div class="topping-grid"><?php foreach($toppings as $t):?><a class="topping-card" href="topping.php?id=<?=$t['id']?>"><span class="topping-icon">🍫</span><span><strong><?=h($t['nombre'])?></strong><small><?=money($t['precio_operativo'])?><?php if((float)$t['precio']<=0):?><em class="price-source">precio recuperado</em><?php endif;?></small></span><span class="mini-status <?=((int)$t['activo']?'on':'off')?>"></span></a><?php endforeach;?></div></section>
</section>

<section class="card" id="ventas"><div class="sectionTitle"><div><span class="eyebrow">MOVIMIENTO</span><h2>Últimas ventas</h2><p>Consulta rápida de las operaciones registradas.</p></div><a class="btn btn-secondary" href="corte.php">Ver caja</a></div><div class="tableWrap"><table><thead><tr><th>Folio</th><th>Fecha</th><th>Total</th><th>Pago</th><th>Efectivo</th><th>Cambio</th></tr></thead><tbody><?php if(!$sales):?><tr><td colspan="6"><div class="table-empty">🧾 Todavía no hay ventas registradas.</div></td></tr><?php else: foreach($sales as $s):?><tr><td><strong><?=h($s['folio'])?></strong></td><td><?=h($s['fecha'])?></td><td class="priceCell"><?=money($s['total'])?></td><td><?=h(($s['metodo_pago']??'efectivo')==='transferencia'?'Transferencia':'Efectivo')?></td><td><?=money($s['efectivo'])?></td><td><?=money($s['cambio'])?></td></tr><?php endforeach; endif;?></tbody></table></div></section>
</main>
<script src="catalogo.js?v=12"></script>
</body></html>
