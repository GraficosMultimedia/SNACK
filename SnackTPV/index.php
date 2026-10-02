<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/topping_price.php';

$pdo = db();

$cats = $pdo->query("SELECT id,nombre FROM categorias WHERE activa=1 ORDER BY orden,id")->fetchAll();

$products = $pdo->query("SELECT p.id,p.categoria_id,p.nombre,p.descripcion,p.imagen,p.precio,p.permite_toppings,p.max_toppings,p.toppings_gratis FROM productos p INNER JOIN categorias c ON c.id=p.categoria_id WHERE p.activo=1 AND c.activa=1 ORDER BY c.orden,p.orden,p.id")->fetchAll();

$toppings = $pdo->query("SELECT id,nombre,precio,activo,orden FROM toppings WHERE activo=1 ORDER BY orden,id")->fetchAll();
foreach ($toppings as &$topping) {
    $topping['precio'] = topping_effective_price($pdo, $topping);
}
unset($topping);

$pt = $pdo->query("SELECT producto_id,topping_id FROM producto_toppings ORDER BY producto_id,topping_id")->fetchAll();
$allowed = [];
foreach ($pt as $row) {
    $allowed[(int)$row['producto_id']][] = (int)$row['topping_id'];
}
foreach ($products as &$product) {
    $product['toppings_ids'] = $allowed[(int)$product['id']] ?? [];
}
unset($product);

$name = config_value($pdo, 'nombre_negocio', APP_NAME);
$logo = config_value($pdo, 'logo', '');
$address = config_value($pdo, 'direccion', '');
$phone = config_value($pdo, 'telefono', '');
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">

<meta property="og:type" content="website">
<meta property="og:url" content="https://colibriprint.com.mx/SnackTPV/menu/">
<meta property="og:title" content="Snackliciosos · Menú">
<meta property="og:description" content="Antojitos que sacan sonrisas 💗 Descubre nuestro menú y haz tu pedido por WhatsApp.">
<meta property="og:image" content="https://colibriprint.com.mx/SnackTPV/menu/assets/social-preview.jpg">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:type" content="image/jpeg">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Snackliciosos · Menú">
<meta name="twitter:description" content="Antojitos que sacan sonrisas 💗">
<meta name="twitter:image" content="https://colibriprint.com.mx/SnackTPV/menu/assets/social-preview.jpg">

<title><?= h($name) ?> · TPV</title>
<link rel="stylesheet" href="assets/css/tpv.css?v=22">
<link rel="stylesheet" href="assets/css/cart.css?v=5">
<link rel="stylesheet" href="assets/css/ticket-58mm.css?v=1">
</head>
<body>
<header class="top">
    <div class="brand">
        <?php if ($logo): ?><img src="<?= h($logo) ?>" alt=""><?php endif; ?>
        <b><?= h($name) ?></b>
    </div>
    <a class="adminLink" href="admin/">⚙️ Admin</a>
    <button class="cartBadge" id="openCart" type="button">🛒 <span id="cartCount">0</span> · <span id="cartTotal">$0.00</span></button>
</header>

<main class="layout">
    <aside class="cats">
        <?php foreach ($cats as $i => $c): ?>
            <button type="button" class="cat <?= $i === 0 ? 'active' : '' ?>" data-cat="<?= (int)$c['id'] ?>"><?= h($c['nombre']) ?></button>
        <?php endforeach; ?>
    </aside>

    <section class="products"><div class="grid" id="productGrid"></div></section>
</main>

<div class="cartOverlay" id="cartOverlay" aria-hidden="true">
    <aside class="cartPopup" id="cartPopup" role="dialog" aria-modal="true" aria-labelledby="cartTitle">
        <div class="cartHead">
            <div>
                <span class="cartEyebrow">PEDIDO ACTUAL</span>
                <h2 id="cartTitle">Tu pedido</h2>
            </div>
            <button id="closeCart" type="button" aria-label="Cerrar carrito">×</button>
        </div>
        <div id="cartItems" class="cartItems"></div>
        <div class="cartFoot">
            <div class="totalRow"><span>Total</span><strong id="panelTotal">$0.00</strong></div>
            <button class="payBtn" id="payBtn" type="button" disabled>COBRAR</button>
        </div>
    </aside>
</div>

<div class="modal" id="productModal">
    <div class="modalCard">
        <button class="x" data-close type="button">×</button>
        <h2 id="mName"></h2>
        <p id="mDesc"></p>
        <div id="toppingsBox"></div>
    </div>
</div>

<div class="modal" id="payModal">
    <div class="modalCard payCard">
        <button class="x" data-close type="button" aria-label="Cerrar pago">×</button>
        <div class="payIntro">
            <span class="eyebrow">REGISTRO DE PAGO</span>
            <h2>¿Cómo recibiste el pago?</h2>
            <div class="bigTotal" id="payTotal">$0.00</div>
        </div>

        <div class="payCascade" id="payCascade">
            <button class="payStep" type="button" data-method="efectivo" aria-expanded="false">
                <span class="payStepIcon">💵</span>
                <span><b>Efectivo</b><small>Recibe dinero y calcula el cambio</small></span>
                <span class="payChevron">⌄</span>
            </button>
            <div class="payPanel" data-panel="efectivo">
                <label class="cashLabel" for="cash">Efectivo recibido</label>
                <input id="cash" type="number" min="0" step="0.01" inputmode="decimal" placeholder="$0.00">
                <div class="quick">
                    <button type="button" data-cash="50">$50</button>
                    <button type="button" data-cash="100">$100</button>
                    <button type="button" data-cash="200">$200</button>
                    <button type="button" data-cash="500">$500</button>
                </div>
                <div class="change">Cambio <b id="change">$0.00</b></div>
                <button class="primary" id="confirmCash" type="button" disabled>✓ CONFIRMAR COBRO</button>
            </div>

            <button class="payStep" type="button" data-method="transferencia" aria-expanded="false">
                <span class="payStepIcon">🏦</span>
                <span><b>Transferencia</b><small>El dinero no entra físicamente a caja</small></span>
                <span class="payChevron">⌄</span>
            </button>
            <div class="payPanel" data-panel="transferencia">
                <div class="transferNotice">
                    <div class="transferIcon">📲</div>
                    <strong>Pago por transferencia</strong>
                    <span>Confirma que recibiste la transferencia antes de registrar la venta.</span>
                </div>
                <button class="primary transferConfirm" id="confirmTransfer" type="button">✓ CONFIRMAR TRANSFERENCIA</button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="doneModal">
    <div class="modalCard done">
        <div class="successIcon">✓</div>
        <h2>¡Venta realizada!</h2>
        <p id="doneText"></p>
        <div class="doneActions">
            <button class="primary" id="viewTicket" type="button">🧾 VER TICKET</button>
            <button class="secondaryBtn" id="newSale" type="button">NUEVA VENTA</button>
        </div>
    </div>
</div>

<div class="modal ticketModal" id="ticketModal">
    <div class="modalCard ticketCard">
        <button class="x noPrint" data-close type="button">×</button>
        <div id="ticketContent"></div>
        <div class="printHint noPrint">Selecciona tu impresora térmica en el diálogo de impresión · Formato 58 mm</div>
        <div class="ticketActions noPrint">
            <button class="primary" id="printTicket58" type="button">🖨️ IMPRIMIR TICKET 58 MM</button>
            <button class="secondaryBtn" id="ticketNewSale" type="button">NUEVA VENTA</button>
        </div>
    </div>
</div>

<script>
window.TPV = {
    products: <?= json_encode($products, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
    toppings: <?= json_encode($toppings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
    business: {
        name: <?= json_encode($name, JSON_UNESCAPED_UNICODE) ?>,
        logo: <?= json_encode($logo, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>,
        address: <?= json_encode($address, JSON_UNESCAPED_UNICODE) ?>,
        phone: <?= json_encode($phone, JSON_UNESCAPED_UNICODE) ?>
    }
};
</script>
<script src="assets/js/cart.js?v=5"></script>
<script src="assets/js/tpv.js?v=23"></script>
<script src="assets/js/ticket-print-58mm.js?v=1"></script>
</body>
</html>
