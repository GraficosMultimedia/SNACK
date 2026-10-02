<?php
declare(strict_types=1);

/**
 * Galería de producto sin modificar el esquema SQL.
 *
 * 1) Conserva una galería opcional en uploads/productos/galeria/{id}.json.
 * 2) Además detecta automáticamente otras fotografías optimizadas del mismo
 *    producto por su nombre base dentro de uploads/productos/.
 *
 * Esto permite que las fotos nuevas subidas desde el editor actual del producto
 * entren a la galería sin agregar columnas ni tablas a la base de datos.
 */
function product_gallery_dir(int $productId): string {
    return __DIR__.'/../uploads/productos/galeria/'.$productId;
}

function product_gallery_meta_file(int $productId): string {
    return __DIR__.'/../uploads/productos/galeria/'.$productId.'.json';
}

function product_gallery_safe_base(string $name): string {
    $safe = preg_replace('/[^a-zA-Z0-9_-]+/', '-', $name) ?: 'producto';
    return trim($safe, '-_') ?: 'producto';
}

function product_gallery_extras(int $productId, string $productName=''): array {
    if ($productId <= 0) return [];
    $items=[];

    $meta=product_gallery_meta_file($productId);
    if(is_file($meta)){
        $decoded=json_decode((string)@file_get_contents($meta),true);
        if(is_array($decoded)) foreach($decoded as $url){
            $url=trim((string)$url);
            if($url!=='' && !in_array($url,$items,true)) $items[]=$url;
        }
    }

    if($productName!==''){
        $dir=__DIR__.'/../uploads/productos';
        $base=product_gallery_safe_base($productName);
        $matches=glob($dir.'/'.$base.'_*.{webp,jpg,jpeg,png}',GLOB_BRACE) ?: [];
        usort($matches,static fn($a,$b)=>(filemtime($b)?:0)<=>(filemtime($a)?:0));
        foreach($matches as $file){
            if(!is_file($file)) continue;
            $items[]=app_url('uploads/productos/'.basename($file));
        }
    }
    return array_values(array_unique($items));
}

function product_gallery(int $productId, string $mainImage='', string $productName=''): array {
    if($productId<=0) return $mainImage?[$mainImage]:[];
    $items=product_gallery_extras($productId,$productName);
    if($mainImage!=='') array_unshift($items,$mainImage);
    return array_values(array_unique($items));
}
