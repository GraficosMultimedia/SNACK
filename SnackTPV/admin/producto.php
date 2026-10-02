<?php
declare(strict_types=1);
require_once __DIR__.'/../includes/auth.php';
require_admin();
require_once __DIR__.'/../includes/functions.php';
$pdo=db();

$id=(int)($_GET['id']??0);
$isNew=$id<=0;
$p=null;
if(!$isNew){
  $q=$pdo->prepare('SELECT * FROM productos WHERE id=?');
  $q->execute([$id]);
  $p=$q->fetch();
  if(!$p) exit('Producto no encontrado');
}

$cats=$pdo->query('SELECT id,nombre FROM categorias WHERE activa=1 ORDER BY orden,id')->fetchAll();
if(!$cats) $cats=$pdo->query('SELECT id,nombre FROM categorias ORDER BY orden,id')->fetchAll();
$toppings=$pdo->query('SELECT id,nombre,precio,activo FROM toppings ORDER BY orden,id')->fetchAll();
$assigned=[];
$image=$p['imagen']??'';
$gallery=[];
if(!$isNew){
  try {
    $q=$pdo->prepare('SELECT id,producto_id,imagen,orden,activa,created_at FROM producto_imagenes WHERE producto_id=? ORDER BY orden,id');
    $q->execute([$id]);
    $gallery=$q->fetchAll();
  } catch(Throwable $e) {}
  try {
    $q=$pdo->prepare('SELECT topping_id FROM producto_toppings WHERE producto_id=?');
    $q->execute([$id]);
    $assigned=array_map('intval',$q->fetchAll(PDO::FETCH_COLUMN));
  } catch(Throwable $e) {}
}

function product_upload(PDO $pdo, array $file, string $name): string {
  if(($file['error'] ?? UPLOAD_ERR_NO_FILE)===UPLOAD_ERR_NO_FILE) return '';
  if(($file['error'] ?? UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK) throw new RuntimeException('No se pudo subir una de las imágenes.');
  if((int)($file['size']??0)>4*1024*1024) throw new RuntimeException('Cada imagen original no debe superar 4 MB.');
  $mime=(new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
  $allowed=['image/jpeg','image/png','image/webp'];
  if(!in_array($mime,$allowed,true)) throw new RuntimeException('Formato no permitido. Usa JPG, PNG o WEBP.');
  $dir=__DIR__.'/../uploads/productos';
  $out=optimize_product_image($file['tmp_name'],$mime,$dir,$name);
  return app_url('uploads/productos/'.$out);
}

function normalize_files_array(array $files): array {
  if(!isset($files['name']) || !is_array($files['name'])) return [];
  $out=[];
  $count=count($files['name']);
  for($i=0;$i<$count;$i++){
    $out[]=[
      'name'=>$files['name'][$i]??'',
      'type'=>$files['type'][$i]??'',
      'tmp_name'=>$files['tmp_name'][$i]??'',
      'error'=>$files['error'][$i]??UPLOAD_ERR_NO_FILE,
      'size'=>$files['size'][$i]??0,
    ];
  }
  return $out;
}

if($_SERVER['REQUEST_METHOD']==='POST'){
  check_csrf();
  $action=(string)($_POST['action']??'save_product');

  if($action!=='save_product'){
    if($isNew) exit('Primero guarda el producto para administrar su galería.');

    try {
      if($action==='upload_gallery'){
        $files=normalize_files_array($_FILES['imagenes']??[]);
        if(!$files) exit('Selecciona al menos una imagen.');
        $qMax=$pdo->prepare('SELECT COALESCE(MAX(orden),0) FROM producto_imagenes WHERE producto_id=?');
        $qMax->execute([$id]);
        $nextOrder=(int)$qMax->fetchColumn()+1;
        $ins=$pdo->prepare('INSERT INTO producto_imagenes(producto_id,imagen,orden,activa) VALUES(?,?,?,1)');
        foreach($files as $file){
          if(($file['error']??UPLOAD_ERR_NO_FILE)===UPLOAD_ERR_NO_FILE) continue;
          $url=product_upload($pdo,$file,(string)$p['nombre']);
          $ins->execute([$id,$url,$nextOrder++]);
        }
        header('Location: producto.php?id='.$id.'#galeria'); exit;
      }

      if($action==='set_primary'){
        $imageId=(int)($_POST['image_id']??0);
        $q=$pdo->prepare('SELECT imagen FROM producto_imagenes WHERE id=? AND producto_id=? LIMIT 1');
        $q->execute([$imageId,$id]);
        $selected=(string)($q->fetchColumn()??'');
        if($selected==='') exit('Imagen no encontrada.');
        $pdo->beginTransaction();
        $pdo->prepare('UPDATE productos SET imagen=? WHERE id=?')->execute([$selected,$id]);
        $pdo->prepare('UPDATE producto_imagenes SET orden=CASE WHEN id=? THEN 0 ELSE orden+1 END WHERE producto_id=?')->execute([$imageId,$id]);
        $pdo->commit();
        header('Location: producto.php?id='.$id.'#galeria'); exit;
      }

      if($action==='sync_primary'){
        $current=trim((string)($p['imagen']??''));
        if($current!==''){
          $q=$pdo->prepare('SELECT id FROM producto_imagenes WHERE producto_id=? AND imagen=? LIMIT 1');
          $q->execute([$id,$current]);
          if(!$q->fetchColumn()){
            $pdo->prepare('INSERT INTO producto_imagenes(producto_id,imagen,orden,activa) VALUES(?,?,0,1)')->execute([$id,$current]);
          }
        }
        header('Location: producto.php?id='.$id.'#galeria'); exit;
      }

      if($action==='toggle_gallery'){
        $imageId=(int)($_POST['image_id']??0);
        $q=$pdo->prepare('UPDATE producto_imagenes SET activa=IF(activa=1,0,1) WHERE id=? AND producto_id=?');
        $q->execute([$imageId,$id]);
        header('Location: producto.php?id='.$id.'#galeria'); exit;
      }

      if($action==='delete_gallery'){
        $imageId=(int)($_POST['image_id']??0);
        $q=$pdo->prepare('SELECT imagen FROM producto_imagenes WHERE id=? AND producto_id=? LIMIT 1');
        $q->execute([$imageId,$id]);
        $selected=(string)($q->fetchColumn()??'');
        if($selected==='') exit('Imagen no encontrada.');
        if($selected===trim((string)($p['imagen']??''))){
          $q=$pdo->prepare('SELECT imagen FROM producto_imagenes WHERE producto_id=? AND id<>? AND activa=1 ORDER BY orden,id LIMIT 1');
          $q->execute([$id,$imageId]);
          $replacement=(string)($q->fetchColumn()??'');
          $pdo->beginTransaction();
          $pdo->prepare('DELETE FROM producto_imagenes WHERE id=? AND producto_id=?')->execute([$imageId,$id]);
          $pdo->prepare('UPDATE productos SET imagen=? WHERE id=?')->execute([$replacement,$id]);
          $pdo->commit();
        } else {
          $pdo->prepare('DELETE FROM producto_imagenes WHERE id=? AND producto_id=?')->execute([$imageId,$id]);
        }
        header('Location: producto.php?id='.$id.'#galeria'); exit;
      }

      if($action==='reorder_gallery'){
        $ids=$_POST['gallery_order']??[];
        if(is_array($ids)){
          $upd=$pdo->prepare('UPDATE producto_imagenes SET orden=? WHERE id=? AND producto_id=?');
          $order=0;
          foreach($ids as $imageId){
            $upd->execute([$order++,(int)$imageId,$id]);
          }
        }
        header('Location: producto.php?id='.$id.'#galeria'); exit;
      }

      exit('Acción de galería no reconocida.');
    } catch(Throwable $e) {
      if($pdo->inTransaction()) $pdo->rollBack();
      exit('No se pudo actualizar la galería: '.h($e->getMessage()));
    }
  }

  $nombre=trim((string)($_POST['nombre']??''));
  $descripcion=trim((string)($_POST['descripcion']??''));
  $precio=max(0,(float)($_POST['precio']??0));
  $categoria=(int)($_POST['categoria_id']??0);
  $orden=(int)($_POST['orden']??0);
  $activo=isset($_POST['activo'])?1:0;
  $permite=isset($_POST['permite_toppings'])?1:0;
  $max=$permite?max(1,min(9,(int)($_POST['max_toppings']??1))):0;
  $gratis=$permite?2:0;

  if($nombre==='') exit('El nombre del producto es obligatorio.');
  if($categoria<=0) exit('Selecciona una categoría.');

  try {
    $newImage=$image;
    if(isset($_FILES['imagen']) && ($_FILES['imagen']['error'] ?? UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_NO_FILE){
      $newImage=product_upload($pdo,$_FILES['imagen'],$nombre);
    }

    $pdo->beginTransaction();
    if($isNew){
      if($orden<=0){
        $q=$pdo->prepare('SELECT COALESCE(MAX(orden),0)+1 FROM productos WHERE categoria_id=?');
        $q->execute([$categoria]);
        $orden=(int)$q->fetchColumn();
      }
      $q=$pdo->prepare('INSERT INTO productos (categoria_id,nombre,descripcion,precio,imagen,permite_toppings,max_toppings,toppings_gratis,orden,activo) VALUES (?,?,?,?,?,?,?,?,?,?)');
      $q->execute([$categoria,$nombre,$descripcion,$precio,$newImage,$permite,$max,$gratis,$orden,$activo]);
      $id=(int)$pdo->lastInsertId();
    } else {
      $q=$pdo->prepare('UPDATE productos SET nombre=?,descripcion=?,precio=?,imagen=?,permite_toppings=?,max_toppings=?,toppings_gratis=?,categoria_id=?,orden=?,activo=? WHERE id=?');
      $q->execute([$nombre,$descripcion,$precio,$newImage,$permite,$max,$gratis,$categoria,$orden,$activo,$id]);
    }

    $pdo->prepare('DELETE FROM producto_toppings WHERE producto_id=?')->execute([$id]);
    if($permite && !empty($_POST['toppings']) && is_array($_POST['toppings'])){
      $ins=$pdo->prepare('INSERT INTO producto_toppings(producto_id,topping_id) VALUES(?,?)');
      foreach(array_slice(array_unique(array_map('intval',$_POST['toppings'])),0,50) as $tid) $ins->execute([$id,$tid]);
    }

    // Si se cambió/subió la imagen principal, también queda disponible en la galería.
    if($newImage!==''){
      $q=$pdo->prepare('SELECT id FROM producto_imagenes WHERE producto_id=? AND imagen=? LIMIT 1');
      $q->execute([$id,$newImage]);
      if(!$q->fetchColumn()){
        $pdo->prepare('INSERT INTO producto_imagenes(producto_id,imagen,orden,activa) VALUES(?,?,0,1)')->execute([$id,$newImage]);
      }
    }

    // En un producto nuevo, las imágenes adicionales se pueden cargar en la misma pantalla.
    $files=normalize_files_array($_FILES['imagenes']??[]);
    if($files){
      $qMax=$pdo->prepare('SELECT COALESCE(MAX(orden),0) FROM producto_imagenes WHERE producto_id=?');
      $qMax->execute([$id]);
      $nextOrder=(int)$qMax->fetchColumn()+1;
      $ins=$pdo->prepare('INSERT INTO producto_imagenes(producto_id,imagen,orden,activa) VALUES(?,?,?,1)');
      foreach($files as $file){
        if(($file['error']??UPLOAD_ERR_NO_FILE)===UPLOAD_ERR_NO_FILE) continue;
        $url=product_upload($pdo,$file,$nombre);
        $ins->execute([$id,$url,$nextOrder++]);
      }
    }

    $pdo->commit();
    header('Location:index.php#productos');
    exit;
  } catch(Throwable $e) {
    if($pdo->inTransaction()) $pdo->rollBack();
    exit('No se pudo guardar el producto: '.h($e->getMessage()));
  }
}

$name=$p['nombre']??'';
$desc=$p['descripcion']??'';
$price=$p['precio']??'0.00';
$image=$p['imagen']??'';
$catId=(int)($p['categoria_id']??($cats[0]['id']??0));
$order=(int)($p['orden']??0);
$active=(int)($p['activo']??1);
$allows=(int)($p['permite_toppings']??0);
$maxT=(int)($p['max_toppings']??1);
$galleryCount=count($gallery);
?><!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=h($isNew?'Nuevo producto':'Editar producto')?> · Snackliciosos</title><link rel="stylesheet" href="style.css?v=11"><link rel="stylesheet" href="catalogo.css?v=13"></head><body>
<?php require __DIR__.'/sidebar.php'; ?>
<main class="wrap admin-content"><section class="hero"><div><span class="eyebrow">CATÁLOGO · CASCADA</span><h1><?=h($isNew?'Nuevo producto':'Editar producto')?></h1><p>Una etapa a la vez. Completa lo necesario y revisa antes de guardar.</p></div></section>
<section class="card cascade-card">
<form class="form" id="productWizard" method="post" enctype="multipart/form-data"><input type="hidden" name="csrf" value="<?=h(csrf())?>"><input type="hidden" name="action" value="save_product">
<div class="wizard-progress" aria-label="Progreso"><span class="wizard-step-dot active"></span><span class="wizard-step-dot"></span><span class="wizard-step-dot"></span><span class="wizard-step-dot"></span><span class="wizard-step-dot"></span></div>
<div class="wizard-pane active" data-step="1"><span class="eyebrow">01 · PRODUCTO</span><h2 class="wizard-question">¿Qué producto vas a agregar?</h2><p class="wizard-help">Empieza con el nombre y una descripción breve.</p><label>Nombre<input id="productName" name="nombre" value="<?=h($name)?>" required autofocus placeholder="Ej. Fresas especiales Ferrero"></label><label>Descripción<textarea id="productDesc" name="descripcion" rows="3" placeholder="Descripción opcional"><?=h($desc)?></textarea></label><div class="wizard-actions"><a class="btn btn-light" href="index.php#productos">Cancelar</a><button class="btn btn-primary" type="button" data-next>Continuar →</button></div></div>
<div class="wizard-pane" data-step="2"><span class="eyebrow">02 · PRECIO</span><h2 class="wizard-question">¿Cuánto cuesta?</h2><p class="wizard-help">El precio se reflejará automáticamente en el TPV y el menú cliente.</p><label>Precio<input id="productPrice" name="precio" type="number" step="0.01" min="0" value="<?=h($price)?>" required></label><div class="wizard-summary">Precio actual: <span class="live-price" id="pricePreview"><?=money($price)?></span></div><div class="wizard-actions"><button class="btn btn-light" type="button" data-prev>← Atrás</button><button class="btn btn-primary" type="button" data-next>Continuar →</button></div></div>
<div class="wizard-pane" data-step="3"><span class="eyebrow">03 · ORGANIZACIÓN</span><h2 class="wizard-question">¿Dónde pertenece?</h2><p class="wizard-help">Elige una categoría existente. La selección actual se marca visualmente.</p><div class="choice-grid" id="categoryChoices"><?php foreach($cats as $c):?><div class="choice-card"><input id="cat<?=$c['id']?>" type="radio" name="categoria_id" value="<?=$c['id']?>" <?=$c['id']===$catId?'checked':''?> required><label for="cat<?=$c['id']?>">🗂️ <?=h($c['nombre'])?></label></div><?php endforeach;?></div><label class="cascade-section">Orden <input name="orden" type="number" min="0" value="<?=$order?>"><span class="field-hint">0 = colocar automáticamente al final.</span></label><div class="wizard-actions"><button class="btn btn-light" type="button" data-prev>← Atrás</button><button class="btn btn-primary" type="button" data-next>Continuar →</button></div></div>
<div class="wizard-pane" data-step="4"><span class="eyebrow">04 · PERSONALIZACIÓN</span><h2 class="wizard-question">¿Necesita toppings?</h2><p class="wizard-help">Solo configuramos complementos si este producto los permite.</p><label class="check"><input type="checkbox" name="permite_toppings" id="permiteToppings" <?=$allows?'checked':''?>> Este producto permite toppings</label><div id="toppingConfigPane" class="cascade-section"><label>Máximo de toppings<select name="max_toppings" id="maxToppings"><?php for($i=1;$i<=9;$i++):?><option value="<?=$i?>" <?=$maxT===$i?'selected':''?>><?=$i?></option><?php endfor;?></select></label><div class="ruleBox"><strong>Regla de cobro</strong><small>Con 1 topping: se cobra. Con 2 o más: los primeros 2 son gratis y los adicionales se cobran.</small></div><div class="toppingChecks" id="toppingChecks"><?php foreach($toppings as $t):?><label class="check"><input type="checkbox" name="toppings[]" value="<?=$t['id']?>" <?=in_array((int)$t['id'],$assigned,true)?'checked':''?>> <?=h($t['nombre'])?> <span>+<?=money($t['precio'])?></span></label><?php endforeach;?></div></div><div class="wizard-actions"><button class="btn btn-light" type="button" data-prev>← Atrás</button><button class="btn btn-primary" type="button" data-next>Continuar →</button></div></div>
<div class="wizard-pane" data-step="5"><span class="eyebrow">05 · REVISIÓN</span><h2 class="wizard-question">Una última mirada 👀</h2><p class="wizard-help">Revisa la configuración antes de guardar.</p><div class="wizard-summary" id="productSummary"></div><label class="cascade-section">Imagen principal<input type="file" name="imagen" id="productImage" accept="image/jpeg,image/png,image/webp"><span class="field-hint">JPG, PNG o WEBP. Máximo 4 MB. Se optimiza automáticamente.</span></label><?php if($image):?><div class="productImagePreview"><span>Imagen actual</span><img src="<?=h($image)?>" alt="<?=h($name)?>"></div><?php endif;?><label class="cascade-section gallery-upload-field">Fotografías adicionales<input type="file" name="imagenes[]" id="productGalleryImages" accept="image/jpeg,image/png,image/webp" multiple><span class="field-hint">Puedes seleccionar varias. Cada una se optimiza a 600×600. Sin límite artificial de cantidad.</span></label><?php if($isNew):?><div class="gallery-note">💡 Al crear el producto, la primera imagen principal y las fotografías adicionales quedarán disponibles para la galería.</div><?php endif;?><label class="check"><input type="checkbox" name="activo" <?=$active?'checked':''?>> Producto activo</label><div class="wizard-actions"><button class="btn btn-light" type="button" data-prev>← Atrás</button><button class="btn btn-primary" type="submit"><?=h($isNew?'✓ CREAR PRODUCTO':'✓ GUARDAR CAMBIOS')?></button></div></div>
</form></section>
<?php if(!$isNew): ?>
<section class="card product-gallery-admin" id="galeria">
  <div class="sectionTitle"><div><span class="eyebrow">FOTOGRAFÍAS</span><h2>Galería del producto</h2><p>Administra la foto principal y las imágenes que verá el cliente.</p></div><span class="gallery-count-badge"><?=number_format($galleryCount)?> foto(s)</span></div>
  <?php if($image && !array_filter($gallery,fn($g)=>(string)$g['imagen']===(string)$image)): ?>
    <div class="gallery-legacy-notice"><div><strong>La imagen principal actual todavía no está registrada en la galería.</strong><small>Podemos incorporarla sin duplicar el archivo.</small></div><form method="post"><input type="hidden" name="csrf" value="<?=h(csrf())?>"><input type="hidden" name="action" value="sync_primary"><button class="btn btn-secondary" type="submit">＋ Incorporar principal</button></form></div>
  <?php endif; ?>
  <form class="gallery-upload-box" method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?=h(csrf())?>"><input type="hidden" name="action" value="upload_gallery">
    <label class="gallery-drop"><span class="gallery-drop-icon">📸</span><strong>Agregar fotografías</strong><small>Selecciona una o varias imágenes · JPG, PNG o WEBP · máximo 4 MB por imagen</small><input type="file" name="imagenes[]" accept="image/jpeg,image/png,image/webp" multiple required></label>
    <button class="btn btn-primary" type="submit">＋ SUBIR A LA GALERÍA</button>
  </form>
  <?php if($gallery): ?>
    <form method="post" id="galleryOrderForm">
      <input type="hidden" name="csrf" value="<?=h(csrf())?>"><input type="hidden" name="action" value="reorder_gallery">
      <div class="gallery-admin-grid" id="galleryAdminGrid">
      <?php foreach($gallery as $g): $isPrimary=(string)$g['imagen']===(string)$image; ?>
        <article class="gallery-admin-card <?=((int)$g['activa']?'':'is-disabled')?>" draggable="true" data-id="<?=$g['id']?>">
          <div class="gallery-admin-image"><img src="<?=h($g['imagen'])?>" alt="<?=h($name)?>"><span class="gallery-order">#<?=((int)$g['orden']+1)?></span><?php if($isPrimary):?><span class="gallery-primary">PRINCIPAL</span><?php endif;?></div>
          <div class="gallery-admin-body"><div class="gallery-admin-title"><strong>Foto <?=((int)$g['orden']+1)?></strong><span class="gallery-active <?=((int)$g['activa']?'on':'off')?>"><?=((int)$g['activa']?'Visible':'Oculta')?></span></div><small>Arrastra para ordenar</small><div class="gallery-admin-actions">
            <?php if(!$isPrimary): ?><button class="btn btn-light btn-mini" type="submit" formaction="producto.php?id=<?=$id?>" formmethod="post" name="action" value="set_primary" onclick="this.form.insertAdjacentHTML('beforeend','<input type=hidden name=image_id value=<?=$g['id']?>>');">★ Principal</button><?php endif; ?>
            <button class="btn btn-light btn-mini" type="submit" formaction="producto.php?id=<?=$id?>" formmethod="post" name="action" value="toggle_gallery" onclick="this.form.insertAdjacentHTML('beforeend','<input type=hidden name=image_id value=<?=$g['id']?>>');"><?=((int)$g['activa']?'Ocultar':'Activar')?></button>
            <button class="btn btn-danger btn-mini" type="submit" formaction="producto.php?id=<?=$id?>" formmethod="post" name="action" value="delete_gallery" onclick="if(!confirm('¿Eliminar esta referencia de la galería? El archivo físico no se borra.')) return false; this.form.insertAdjacentHTML('beforeend','<input type=hidden name=image_id value=<?=$g['id']?>>'); return true;">Eliminar</button>
          </div></div>
          <input type="hidden" name="gallery_order[]" value="<?=$g['id']?>">
        </article>
      <?php endforeach; ?>
      </div>
      <div class="gallery-order-actions"><span>↕ Arrastra las tarjetas para cambiar el orden.</span><button class="btn btn-secondary" type="submit">Guardar orden</button></div>
    </form>
  <?php else: ?><div class="gallery-empty"><div>🖼️</div><strong>La galería todavía está vacía</strong><span>Sube una o varias fotografías para empezar.</span></div><?php endif; ?>
</section>
<?php endif; ?>
</main>
<script>
(function(){
 const form=document.getElementById('productWizard'), panes=[...document.querySelectorAll('.wizard-pane')], dots=[...document.querySelectorAll('.wizard-step-dot')]; let step=0;
 const name=document.getElementById('productName'),price=document.getElementById('productPrice'),preview=document.getElementById('pricePreview'),allow=document.getElementById('permiteToppings'),toppingPane=document.getElementById('toppingConfigPane');
 function validCurrent(){const fields=[...panes[step].querySelectorAll('input[required],select[required],textarea[required]')]; for(const f of fields) if(!f.checkValidity()){f.reportValidity();return false;} return true;}
 function render(){panes.forEach((p,i)=>p.classList.toggle('active',i===step));dots.forEach((d,i)=>d.classList.toggle('active',i<=step));if(step===4){const cat=form.querySelector('input[name="categoria_id"]:checked');document.getElementById('productSummary').innerHTML='<strong>'+esc(name.value||'Sin nombre')+'</strong><br><span>Precio: '+esc(preview.textContent)+'</span><br><span>Categoría: '+esc(cat?.nextElementSibling?.textContent.trim()||'Sin categoría')+'</span><br><span>Toppings: '+(allow.checked?'Sí, hasta '+document.getElementById('maxToppings').value:'No')+'</span>';} window.scrollTo({top:0,behavior:'smooth'});}
 function esc(v){return String(v).replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));}
 form.querySelectorAll('[data-next]').forEach(b=>b.addEventListener('click',()=>{if(validCurrent()&&step<4){step++;render();}})); form.querySelectorAll('[data-prev]').forEach(b=>b.addEventListener('click',()=>{if(step>0){step--;render();}}));
 price?.addEventListener('input',()=>{const n=parseFloat(price.value)||0;preview.textContent=new Intl.NumberFormat('es-MX',{style:'currency',currency:'MXN'}).format(n);});
 function syncToppings(){toppingPane.style.display=allow.checked?'block':'none';if(!allow.checked)document.querySelectorAll('#toppingChecks input').forEach(x=>x.checked=false);} allow?.addEventListener('change',syncToppings);syncToppings();
 form.addEventListener('keydown',e=>{if(e.key==='Enter'&&e.target.tagName!=='TEXTAREA'){e.preventDefault();const next=panes[step].querySelector('[data-next]');if(next)next.click();}}); render();
})();
(function(){
 const grid=document.getElementById('galleryAdminGrid'); if(!grid) return;
 let dragged=null;
 grid.querySelectorAll('.gallery-admin-card').forEach(card=>{
   card.addEventListener('dragstart',()=>{dragged=card;card.classList.add('is-dragging');});
   card.addEventListener('dragend',()=>{card.classList.remove('is-dragging');dragged=null;sync();});
   card.addEventListener('dragover',e=>{e.preventDefault();if(!dragged||dragged===card)return;const r=card.getBoundingClientRect();const after=e.clientY>r.top+r.height/2;if(after)card.after(dragged);else card.before(dragged);});
 });
 function sync(){grid.querySelectorAll('.gallery-admin-card').forEach((c,i)=>{const n=c.querySelector('.gallery-order');if(n)n.textContent='#'+(i+1);const input=c.querySelector('input[name="gallery_order[]"]');if(input)input.value=c.dataset.id;});}
 sync();
})();
</script></body></html>
