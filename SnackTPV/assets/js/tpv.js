const P = window.TPV.products || [];
const T = window.TPV.toppings || [];
let selected = null;
let selectedToppings = [];
let toppingMode = false;
let lastSale = null;
let paymentMethod = null;

const $ = (s, root = document) => root.querySelector(s);
const $$ = (s, root = document) => [...root.querySelectorAll(s)];
const money = n => '$' + Number(n || 0).toFixed(2);

function esc(s){
    return String(s ?? '').replace(/[&<>'"]/g,c=>({
        '&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'
    }[c]));
}

function freeToppingCount(count, product = selected, quantity = 1){
    const perUnit = Number(product?.toppings_gratis ?? product?.max_toppings ?? 2);
    const qty = Math.max(1, Number(quantity || 1));
    if (!perUnit || count <= 0) return 0;
    return Math.min(count, perUnit * qty);
}

function renderProducts(cat){
    const grid = $('#productGrid');
    if (!grid) return;
    const products = P.filter(p => String(p.categoria_id) === String(cat));
    grid.innerHTML = products.map(p => `
        <button class="prod" type="button" data-product-id="${Number(p.id)}">
            ${p.imagen
                ? `<div class="prodImage"><img src="${esc(p.imagen)}" alt="${esc(p.nombre)}" loading="lazy"><span class="prodPlus">+</span></div>`
                : `<div class="prodImage placeholder"><span>🍓</span><span class="prodPlus">+</span></div>`}
            <div class="prodBody">
                <div class="prodName">${esc(p.nombre)}</div>
                <div class="price">${money(p.precio)}</div>
            </div>
        </button>`).join('') || '<div class="empty">No hay productos activos.</div>';
}

function choose(id){
    selected = P.find(p => p.id == id);
    selectedToppings = [];
    toppingMode = false;
    if (!selected) return;

    $('#mName').textContent = selected.nombre;
    $('#mDesc').textContent = selected.descripcion || '';

    if (Number(selected.permite_toppings) === 1 && Number(selected.max_toppings) > 0){
        renderToppingChooser();
        show('productModal');
    } else {
        addSelected();
    }
}

function renderToppingChooser(){
    const allowedIds = selected && Array.isArray(selected.toppings_ids)
        ? selected.toppings_ids.map(Number)
        : [];
    const available = T.filter(t => allowedIds.includes(Number(t.id)));
    const box = $('#toppingsBox');
    const max = Number(selected?.max_toppings || 0);
    const freeLimit = Number(selected?.toppings_gratis || 2);

    if (!available.length){
        box.innerHTML = `
            <div class="toppingEmpty">
                <span class="toppingEmptyIcon">🍓</span>
                <strong>Este producto todavía no tiene complementos.</strong>
                <small>Puedes agregarlo directamente a tu pedido.</small>
            </div>`;
        return;
    }

    box.innerHTML = `
        <section class="toppingStudio">
            <div class="toppingStudioHead">
                <div>
                    <span class="eyebrow">PERSONALIZA TU PEDIDO</span>
                    <h3>Hazlo a tu gusto ✨</h3>
                    <p>Incluye ${freeLimit} gratis por producto. Los extras se cobran según su precio.</p>
                </div>
                <div class="toppingCounter" id="toppingCounter"><b>0</b><span>de ${max}</span></div>
            </div>

            <div class="toppingChoiceModern" role="group" aria-label="Agregar toppings">
                <button type="button" class="toppingChoiceModernBtn active" id="topNo">
                    <span>Ahora no</span><small>Sin toppings</small>
                </button>
                <button type="button" class="toppingChoiceModernBtn" id="topYes">
                    <span>¡Sí, quiero!</span><small>Personalizar</small>
                </button>
            </div>

            <div id="toppingPicker" class="toppingPickerModern hidden">
                <div class="toppingRuleModern">
                    <span class="ruleIcon">🎁</span>
                    <span><b>${freeLimit} topping${freeLimit === 1 ? '' : 's'} gratis por producto.</b> Los toppings extra se cobran según su precio.</span>
                </div>
                <div class="toppingGridModern" id="toppingGridModern">
                    ${available.map((t, index) => `
                        <button type="button" class="toppingOption" data-topping-id="${Number(t.id)}" style="--delay:${Math.min(index,9)*45}ms">
                            <span class="toppingOptionIcon">${toppingEmoji(t.nombre)}</span>
                            <span class="toppingOptionInfo">
                                <b>${esc(t.nombre)}</b>
                                <small class="toppingOptionPrice">+${money(t.precio)}</small>
                            </span>
                            <span class="toppingOptionCheck">✓</span>
                        </button>`).join('')}
                </div>
                <div class="selectedToppingModern" id="selectedToppingList"></div>
            </div>
        </section>

        <div class="toppingAddBar">
            <div>
                <span>Total del producto</span>
                <strong id="toppingLivePrice">${money(selected.precio)}</strong>
            </div>
            <button type="button" class="primary" id="addBtnModern">AGREGAR AL PEDIDO · ${money(selected.precio)}</button>
        </div>`;

    $('#topNo').onclick = () => setToppingMode(false);
    $('#topYes').onclick = () => setToppingMode(true);
    $$('.toppingOption', box).forEach(btn => {
        btn.addEventListener('click', () => toggleTopping(Number(btn.dataset.toppingId)));
    });
    $('#addBtnModern').onclick = addSelected;
    renderSelectedToppings();
}

function toppingEmoji(name){
    const n = String(name).toLowerCase();
    if(n.includes('oreo')) return '🍪';
    if(n.includes('nuez')) return '🌰';
    if(n.includes('bombon')) return '🍡';
    if(n.includes('chisp')) return '🍫';
    if(n.includes('lunet')) return '🌈';
    if(n.includes('almendr')) return '🥜';
    if(n.includes('nutella')) return '🍫';
    if(n.includes('granola')) return '🥣';
    if(n.includes('lechera')) return '🥛';
    if(n.includes('granillo')) return '✨';
    return '🍓';
}

function setToppingMode(on){
    toppingMode = on;
    $('#topNo')?.classList.toggle('active', !on);
    $('#topYes')?.classList.toggle('active', on);
    $('#toppingPicker')?.classList.toggle('hidden', !on);
    if(!on) selectedToppings = [];
    renderSelectedToppings();
}

function toggleTopping(id){
    const t = T.find(x => Number(x.id) === Number(id));
    if(!t || !selected) return;
    const max = Number(selected.max_toppings || 0);
    const exists = selectedToppings.some(x => Number(x.id) === Number(id));

    if(exists){
        selectedToppings = selectedToppings.filter(x => Number(x.id) !== Number(id));
    } else {
        if(selectedToppings.length >= max){
            pulseToppingGrid();
            return;
        }
        selectedToppings.push({id:Number(t.id), nombre:String(t.nombre), precio:Number(t.precio)});
    }
    renderSelectedToppings();
}

function pulseToppingGrid(){
    const grid = $('#toppingGridModern');
    if(!grid) return;
    grid.classList.remove('limitShake');
    void grid.offsetWidth;
    grid.classList.add('limitShake');
}

function renderSelectedToppings(){
    const list = $('#selectedToppingList');
    const counter = $('#toppingCounter');
    const live = $('#toppingLivePrice');
    const add = $('#addBtnModern');
    if(!list) return;

    const count = selectedToppings.length;
    const free = freeToppingCount(count, selected, 1);
    const chargedExtra = selectedToppings.reduce((sum,t,i) => sum + (i < free ? 0 : Number(t.precio || 0)), 0);
    const total = Number(selected?.precio || 0) + chargedExtra;

    if(counter){
        counter.querySelector('b').textContent = count;
        const extrasCount = Math.max(0, count - free);
        counter.querySelector('span').textContent = extrasCount
            ? `${free} gratis + ${extrasCount} extra${extrasCount === 1 ? '' : 's'}`
            : `${free} gratis`;
        counter.classList.remove('is-full');
    }

    list.innerHTML = count
        ? `<div class="selectedSummary"><span>${count} seleccionado${count===1?'':'s'}</span><b>${free ? `🎁 ${free} GRATIS${chargedExtra ? ' · +'+money(chargedExtra) : ''}` : '+'+money(chargedExtra)}</b></div>
           <div class="selectedChips">${selectedToppings.map((t,i)=>`
             <span class="selectedChip pop-in">
                <span>${toppingEmoji(t.nombre)} ${esc(t.nombre)}</span>
                <b>${i < free ? 'GRATIS' : money(t.precio)}</b>
                <button type="button" aria-label="Quitar ${esc(t.nombre)}" onclick="removeSelectedTopping(${Number(t.id)})">×</button>
             </span>`).join('')}</div>`
        : `<div class="selectedEmpty">Toca una tarjeta para añadirla a tu pedido.</div>`;

    $$('.toppingOption').forEach(btn => {
        const id = Number(btn.dataset.toppingId);
        const index = selectedToppings.findIndex(x => Number(x.id) === id);
        const isSelected = index >= 0;
        btn.classList.toggle('selected', isSelected);
        const price = $('.toppingOptionPrice', btn);
        if(price){
            price.textContent = isSelected && index < free
                ? 'GRATIS'
                : '+' + money(Number(T.find(x=>Number(x.id)===id)?.precio || 0));
        }
    });

    if(live) live.textContent = money(total);
    if(add){
        add.textContent = `AGREGAR AL PEDIDO · ${money(total)}`;
        add.classList.toggle('is-ready', count > 0);
    }
}

function removeSelectedTopping(id){
    selectedToppings = selectedToppings.filter(t => Number(t.id) !== Number(id));
    renderSelectedToppings();
}

function addSelected(){
    if(!selected || !window.SnackCart) return;
    window.SnackCart.add({
        uid:Date.now()+Math.random(),
        product_id:selected.id,
        nombre:selected.nombre,
        precio:Number(selected.precio),
        toppings:[...selectedToppings],
        cantidad:1,
        toppings_gratis:Number(selected.toppings_gratis ?? selected.max_toppings ?? 2),
        max_toppings:Number(selected.max_toppings ?? selected.toppings_gratis ?? 2)
    });
    hide('productModal');
}

function show(id){ $('#'+id)?.classList.add('show'); }
function hide(id){ $('#'+id)?.classList.remove('show'); }

function openPay(){
    if(!window.SnackCart || !window.SnackCart.getItems().length) return;
    paymentMethod = null;
    const total = window.SnackCart.total();
    $('#payTotal').textContent = money(total);
    $('#cash').value = '';
    $('#change').textContent = money(0);
    $('#confirmCash').disabled = true;
    $$('.payStep').forEach(s => { s.classList.remove('active'); s.setAttribute('aria-expanded','false'); });
    $$('.payPanel').forEach(p => p.classList.remove('open'));
    show('payModal');
}

function selectPayment(method){
    paymentMethod = method;
    $$('.payStep').forEach(step => {
        const active = step.dataset.method === method;
        step.classList.toggle('active', active);
        step.setAttribute('aria-expanded', active ? 'true' : 'false');
    });
    $$('.payPanel').forEach(panel => panel.classList.toggle('open', panel.dataset.panel === method));
    if(method === 'efectivo') setTimeout(() => $('#cash')?.focus(), 140);
}

async function confirmPayment(method){
    if(!window.SnackCart || !window.SnackCart.getItems().length) return;
    const total = window.SnackCart.total();
    const cash = method === 'efectivo' ? Number($('#cash').value || 0) : 0;
    if(method === 'efectivo' && cash < total){ updateChange(); return; }

    const button = method === 'transferencia' ? $('#confirmTransfer') : $('#confirmCash');
    if(button) button.disabled = true;

    try{
        const r = await fetch('api/venta.php', {
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body:JSON.stringify({
                cart:window.SnackCart.getItems(),
                metodo_pago:method,
                efectivo:cash
            })
        });
        const j = await r.json();
        if(!j.ok) throw new Error(j.error || 'No se pudo registrar la venta.');

        lastSale = {
            ...j,
            items:window.SnackCart.getItems(),
            businessName:window.TPV.business?.name || 'Snackliciosos',
            date:new Date()
        };
        hide('payModal');
        const paymentLabel = method === 'transferencia' ? 'Transferencia' : `Efectivo · Cambio ${money(j.cambio)}`;
        $('#doneText').textContent = `Folio ${j.folio} · Total ${money(j.total)} · ${paymentLabel}`;
        show('doneModal');
    }catch(e){
        alert(e.message || 'No fue posible conectar con el servidor.');
    }finally{
        if(button) button.disabled = false;
    }
}

function updateChange(){
    const cash = Number($('#cash')?.value || 0);
    const total = window.SnackCart?.total() || 0;
    const d = cash - total;
    $('#change').textContent = money(Math.max(0,d));
    $('#confirmCash').disabled = cash < total || !window.SnackCart?.getItems().length;
}

/* =========================================================
   TICKET
   El encabezado se genera aquí desde Administración > Configuración.
   ========================================================= */
function buildTicket(){
    if(!lastSale) return;

    const s = lastSale;
    const business = window.TPV?.business || {};

    const businessName = business.name || s.businessName || 'Snackliciosos';
    const businessLogo = business.logo || '';
    const businessAddress = business.address || '';
    const businessPhone = business.phone || '';

    const logoHtml = businessLogo
        ? `<img class="ticketLogoImg" src="${esc(businessLogo)}" alt="${esc(businessName)}">`
        : `<div class="ticketLogo">🍓</div>`;

    let html = `<div class="ticket">
        <div class="ticketHeader">
            ${logoHtml}
            <h1>${esc(businessName)}</h1>
            ${businessAddress ? `<div class="ticketBusinessData">${esc(businessAddress)}</div>` : ''}
            ${businessPhone ? `<div class="ticketBusinessData">Tel. ${esc(businessPhone)}</div>` : ''}
            <div class="ticketTitle">TICKET DE VENTA</div>
        </div>
        <div class="ticketMeta">
            <span>Folio</span><b>${esc(s.folio)}</b>
            <span>Fecha</span><b>${new Date(s.date).toLocaleString('es-MX')}</b>
        </div>
        <div class="ticketDivider"></div>`;

    s.items.forEach(x => {
        const free = freeToppingCount(
            x.toppings.length,
            P.find(p => Number(p.id) === Number(x.product_id)) || selected
        );

        html += `<div class="ticketProduct">
            <div>
                <b>${esc(x.nombre)}</b>
                <span>${x.cantidad} × ${money(x.precio)}</span>
            </div>
            <strong>${money(window.SnackCart.itemTotal(x))}</strong>
        </div>`;

        x.toppings.forEach((t,i) => {
            html += `<div class="ticketTopping">↳ ${esc(t.nombre)} <span>${i < free ? 'GRATIS' : '+'+money(t.precio)}</span></div>`;
        });
    });

    const transfer = s.metodo_pago === 'transferencia';

    html += `<div class="ticketDivider"></div>
        <div class="ticketRow"><span>TOTAL</span><b>${money(s.total)}</b></div>
        <div class="ticketRow"><span>FORMA DE PAGO</span><b>${transfer ? 'TRANSFERENCIA' : 'EFECTIVO'}</b></div>
        ${transfer ? '' : `
            <div class="ticketRow"><span>EFECTIVO</span><b>${money(s.efectivo)}</b></div>
            <div class="ticketRow changeRow"><span>CAMBIO</span><b>${money(s.cambio)}</b></div>
        `}
        <div class="ticketThanks">¡Gracias por tu compra! ✨</div>
    </div>`;

    $('#ticketContent').innerHTML = html;
}

function openTicket(){ buildTicket(); hide('doneModal'); show('ticketModal'); }
function resetSale(){ window.SnackCart.clear(); lastSale=null; hide('doneModal'); hide('ticketModal'); window.SnackCart.close(); }

function printTicket(){
    buildTicket();
    const ticket = document.querySelector('#ticketContent .ticket');
    if(!ticket) return;
    const printWindow = window.open('', 'snackliciosos_ticket_80mm', 'width=420,height=800,menubar=no,toolbar=no,location=no,status=no,resizable=yes,scrollbars=yes');
    if(!printWindow){ alert('El navegador bloqueó la ventana de impresión. Permite ventanas emergentes para este sitio.'); return; }
    const css = `@page{size:80mm auto;margin:0;}*{box-sizing:border-box;}html,body{margin:0!important;padding:0!important;width:80mm!important;background:#fff!important;}body{font-family:"Courier New",monospace;color:#111}.thermal-sheet{width:80mm;padding:2mm}.ticket{width:76mm;margin:auto;font-size:11px;line-height:1.28}.ticketHeader{text-align:center}.ticketLogo{width:34px;height:34px;margin:auto;border-radius:50%;display:grid;place-items:center;background:#ffe7f1;font-size:19px}.ticketLogoImg{display:block;width:28mm;max-width:28mm;max-height:18mm;object-fit:contain;margin:0 auto 3px}.ticketHeader h1{margin:4px 0 0;font-family:Arial,sans-serif;font-size:19px;color:#f20b78}.ticketBusinessData{margin-top:2px;font-size:8.5px;line-height:1.2;overflow-wrap:anywhere}.ticketTitle{font-size:9px;font-weight:900}.ticketMeta{display:grid;grid-template-columns:auto 1fr;gap:2px 7px;margin-top:7px;font-size:9px}.ticketMeta b{text-align:right}.ticketDivider{border-top:1px dashed #666;margin:8px 0}.ticketProduct{display:grid;grid-template-columns:1fr auto;gap:7px;margin-top:5px}.ticketProduct div{display:flex;flex-direction:column}.ticketProduct b{font-size:10px}.ticketProduct span,.ticketTopping{font-size:8.5px}.ticketTopping{padding-left:9px;display:flex;justify-content:space-between}.ticketTopping span{font-weight:900;color:#16834d}.ticketRow{display:flex;justify-content:space-between;gap:8px;margin:4px 0;font-size:10px}.ticketRow b{font-size:11px}.changeRow{padding:5px;background:#eaf8f0}.ticketThanks{text-align:center;margin-top:11px;padding-top:8px;border-top:1px dashed #777;color:#f20b78;font-weight:800}`;
    printWindow.document.open();
    printWindow.document.write(`<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Ticket Snackliciosos</title><style>${css}</style></head><body><main class="thermal-sheet">${ticket.outerHTML}</main></body></html>`);
    printWindow.document.close();
    printWindow.focus();
    setTimeout(()=>{ try{ printWindow.print(); }catch(e){} },350);
    printWindow.addEventListener('afterprint',()=>setTimeout(()=>{try{printWindow.close()}catch(e){}},300));
}

function bindUI(){
    $$('.cat').forEach(btn => btn.addEventListener('click', () => {
        $$('.cat').forEach(x => x.classList.remove('active'));
        btn.classList.add('active');
        renderProducts(btn.dataset.cat);
    }));

    const productGrid = $('#productGrid');
    if(productGrid){
        productGrid.addEventListener('click', e => {
            const card = e.target.closest('[data-product-id]');
            if(!card || !productGrid.contains(card)) return;
            e.preventDefault();
            choose(Number(card.dataset.productId));
        });
    }

    $$('[data-close]').forEach(btn => btn.addEventListener('click', () => {
        const modal = btn.closest('.modal');
        if(modal) modal.classList.remove('show');
    }));

    $$('.modal').forEach(modal => modal.addEventListener('click', e => {
        if(e.target === modal) modal.classList.remove('show');
    }));

    $$('.payStep').forEach(step => step.addEventListener('click', () => selectPayment(step.dataset.method)));
    $$('.quick button').forEach(btn => btn.addEventListener('click', () => { $('#cash').value = btn.dataset.cash; updateChange(); }));

    $('#cash')?.addEventListener('input', updateChange);
    $('#confirmCash')?.addEventListener('click', () => confirmPayment('efectivo'));
    $('#confirmTransfer')?.addEventListener('click', () => confirmPayment('transferencia'));
    $('#viewTicket')?.addEventListener('click', openTicket);
    $('#printTicket')?.addEventListener('click', printTicket);
    $('#newSale')?.addEventListener('click', resetSale);
    $('#ticketNewSale')?.addEventListener('click', resetSale);

    const first = $('.cat.active') || $('.cat');
    if(first) renderProducts(first.dataset.cat);
}

window.openPay = openPay;
window.choose = choose;
window.removeSelectedTopping = removeSelectedTopping;

if(document.readyState === 'loading') document.addEventListener('DOMContentLoaded', bindUI, {once:true});
else bindUI();