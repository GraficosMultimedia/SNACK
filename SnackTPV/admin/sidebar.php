<?php
$current = basename($_SERVER['PHP_SELF'] ?? 'index.php');
$active = match ($current) {
    'producto.php' => 'productos',
    'categoria.php' => 'categorias',
    'topping.php' => 'toppings',
    'corte.php' => 'corte',
    'configuracion.php' => 'negocio',
    'index.php' => 'inicio',
    default => '',
};
?>
<button class="menu-toggle" id="menuToggle" type="button" aria-label="Abrir menú">☰</button>
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-brand">
        <div class="brand-icon">🍓</div>
        <div class="brand-text"><strong>SNACKLICIOSOS</strong><span>Panel de administración</span></div>
        <button class="sidebar-close" id="sidebarClose" type="button" aria-label="Cerrar menú">×</button>
    </div>
    <nav class="sidebar-nav" aria-label="Navegación administrativa">
        <a href="index.php" class="sidebar-link <?= $active==='inicio'?'active':'' ?>"><span class="nav-icon">🏠</span><span>Inicio</span></a>
        <a href="index.php#productos" class="sidebar-link <?= $active==='productos'?'active':'' ?>"><span class="nav-icon">🛒</span><span>Productos</span></a>
        <a href="index.php#categorias" class="sidebar-link <?= $active==='categorias'?'active':'' ?>"><span class="nav-icon">🗂️</span><span>Categorías</span></a>
        <a href="index.php#toppings" class="sidebar-link <?= $active==='toppings'?'active':'' ?>"><span class="nav-icon">🍫</span><span>Toppings</span></a>
        <a href="index.php#ventas" class="sidebar-link"><span class="nav-icon">💰</span><span>Ventas</span></a>
        <a href="corte.php" class="sidebar-link <?= $active==='corte'?'active':'' ?>"><span class="nav-icon">🧾</span><span>Corte de caja</span></a>
        <div class="sidebar-divider"></div>
        <a href="configuracion.php" class="sidebar-link <?= $active==='negocio'?'active':'' ?>"><span class="nav-icon">⚙️</span><span>Datos del negocio</span></a>
        <a href="../menu/" target="_blank" rel="noopener" class="sidebar-link"><span class="nav-icon">👁️</span><span>Vista cliente</span></a>
        <a href="../" class="sidebar-link"><span class="nav-icon">🖥️</span><span>TPV</span></a>
    </nav>
    <div class="sidebar-bottom"><a href="logout.php" class="sidebar-link logout"><span class="nav-icon">🚪</span><span>Cerrar sesión</span></a></div>
</aside>
<script>
(function(){
    const sidebar=document.getElementById('adminSidebar');
    const toggle=document.getElementById('menuToggle');
    const close=document.getElementById('sidebarClose');
    const overlay=document.getElementById('sidebarOverlay');
    if(!sidebar||!toggle||!close||!overlay)return;
    function open(){sidebar.classList.add('open');overlay.classList.add('open');document.body.classList.add('sidebar-open');}
    function shut(){sidebar.classList.remove('open');overlay.classList.remove('open');document.body.classList.remove('sidebar-open');}
    toggle.addEventListener('click',open); close.addEventListener('click',shut); overlay.addEventListener('click',shut);
    document.querySelectorAll('.sidebar-link').forEach(a=>a.addEventListener('click',()=>{if(window.innerWidth<=900)shut();}));
})();
</script>
