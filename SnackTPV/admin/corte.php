<?php
declare(strict_types=1);
require_once __DIR__.'/../includes/auth.php';
require_admin();
require_once __DIR__.'/../includes/functions.php';
$pdo=db();
$pdo->exec("CREATE TABLE IF NOT EXISTS caja_cortes (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
 fecha_apertura DATETIME NULL,
 fecha_corte DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 fondo_inicial DECIMAL(10,2) NOT NULL DEFAULT 0.00,
 ventas_total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
 efectivo_ventas DECIMAL(10,2) NOT NULL DEFAULT 0.00,
 transferencias DECIMAL(10,2) NOT NULL DEFAULT 0.00,
 efectivo_esperado DECIMAL(10,2) NOT NULL DEFAULT 0.00,
 efectivo_contado DECIMAL(10,2) NOT NULL DEFAULT 0.00,
 diferencia DECIMAL(10,2) NOT NULL DEFAULT 0.00,
 cantidad_ventas INT UNSIGNED NOT NULL DEFAULT 0,
 usuario_id INT UNSIGNED NULL,
 PRIMARY KEY(id), KEY idx_corte_fecha(fecha_corte)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
$last=$pdo->query('SELECT * FROM caja_cortes ORDER BY id DESC LIMIT 1')->fetch();
$desde=$last['fecha_corte']??'2000-01-01 00:00:00';
$statsSt=$pdo->prepare("SELECT COUNT(*) cantidad,COALESCE(SUM(total),0) total,COALESCE(SUM(CASE WHEN metodo_pago='efectivo' THEN efectivo ELSE 0 END),0) efectivo,COALESCE(SUM(CASE WHEN metodo_pago='transferencia' THEN total ELSE 0 END),0) transferencias FROM ventas WHERE fecha>?");
$statsSt->execute([$desde]); $stats=$statsSt->fetch();
$fondo=(float)($_POST['fondo_inicial']??($last['fondo_inicial']??0));
$contado=(float)($_POST['efectivo_contado']??0);
$ventas=(float)$stats['total']; $esperado=$fondo+(float)$stats['efectivo']; $diferencia=$contado-$esperado; $msg=''; $savedCutId=0;
if($_SERVER['REQUEST_METHOD']==='POST'){
 check_csrf();
 $s=$pdo->prepare('INSERT INTO caja_cortes(fecha_apertura,fecha_corte,fondo_inicial,ventas_total,efectivo_ventas,transferencias,efectivo_esperado,efectivo_contado,diferencia,cantidad_ventas,usuario_id) VALUES(?,NOW(),?,?,?,?,?,?,?,?,?)');
 $s->execute([$desde,$fondo,$ventas,(float)$stats['efectivo'],(float)$stats['transferencias'],$esperado,$contado,$diferencia,(int)$stats['cantidad'],(int)$_SESSION['admin_id']]);
 $savedCutId=(int)$pdo->lastInsertId();
 $msg='Corte guardado correctamente.';
 $last=$pdo->query('SELECT * FROM caja_cortes ORDER BY id DESC LIMIT 1')->fetch(); $desde=$last['fecha_corte']; $stats=['cantidad'=>0,'total'=>0,'efectivo'=>0,'transferencias'=>0]; $ventas=0; $esperado=$fondo; $diferencia=0; $contado=0;
}
$history=$pdo->query('SELECT c.*,u.usuario FROM caja_cortes c LEFT JOIN usuarios u ON u.id=c.usuario_id ORDER BY c.id DESC LIMIT 20')->fetchAll();
?><!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Corte de caja · Snackliciosos</title><link rel="stylesheet" href="style.css?v=10"></head><body>
<?php require __DIR__.'/sidebar.php'; ?>
<main class="wrap admin-content">
<?php if($msg):?><div class="alert success"><?=h($msg)?> <?php if($savedCutId): ?><a href="corte_pdf.php?id=<?= $savedCutId ?>" style="margin-left:10px;color:inherit;text-decoration:underline;font-weight:950">📄 Descargar PDF de este corte</a><?php endif; ?></div><?php endif;?>
<section class="hero"><div><span class="eyebrow">CAJA ACTUAL</span><h1>Corte de caja</h1><p>Consulta lo vendido desde el último corte y genera el reporte.</p></div><div class="heroActions"><a class="btn btn-light" href="corte_pdf.php<?= $last ? '?id='.(int)$last['id'] : '' ?>">📄 PDF DEL ÚLTIMO CORTE</a><a class="btn btn-light" href="../">Abrir TPV</a></div></section>
<section class="stats four"><div class="stat"><span>Ventas</span><strong><?=number_format((int)$stats['cantidad'])?></strong></div><div class="stat pink"><span>Total vendido</span><strong><?=money($ventas)?></strong></div><div class="stat"><span>Efectivo esperado</span><strong><?=money($esperado)?></strong></div><div class="stat"><span>Desde</span><strong class="smallStat"><?=h($desde)?></strong></div></section>
<section class="card"><div class="sectionTitle"><div><h2>Realizar corte</h2><p>Captura el efectivo que tienes físicamente en caja.</p></div></div><form method="post" class="cutForm"><input type="hidden" name="csrf" value="<?=h(csrf())?>"><label>Fondo inicial<input name="fondo_inicial" type="number" step="0.01" min="0" value="<?=h((string)$fondo)?>"></label><label>Efectivo contado<input name="efectivo_contado" type="number" step="0.01" min="0" required value="<?=h((string)$contado)?>"></label><div class="expected"><span>Efectivo de ventas</span><b><?=money($stats['efectivo'])?></b></div><div class="expected"><span>Transferencias</span><b><?=money($stats['transferencias'])?></b></div><div class="expected"><span>Esperado en caja</span><b><?=money($esperado)?></b></div><div class="expected difference"><span>Diferencia estimada</span><b><?=money($diferencia)?></b></div><button class="btn btn-primary" type="submit">CERRAR CAJA Y GUARDAR CORTE</button></form></section>
<section class="card"><div class="sectionTitle"><div><h2>Historial de cortes</h2><p>Últimos cortes registrados.</p></div></div><div class="tableWrap"><table><thead><tr><th>Fecha</th><th>Ventas</th><th>Total</th><th>Esperado</th><th>Contado</th><th>Diferencia</th><th>Usuario</th><th>PDF</th></tr></thead><tbody><?php foreach($history as $c):?><tr><td><?=h($c['fecha_corte'])?></td><td><?=number_format((int)$c['cantidad_ventas'])?></td><td><?=money($c['ventas_total'])?></td><td><?=money($c['efectivo_esperado'])?></td><td><?=money($c['efectivo_contado'])?></td><td class="<?=((float)$c['diferencia']<0?'neg':((float)$c['diferencia']>0?'pos':''))?>"><?=money($c['diferencia'])?></td><td><?=h($c['usuario']??'')?></td><td><a class="edit" href="corte_pdf.php?id=<?= (int)$c['id'] ?>">PDF</a></td></tr><?php endforeach;?></tbody></table></div></section>
</main></body></html>
