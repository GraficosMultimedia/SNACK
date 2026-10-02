/* SnackTPV · Pedido directo del menú cliente
 * Pedido local + resumen tipo ticket + WhatsApp.
 * No registra una venta en caja: el cliente confirma el pedido por WhatsApp.
 */
(function(){
  'use strict';

  const data = window.MENU_DATA || {categories:[], toppings:[], business:{}};
  const money = n => '$' + Number(n || 0).toFixed(2);
  const esc = s => String(s ?? '').replace(/[&<>'"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[c]));
  const allProducts = () => (data.categories || []).flatMap(c => (c.productos || []).map(p => ({...p, categoryName:c.nombre})));
  const getProduct = id => allProducts().find(p => Number(p.id) === Number(id));
  const toppingMap = () => new Map((data.toppings || []).map(t => [Number(t.id), t]));
  const waDigits = phone => String(phone || '').replace(/\D/g,'');

  let cart = [];
  let selectedProduct = null;
  let selectedToppings = [];
  let payment = null;
  let cashBill = 0;

  const $ = id => document.getElementById(id);

  function freePerUnit(product){
    const n = Number(product?.toppings_gratis ?? product?.max_toppings ?? 0);
    return n > 0 ? n : 0;
  }

  function lineToppingCharge(line){
    const free = freePerUnit(line.product);
    const qty = Math.max(1, Number(line.cantidad || 1));
    return (line.toppings || []).reduce((sum,t,i) => sum + (i < free ? 0 : Number(t.precio || 0)), 0) * qty;
  }

  function lineTotal(line){
    const qty = Math.max(1, Number(line.cantidad || 1));
    return Number(line.product.precio || 0) * qty + lineToppingCharge(line);
  }

  function cartTotal(){ return cart.reduce((s,l)=>s+lineTotal(l),0); }
  function cartCount(){ return cart.reduce((s,l)=>s+Math.max(1,Number(l.cantidad||1)),0); }

  function freeForLine(line){
    const free = freePerUnit(line.product);
    return Math.min((line.toppings || []).length, free);
  }

  function openOrder(){
    renderOrder();
    const modal = $('orderModal'), back = $('orderBackdrop');
    if(!modal || !back) return;
    modal.hidden = false; back.hidden = false; document.body.classList.add('modalOpen');
    requestAnimationFrame(()=>{modal.classList.add('isOpen');back.classList.add('isOpen');});
  }

  function closeOrder(){
    const modal = $('orderModal'), back = $('orderBackdrop');
    if(!modal || !back) return;
    modal.classList.remove('isOpen'); back.classList.remove('isOpen'); document.body.classList.remove('modalOpen');
    setTimeout(()=>{modal.hidden=true;back.hidden=true;},220);
  }

  function openTopping(product){
    selectedProduct = product;
    selectedToppings = [];
    const modal=$('menuToppingModal'), back=$('toppingBackdrop');
    if(!modal || !back) return;
    $('menuToppingTitle').textContent = product.nombre;
    const free = freePerUnit(product);
    $('menuToppingDesc').textContent = free ? `Incluye ${free} topping${free===1?'':'s'} gratis por producto. Los extras se cobran según su precio.` : 'Personaliza tu producto. Los toppings se cobran según su precio.';
    renderToppingPicker();
    modal.hidden=false; back.hidden=false; document.body.classList.add('modalOpen');
    requestAnimationFrame(()=>{modal.classList.add('isOpen');back.classList.add('isOpen');});
  }

  function closeTopping(){
    const modal=$('menuToppingModal'), back=$('toppingBackdrop');
    if(!modal || !back) return;
    modal.classList.remove('isOpen'); back.classList.remove('isOpen'); document.body.classList.remove('modalOpen');
    setTimeout(()=>{modal.hidden=true;back.hidden=true;},220);
  }

  function renderToppingPicker(){
    const ids = Array.isArray(selectedProduct?.toppings_ids) ? selectedProduct.toppings_ids.map(Number) : [];
    const toppings = (data.toppings || []).filter(t => ids.includes(Number(t.id)));
    const free = freePerUnit(selectedProduct);
    const grid=$('menuToppingGrid');
    grid.innerHTML = toppings.length ? toppings.map((t,i)=>`<button class="menuToppingOption ${selectedToppings.some(x=>Number(x.id)===Number(t.id))?'selected':''}" type="button" data-topping-id="${Number(t.id)}" style="--delay:${Math.min(i,10)*35}ms"><span class="menuTopIcon">${emoji(t.nombre)}</span><span><b>${esc(t.nombre)}</b><small>${i < free ? 'GRATIS' : '+'+money(t.precio)}</small></span><i>✓</i></button>`).join('') : '<div class="menuNoToppings">🍓 Este producto no tiene toppings disponibles.</div>';
    grid.querySelectorAll('[data-topping-id]').forEach(btn=>btn.addEventListener('click',()=>toggleTopping(Number(btn.dataset.toppingId))));
    renderToppingSummary();
  }

  function toggleTopping(id){
    const t = toppingMap().get(Number(id));
    if(!t) return;
    const index=selectedToppings.findIndex(x=>Number(x.id)===Number(id));
    if(index>=0) selectedToppings.splice(index,1);
    else selectedToppings.push({id:Number(t.id),nombre:String(t.nombre),precio:Number(t.precio||0)});
    renderToppingPicker();
  }

  function renderToppingSummary(){
    const free = Math.min(selectedToppings.length, freePerUnit(selectedProduct));
    const charged = selectedToppings.reduce((s,t,i)=>s+(i<free?0:Number(t.precio||0)),0);
    const total = Number(selectedProduct?.precio||0)+charged;
    $('menuToppingTotal').textContent=money(total);
    $('menuSelectedToppings').innerHTML=selectedToppings.length
      ? `<div class="menuTopSummary"><span>${selectedToppings.length} seleccionado${selectedToppings.length===1?'':'s'}</span><b>🎁 ${free} gratis${charged?' · +'+money(charged):''}</b></div><div class="menuTopChips">${selectedToppings.map((t,i)=>`<span>${emoji(t.nombre)} ${esc(t.nombre)} <b>${i<free?'GRATIS':money(t.precio)}</b></span>`).join('')}</div>`
      : '<div class="menuTopEmpty">Toca una tarjeta para agregar un topping.</div>';
    $('addCustomizedProduct').disabled = false;
  }

  function addProduct(product){
    if(Number(product.permite_toppings)===1 && Array.isArray(product.toppings_ids) && product.toppings_ids.length){
      openTopping(product); return;
    }
    addLine(product,[]);
  }

  function addCustomized(){
    if(!selectedProduct) return;
    addLine(selectedProduct, selectedToppings);
    closeTopping();
    openOrder();
  }

  function addLine(product,toppings){
    cart.push({uid:Date.now()+Math.random(),product:{...product},cantidad:1,toppings:toppings.map(t=>({...t}))});
    renderOrder();
    flashCart();
  }

  function changeQty(uid,delta){
    const line=cart.find(x=>String(x.uid)===String(uid)); if(!line)return;
    line.cantidad=Math.max(0,Number(line.cantidad||1)+Number(delta||0));
    if(line.cantidad===0) cart=cart.filter(x=>String(x.uid)!==String(uid));
    renderOrder();
  }

  function removeLine(uid){cart=cart.filter(x=>String(x.uid)!==String(uid));renderOrder();}

  function renderOrder(){
    const list=$('orderItems'), empty=$('orderEmpty'), total=$('orderTotal'), badge=$('menuCartCount'), floatingTotal=$('menuCartTotal');
    if(total) total.textContent=money(cartTotal());
    if(badge) badge.textContent=cartCount();
    if(floatingTotal) floatingTotal.textContent=money(cartTotal());
    if(!list)return;
    empty.hidden=cart.length>0;
    list.hidden=cart.length===0;
    list.innerHTML=cart.map(line=>{
      const free=freeForLine(line), qty=Math.max(1,Number(line.cantidad||1));
      return `<article class="orderLine" data-uid="${esc(line.uid)}"><div class="orderLineTop"><div><b>${esc(line.product.nombre)}</b><small>${money(line.product.precio)} × ${qty}</small></div><strong>${money(lineTotal(line))}</strong></div>${line.toppings.length?`<div class="orderLineToppings">${line.toppings.map((t,i)=>`<span>+ ${esc(t.nombre)} <b>${i<free?'GRATIS':money(t.precio)}</b></span>`).join('')}</div>`:''}<div class="orderLineBottom"><div class="orderQty"><button type="button" data-qty="-1" data-uid="${esc(line.uid)}">−</button><b>${qty}</b><button type="button" data-qty="1" data-uid="${esc(line.uid)}">+</button></div><button class="removeLine" type="button" data-remove="${esc(line.uid)}">Eliminar</button></div></article>`;
    }).join('');
    if(payment==='efectivo' && cashBill) selectBill(cashBill);
    else updatePaymentUI();
  }

  function flashCart(){
    const b=$('menuCartButton'); if(!b)return;
    b.classList.remove('cartBurst'); void b.offsetWidth; b.classList.add('cartBurst');
  }

  function selectPayment(method){
    payment=method; cashBill=0;
    document.querySelectorAll('[data-order-payment]').forEach(b=>b.classList.toggle('active',b.dataset.orderPayment===method));
    $('cashDetail').hidden=method!=='efectivo'; $('transferDetail').hidden=method!=='transferencia';
    if(method==='transferencia') renderBankData();
    updatePaymentUI();
  }

  function selectBill(amount){
    cashBill=Number(amount||0);
    document.querySelectorAll('[data-bill]').forEach(b=>b.classList.toggle('active',Number(b.dataset.bill)===cashBill));
    const total=cartTotal(), change=cashBill-total;
    $('cashSummary').innerHTML = cashBill >= total ? `<b>Pagarás con ${money(cashBill)}</b><span>Cambio estimado: <strong>${money(change)}</strong></span>` : `<b>Ese billete no alcanza.</b><span>El total es ${money(total)}.</span>`;
    updatePaymentUI();
  }

  function renderBankData(){
    const b=data.business?.transferencia||{};
    const rows=[];
    if(b.banco) rows.push(`<div><span>Banco</span><b>${esc(b.banco)}</b></div>`);
    if(b.titular) rows.push(`<div><span>Titular</span><b>${esc(b.titular)}</b></div>`);
    if(b.cuenta) rows.push(`<div><span>Cuenta</span><b>${esc(b.cuenta)}</b></div>`);
    if(b.clabe) rows.push(`<div><span>CLABE</span><b>${esc(b.clabe)}</b></div>`);
    $('bankData').innerHTML=rows.length?rows.join(''):'<div class="bankMissing">⚠️ El negocio todavía no ha configurado sus datos de transferencia.</div>';
    $('transferInstructions').textContent=b.instrucciones||'Envía el comprobante por este mismo WhatsApp después de realizar la transferencia.';
  }

  function updatePaymentUI(){
    const total=cartTotal();
    let ready=cart.length>0 && !!payment;
    if(payment==='efectivo') ready = ready && cashBill>=total;
    if(payment==='transferencia'){
      const b=data.business?.transferencia||{};
      ready = ready && !!(b.banco||b.titular||b.cuenta||b.clabe) && !!$('transferReady')?.checked;
    }
    $('sendOrderWhatsapp').disabled=!ready;
  }

  function buildMessage(){
    const business=data.business?.nombre||'Snackliciosos';
    const stamp=new Date();
    const pad=n=>String(n).padStart(2,'0');
    const orderId=`WEB-${stamp.getFullYear()}${pad(stamp.getMonth()+1)}${pad(stamp.getDate())}-${pad(stamp.getHours())}${pad(stamp.getMinutes())}${pad(stamp.getSeconds())}`;
    const name=String($('customerName')?.value||'').trim();
    const note=String($('customerNote')?.value||'').trim();
    const lines=[];
    lines.push(`🍓 *NUEVO PEDIDO · ${business}*`);
    lines.push(`Pedido: ${orderId}`);
    if(name) lines.push(`Cliente: ${name}`);
    lines.push('');
    cart.forEach(line=>{
      const free=freeForLine(line), qty=Math.max(1,Number(line.cantidad||1));
      lines.push(`${qty} × ${line.product.nombre} · ${money(lineTotal(line))}`);
      (line.toppings||[]).forEach((t,i)=>lines.push(`   • ${t.nombre} · ${i<free?'GRATIS':'+'+money(t.precio)+' c/u'}`));
    });
    lines.push('');
    lines.push(`💰 *TOTAL: ${money(cartTotal())}*`);
    if(payment==='efectivo'){
      lines.push('💵 FORMA DE PAGO: EFECTIVO');
      lines.push(`   Pagará con: ${money(cashBill)}`);
      lines.push(`   Cambio estimado: ${money(cashBill-cartTotal())}`);
    }else{
      lines.push('🏦 FORMA DE PAGO: TRANSFERENCIA');
      lines.push('   El cliente revisará y enviará comprobante por este WhatsApp.');
    }
    if(note) lines.push(`📝 Nota: ${note}`);
    lines.push('');
    lines.push('Por favor confirmar recepción del pedido.');
    return {orderId,text:lines.join('\n')};
  }

  function sendWhatsApp(){
    if($('sendOrderWhatsapp').disabled)return;
    const phone=waDigits(data.business?.whatsapp||data.business?.telefono);
    if(!phone){alert('El negocio no tiene un número de WhatsApp configurado.');return;}
    const msg=buildMessage();
    window.open(`https://wa.me/${phone}?text=${encodeURIComponent(msg.text)}`,'_blank','noopener');
    localStorage.setItem('snacktpv_last_web_order',JSON.stringify({id:msg.orderId,at:new Date().toISOString()}));
    cart=[]; payment=null; cashBill=0; $('customerName').value=''; $('customerNote').value=''; if($('transferReady'))$('transferReady').checked=false;
    closeOrder(); renderOrder();
  }

  function emoji(name){
    const n=String(name||'').toLowerCase();
    if(n.includes('oreo'))return '🍪'; if(n.includes('nuez'))return '🌰'; if(n.includes('bombon'))return '🍡'; if(n.includes('chisp'))return '🍫'; if(n.includes('lunet'))return '🌈'; if(n.includes('almendr'))return '🥜'; if(n.includes('nutella'))return '🍫'; if(n.includes('granola'))return '🥣'; if(n.includes('lechera'))return '🥛'; if(n.includes('granillo'))return '✨'; if(n.includes('kinder'))return '🍫'; if(n.includes('pay'))return '🥧'; return '🍓';
  }

  function bind(){
    document.addEventListener('click',e=>{
      const add=e.target.closest('.addMenuProduct');
      if(add){const p=getProduct(Number(add.dataset.productId));if(p)addProduct(p);return;}
      const qty=e.target.closest('[data-qty]'); if(qty){changeQty(qty.dataset.uid,Number(qty.dataset.qty));return;}
      const rem=e.target.closest('[data-remove]'); if(rem){removeLine(rem.dataset.remove);return;}
      const pay=e.target.closest('[data-order-payment]'); if(pay){selectPayment(pay.dataset.orderPayment);return;}
      const bill=e.target.closest('[data-bill]'); if(bill){selectBill(bill.dataset.bill);return;}
    });
    $('menuCartButton')?.addEventListener('click',openOrder);
    $('openOrderHero')?.addEventListener('click',openOrder);
    $('openOrderBottom')?.addEventListener('click',openOrder);
    $('closeOrder')?.addEventListener('click',closeOrder);
    $('orderBackdrop')?.addEventListener('click',closeOrder);
    $('continueShopping')?.addEventListener('click',closeOrder);
    $('closeTopping')?.addEventListener('click',closeTopping);
    $('toppingBackdrop')?.addEventListener('click',closeTopping);
    $('addCustomizedProduct')?.addEventListener('click',addCustomized);
    $('sendOrderWhatsapp')?.addEventListener('click',sendWhatsApp);
    $('transferReady')?.addEventListener('change',updatePaymentUI);
    $('customerName')?.addEventListener('input',updatePaymentUI);
    document.addEventListener('keydown',e=>{if(e.key==='Escape'){if(!$('menuToppingModal').hidden)closeTopping();else if(!$('orderModal').hidden)closeOrder();}});
    renderOrder();
  }

  window.SnackMenuOrder={open:openOrder,add:addProduct,total:cartTotal,getItems:()=>cart.map(x=>({...x,toppings:x.toppings.map(t=>({...t}))}))};
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',bind,{once:true});else bind();
})();
