<?php
declare(strict_types=1);
require_once __DIR__.'/../includes/auth.php';
require_admin();
require_once __DIR__.'/../includes/functions.php';
$pdo=db();

$keys=['nombre_negocio','direccion','telefono','whatsapp','instagram','mensaje_menu','logo','transfer_banco','transfer_titular','transfer_cuenta','transfer_clabe','transfer_instrucciones'];
$values=[];
foreach($keys as $k){$values[$k]=config_value($pdo,$k,'');}
if (!$values['nombre_negocio']) $values['nombre_negocio'] = APP_NAME;
if (!$values['direccion']) $values['direccion'] = 'Frontera Piedras Negras #55 a col. Progreso';
if (!$values['telefono']) $values['telefono'] = '6271104930';
if (!$values['whatsapp']) $values['whatsapp'] = '526271104930';
if (!$values['instagram']) $values['instagram'] = '@snackliciosos';
if (!$values['mensaje_menu']) $values['mensaje_menu'] = '¡Gracias por tu preferencia! ✨';
$msg=''; $err='';

if($_SERVER['REQUEST_METHOD']==='POST'){
    check_csrf();
    foreach(['nombre_negocio','direccion','telefono','whatsapp','instagram','mensaje_menu','transfer_banco','transfer_titular','transfer_cuenta','transfer_clabe','transfer_instrucciones'] as $k){
        set_config($pdo,$k,trim((string)($_POST[$k]??'')));
        $values[$k]=trim((string)($_POST[$k]??''));
    }
    if(isset($_FILES['logo']) && ($_FILES['logo']['error'] ?? UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_NO_FILE){
        if($_FILES['logo']['error']!==UPLOAD_ERR_OK){$err='No se pudo subir la imagen.';}
        elseif((int)$_FILES['logo']['size']>4*1024*1024){$err='La imagen no debe superar 4 MB.';}
        else{
            $mime=(new finfo(FILEINFO_MIME_TYPE))->file($_FILES['logo']['tmp_name']);
            $allowed=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
            if(!isset($allowed[$mime])){$err='Formato no permitido. Usa JPG, PNG o WEBP.';}
            else{
                $dir=__DIR__.'/../uploads';
                if(!is_dir($dir))mkdir($dir,0755,true);
                $file='logo_'.date('Ymd_His').'_'.bin2hex(random_bytes(3)).'.'.$allowed[$mime];
                if(move_uploaded_file($_FILES['logo']['tmp_name'],$dir.'/'.$file)){
                    $url=app_url('uploads/'.$file);
                    set_config($pdo,'logo',$url); $values['logo']=$url;
                } else $err='No se pudo guardar la imagen.';
            }
        }
    }
    if(!$err)$msg='Datos del negocio guardados correctamente.';
}
?><!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Datos del negocio · Snackliciosos</title><link rel="stylesheet" href="style.css?v=10"></head><body>
<?php require __DIR__.'/sidebar.php'; ?>
<main class="wrap admin-content">
<?php if($msg):?><div class="alert success"><?=h($msg)?></div><?php endif;?>
<?php if($err):?><div class="alert error"><?=h($err)?></div><?php endif;?>
<section class="hero"><div><span class="eyebrow">CONFIGURACIÓN</span><h1>Datos del negocio</h1><p>Estos datos se muestran en el menú cliente y en los documentos del sistema.</p></div><a class="btn btn-light" href="../menu/" target="_blank">Ver menú</a></section>
<section class="card"><div class="sectionTitle"><div><h2>Información pública</h2><p>Modifica aquí el contenido que verá el cliente.</p></div></div>
<form method="post" enctype="multipart/form-data" class="form businessForm"><input type="hidden" name="csrf" value="<?=h(csrf())?>">
<label>Nombre del negocio<input name="nombre_negocio" value="<?=h($values['nombre_negocio'])?>" required></label>
<label>Dirección<input name="direccion" value="<?=h($values['direccion'])?>" placeholder="Ej. Frontera Piedras Negras #55 a col. Progreso"></label>
<label>Teléfono<input name="telefono" value="<?=h($values['telefono'])?>" placeholder="Ej. 6271104930"></label>
<label>WhatsApp<input name="whatsapp" value="<?=h($values['whatsapp'])?>" placeholder="Ej. 526271104930"></label>
<label>Instagram<input name="instagram" value="<?=h($values['instagram'])?>" placeholder="@snackliciosos"></label>
<label>Mensaje del menú<textarea name="mensaje_menu" rows="3"><?=h($values['mensaje_menu'])?></textarea></label>
<div class="sectionTitle"><div><span class="eyebrow">PAGOS</span><h2>Datos para transferencia</h2><p>Estos datos aparecerán automáticamente al cliente cuando elija pagar por transferencia.</p></div></div>
<label>Banco<input name="transfer_banco" value="<?=h($values['transfer_banco'])?>" placeholder="Ej. BBVA"></label>
<label>Titular de la cuenta<input name="transfer_titular" value="<?=h($values['transfer_titular'])?>" placeholder="Nombre del titular"></label>
<label>Número de cuenta<input name="transfer_cuenta" value="<?=h($values['transfer_cuenta'])?>" placeholder="Número de cuenta"></label>
<label>CLABE interbancaria<input name="transfer_clabe" value="<?=h($values['transfer_clabe'])?>" inputmode="numeric" placeholder="18 dígitos"></label>
<label>Instrucciones para el cliente<textarea name="transfer_instrucciones" rows="3" placeholder="Ej. Envía tu comprobante por WhatsApp indicando tu nombre."><?=h($values['transfer_instrucciones'])?></textarea></label>

<label>Imagen / logo<input type="file" name="logo" accept="image/jpeg,image/png,image/webp"><small>JPG, PNG o WEBP. Máximo 4 MB.</small></label>
<?php if($values['logo']):?><div class="logoPreview"><span>Imagen actual</span><img src="<?=h($values['logo'])?>" alt="Logo actual"></div><?php endif;?>
<button class="btn btn-primary" type="submit">GUARDAR DATOS</button>
</form></section></main></body></html>
