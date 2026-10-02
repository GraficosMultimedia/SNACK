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

$cutId=(int)($_GET['id']??0);
$cut=null;
if($cutId>0){
    $s=$pdo->prepare('SELECT * FROM caja_cortes WHERE id=? LIMIT 1');
    $s->execute([$cutId]);
    $cut=$s->fetch();
}
if(!$cut){
    $cut=$pdo->query('SELECT * FROM caja_cortes ORDER BY id DESC LIMIT 1')->fetch() ?: null;
}

// Si se solicita un corte guardado, reconstruimos EXACTAMENTE ese periodo.
// Si todavía no existe un corte, generamos el reporte del periodo actual.
$reportCutId=$cut ? (int)$cut['id'] : 0;
$desde=$cut['fecha_apertura'] ?? '2000-01-01 00:00:00';
$hasta=$cut['fecha_corte'] ?? date('Y-m-d H:i:s');

$st=$pdo->prepare("SELECT COUNT(*) cantidad,COALESCE(SUM(total),0) total,COALESCE(SUM(CASE WHEN metodo_pago='efectivo' THEN efectivo ELSE 0 END),0) efectivo,COALESCE(SUM(CASE WHEN metodo_pago='transferencia' THEN total ELSE 0 END),0) transferencias,COALESCE(SUM(cambio),0) cambios FROM ventas WHERE fecha>? AND fecha<=?");
$st->execute([$desde,$hasta]);
$stats=$st->fetch() ?: ['cantidad'=>0,'total'=>0,'efectivo'=>0,'transferencias'=>0,'cambios'=>0];

$salesSt=$pdo->prepare('SELECT id,folio,fecha,total,efectivo,cambio,metodo_pago FROM ventas WHERE fecha>? AND fecha<=? ORDER BY id ASC');
$salesSt->execute([$desde,$hasta]);
$sales=$salesSt->fetchAll();

$detailSt=$pdo->prepare('SELECT id,nombre,precio,cantidad,subtotal FROM venta_detalle WHERE venta_id=? ORDER BY id');
$topSt=$pdo->prepare('SELECT nombre,precio,cantidad,subtotal,es_gratis FROM venta_toppings WHERE venta_detalle_id=? ORDER BY id');

$businessName=config_value($pdo,'nombre_negocio',APP_NAME);
$address=config_value($pdo,'direccion','');
$phone=config_value($pdo,'telefono','');
$now=date('d/m/Y H:i');

function pdf_escape(string $s): string {
    $s=iconv('UTF-8','Windows-1252//TRANSLIT//IGNORE',$s) ?: $s;
    return str_replace(['\\','(',')'],['\\\\','\\(','\\)'],$s);
}
function pdf_text(string $text): string { return '(' . pdf_escape($text) . ')'; }

class SimplePDF {
    private array $objects=[]; private array $pages=[]; private float $w=595.28; private float $h=841.89;
    public function addPage(array $commands): void { if($commands)$this->pages[]=$commands; }
    private function obj(string $body): int {$this->objects[]=$body; return count($this->objects);}
    public function output(string $filename): void {
        if(!$this->pages)$this->pages=[[]];
        $font=$this->obj("<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>");
        $pageIds=[]; $contentIds=[];
        foreach($this->pages as $cmd){
            $stream=implode("\n",$cmd)."\n";
            $contentIds[]=$this->obj("<< /Length ".strlen($stream)." >>\nstream\n$stream\nendstream");
            $pageIds[]=$this->obj('');
        }
        $kids=''; foreach($pageIds as $id)$kids.=$id.' 0 R ';
        $pagesId=$this->obj("<< /Type /Pages /Kids [$kids] /Count ".count($pageIds)." >>");
        foreach($pageIds as $i=>$id){
            $this->objects[$id-1]="<< /Type /Page /Parent $pagesId 0 R /MediaBox [0 0 {$this->w} {$this->h}] /Resources << /Font << /F1 $font 0 R >> >> /Contents ".$contentIds[$i]." 0 R >>";
        }
        $catalog=$this->obj("<< /Type /Catalog /Pages $pagesId 0 R >>");
        $pdf="%PDF-1.4\n%\xE2\xE3\xCF\xD3\n"; $offsets=[0];
        foreach($this->objects as $i=>$body){$offsets[]=strlen($pdf);$pdf.=($i+1)." 0 obj\n$body\nendobj\n";}
        $xref=strlen($pdf); $pdf.="xref\n0 ".(count($this->objects)+1)."\n0000000000 65535 f \n";
        for($i=1;$i<=count($this->objects);$i++)$pdf.=sprintf("%010d 00000 n \n",$offsets[$i]);
        $pdf.="trailer\n<< /Size ".(count($this->objects)+1)." /Root $catalog 0 R >>\nstartxref\n$xref\n%%EOF";
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header('Content-Length: '.strlen($pdf));
        echo $pdf; exit;
    }
}
function txt(float $x,float $y,string $text,float $size=10): string { return "BT /F1 $size Tf 1 0 0 1 $x $y Tm ".pdf_text($text)." Tj ET"; }
function line(float $x1,float $y1,float $x2,float $y2): string {return "0.7 w $x1 $y1 m $x2 $y2 l S";}
function shorten(string $s,int $max): string {return strlen($s)>$max?substr($s,0,$max-3).'...':$s;}

$pdf=new SimplePDF();
$commands=[]; $y=805;
$addHeader=function() use (&$commands,&$y,$businessName,$address,$phone,$desde,$hasta,$reportCutId): void {
    $commands[]=txt(40,$y,$businessName,20); $y-=23;
    if($address){$commands[]=txt(40,$y,shorten($address,85),9);$y-=13;}
    if($phone){$commands[]=txt(40,$y,'Tel: '.$phone,9);$y-=13;}
    $y-=7; $commands[]=line(40,$y,555,$y); $y-=17;
    $commands[]=txt(40,$y,'CORTE DE CAJA',16); $y-=18;
    $commands[]=txt(40,$y,'Periodo: '.$desde.'  a  '.$hasta,9); $y-=14;
    if($reportCutId>0){$commands[]=txt(40,$y,'Corte #'.$reportCutId,8);$y-=13;}
};
$addHeader();
$commands[]=txt(40,$y,'Ventas: '.(int)$stats['cantidad'],10);
$commands[]=txt(205,$y,'Total vendido: '.money($stats['total']),10);
$commands[]=txt(405,$y,'Efectivo: '.money($stats['efectivo']),10); $y-=18;
$commands[]=txt(40,$y,'Transferencias: '.money($stats['transferencias']),10); $y-=18;
$commands[]=line(40,$y,555,$y);$y-=17;
$commands[]=txt(40,$y,'DETALLE DE LO VENDIDO',13);$y-=18;

foreach($sales as $sale){
    $detailSt->execute([(int)$sale['id']]); $details=$detailSt->fetchAll();
    $needed=18+max(1,count($details))*12;
    if($y<$needed+45){$pdf->addPage($commands);$commands=[];$y=805;$addHeader();$commands[]=txt(40,$y,'DETALLE DE LO VENDIDO · CONTINUACIÓN',11);$y-=18;}
    $commands[]=txt(40,$y,shorten($sale['folio'].'  '.$sale['fecha'],55),9);
    $commands[]=txt(300,$y,($sale['metodo_pago']==='transferencia'?'TRANSFERENCIA':'EFECTIVO'),8.5);
    $commands[]=txt(470,$y,money($sale['total']),9);$y-=13;
    foreach($details as $d){
        if($y<65){$pdf->addPage($commands);$commands=[];$y=805;$addHeader();$commands[]=txt(40,$y,'DETALLE · CONTINUACIÓN',11);$y-=18;}
        $name=shorten($d['nombre'].' x'.$d['cantidad'],52);
        $commands[]=txt(55,$y,$name,8.5);$commands[]=txt(470,$y,money($d['subtotal']),8.5);$y-=11;
        $topSt->execute([(int)$d['id']]);
        foreach($topSt->fetchAll() as $t){
            if($y<55){$pdf->addPage($commands);$commands=[];$y=805;$addHeader();$commands[]=txt(40,$y,'DETALLE · CONTINUACIÓN',11);$y-=18;}
            $topName='↳ '.$t['nombre'].' x'.$t['cantidad'];
            $label=((int)$t['es_gratis']===1)?'GRATIS':money($t['subtotal']);
            $commands[]=txt(70,$y,shorten($topName,48),7.5);$commands[]=txt(470,$y,$label,7.5);$y-=10;
        }
    }
    $y-=3;
}

// El resumen solo crea otra página si realmente no cabe. No se fuerza una página adicional.
if($y<155){$pdf->addPage($commands);$commands=[];$y=805;$addHeader();}
$commands[]=line(40,$y,555,$y);$y-=18;
$commands[]=txt(40,$y,'RESUMEN',13);$y-=18;
$summary=[
 ['Cantidad de ventas',(string)(int)$stats['cantidad']],
 ['Total vendido',money($stats['total'])],
 ['Efectivo recibido',money($stats['efectivo'])],
 ['Transferencias',money($stats['transferencias'])],
 ['Cambios entregados',money($stats['cambios'])],
];
foreach($summary as [$label,$value]){$commands[]=txt(40,$y,$label,10);$commands[]=txt(455,$y,$value,10);$y-=15;}
if($cut){
    $commands[]=txt(40,$y,'Fondo inicial',9);$commands[]=txt(455,$y,money($cut['fondo_inicial']),9);$y-=14;
    $commands[]=txt(40,$y,'Efectivo esperado',9);$commands[]=txt(455,$y,money($cut['efectivo_esperado']),9);$y-=14;
    $commands[]=txt(40,$y,'Efectivo contado',9);$commands[]=txt(455,$y,money($cut['efectivo_contado']),9);$y-=14;
    $commands[]=txt(40,$y,'Diferencia',9);$commands[]=txt(455,$y,money($cut['diferencia']),9);$y-=17;
}
$commands[]=txt(40,$y,'Documento generado por Snackliciosos TPV · '.$now,8);
$pdf->addPage($commands);
$pdf->output('corte-caja-'.($reportCutId>0?'corte-'.$reportCutId.'-':'').date('Ymd-His').'.pdf');
