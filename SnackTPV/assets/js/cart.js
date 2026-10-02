/* Snackliciosos TPV - Cart Module
 * Popup responsive, independiente del layout principal.
 */
(function(){
  'use strict';

  let items = [];
  const $ = (id) => document.getElementById(id);
  const money = (n) => '$' + Number(n || 0).toFixed(2);

  function freeToppingCount(item){
    const toppings = Array.isArray(item?.toppings) ? item.toppings : [];
    const perUnit = Number(item?.toppings_gratis ?? item?.max_toppings ?? 2);
    const qty = Math.max(1, Number(item?.cantidad || 1));
    if(!perUnit || !toppings.length) return 0;
    return Math.min(toppings.length, perUnit * qty);
  }

  function itemTotal(item){
    const toppings = Array.isArray(item.toppings) ? item.toppings : [];
    const qty = Math.max(1, Number(item.cantidad || 1));
    const free = freeToppingCount(item);
    const toppingTotal = toppings.reduce((sum,t,i) => sum + (i < free ? 0 : Number(t.precio || 0)), 0);
    return (Number(item.precio || 0) + toppingTotal) * qty;
  }

  function total(){
    return items.reduce((sum,item) => sum + itemTotal(item), 0);
  }

  function count(){
    return items.reduce((sum,item) => sum + Math.max(1, Number(item.cantidad || 1)), 0);
  }

  function esc(value){
    return String(value ?? '').replace(/[&<>'"]/g, c => ({
      '&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'
    }[c]));
  }

  function render(){
    const badgeCount = $('cartCount');
    const badgeTotal = $('cartTotal');
    const panelTotal = $('panelTotal');
    const list = $('cartItems');
    const pay = $('payBtn');

    if(badgeCount) {
      badgeCount.textContent = count();
      badgeCount.classList.remove('cart-badge-pop');
      void badgeCount.offsetWidth;
      if(count() > 0) badgeCount.classList.add('cart-badge-pop');
    }
    if(badgeTotal) {
      badgeTotal.textContent = money(total());
      badgeTotal.classList.remove('cart-total-pop');
      void badgeTotal.offsetWidth;
      if(count() > 0) badgeTotal.classList.add('cart-total-pop');
    }
    if(panelTotal) panelTotal.textContent = money(total());
    if(pay) pay.disabled = items.length === 0;

    if(!list) return;

    if(!items.length){
      list.innerHTML = '<div class="cartEmptyState"><div class="cartEmptyIcon">🛒</div><strong>Tu pedido está vacío</strong><span>Agrega productos para comenzar.</span></div>';
      return;
    }

    list.innerHTML = items.map(item => {
      const toppings = Array.isArray(item.toppings) ? item.toppings : [];
      const free = freeToppingCount(item);
      const qty = Math.max(1, Number(item.cantidad || 1));
      return `
        <article class="cartLine" data-uid="${esc(item.uid)}">
          <div class="cartLineMain">
            <div class="cartLineName">${esc(item.nombre)}</div>
            <div class="cartLinePrice">${money(itemTotal(item))}</div>
          </div>
          ${toppings.length ? `<div class="cartLineToppings">${toppings.map((t,i)=>`
            <div class="cartTopping"><span>+ ${esc(t.nombre)}</span><b class="${i < free ? 'isFree' : ''}">${i < free ? 'GRATIS' : money(t.precio)}</b></div>
          `).join('')}</div>` : ''}
          <div class="cartLineActions">
            <div class="cartQty" role="group" aria-label="Cantidad">
              <button type="button" data-action="qty" data-delta="-1" data-uid="${esc(item.uid)}" aria-label="Disminuir">−</button>
              <b>${qty}</b>
              <button type="button" data-action="qty" data-delta="1" data-uid="${esc(item.uid)}" aria-label="Aumentar">+</button>
            </div>
            <button type="button" class="cartRemove" data-action="remove" data-uid="${esc(item.uid)}">Eliminar</button>
          </div>
        </article>`;
    }).join('');
    list.querySelectorAll('.cartLine').forEach((el, index) => {
      el.style.setProperty('--cart-delay', Math.min(index, 8) * 45 + 'ms');
      el.classList.add('cart-line-enter');
    });
  }

  function open(){
    const overlay = $('cartOverlay');
    if(!overlay) return;
    render();
    overlay.classList.add('is-open');
    const popup = $('cartPopup');
    if(popup){ popup.classList.remove('cart-popup-enter'); void popup.offsetWidth; popup.classList.add('cart-popup-enter'); }
    overlay.setAttribute('aria-hidden','false');
    document.body.classList.add('cart-popup-open');
    const close = $('closeCart');
    if(close) setTimeout(() => close.focus(), 20);
  }

  function close(){
    const overlay = $('cartOverlay');
    if(!overlay) return;
    overlay.classList.remove('is-open');
    overlay.setAttribute('aria-hidden','true');
    document.body.classList.remove('cart-popup-open');
  }

  function add(item){
    if(!item) return;
    items.push({
      uid: item.uid || (Date.now() + Math.random()),
      product_id: Number(item.product_id),
      nombre: String(item.nombre || ''),
      precio: Number(item.precio || 0),
      toppings: Array.isArray(item.toppings) ? item.toppings.map(t => ({id:Number(t.id),nombre:String(t.nombre || ''),precio:Number(t.precio || 0)})) : [],
      cantidad: Math.max(1, Number(item.cantidad || 1)),
      toppings_gratis: Number(item.toppings_gratis ?? item.max_toppings ?? 2),
      max_toppings: Number(item.max_toppings ?? item.toppings_gratis ?? 2)
    });
    render();
    const popupBadge = $('openCart');
    if(popupBadge){ popupBadge.classList.remove('cart-badge-burst'); void popupBadge.offsetWidth; popupBadge.classList.add('cart-badge-burst'); }
  }

  function changeQty(uid, delta){
    const item = items.find(x => String(x.uid) === String(uid));
    if(!item) return;
    item.cantidad = Math.max(0, Number(item.cantidad || 1) + Number(delta || 0));
    if(item.cantidad <= 0){
      items = items.filter(x => String(x.uid) !== String(uid));
    }
    render();
  }

  function remove(uid){
    items = items.filter(x => String(x.uid) !== String(uid));
    render();
  }

  function clear(){
    items = [];
    render();
  }

  function getItems(){
    return items.map(x => ({...x, toppings:(x.toppings||[]).map(t=>({...t}))}));
  }

  function init(){
    const overlay = $('cartOverlay');
    const openBtn = $('openCart');
    const closeBtn = $('closeCart');
    const list = $('cartItems');
    const payBtn = $('payBtn');

    if(!overlay || !openBtn || !closeBtn || !list || !payBtn){
      console.error('[SnackCart] Estructura del carrito incompleta.', {overlay, openBtn, closeBtn, list, payBtn});
      return;
    }

    openBtn.addEventListener('click', open);
    closeBtn.addEventListener('click', close);

    overlay.addEventListener('click', function(e){
      if(e.target === overlay) close();
    });

    list.addEventListener('click', function(e){
      const button = e.target.closest('[data-action]');
      if(!button) return;
      const action = button.dataset.action;
      const uid = button.dataset.uid;
      if(action === 'qty') changeQty(uid, Number(button.dataset.delta || 0));
      if(action === 'remove') remove(uid);
    });

    payBtn.addEventListener('click', function(){
      if(!items.length) return;
      close();
      if(typeof window.openPay === 'function') window.openPay();
      else document.dispatchEvent(new CustomEvent('snackcart:pay'));
    });

    document.addEventListener('keydown', function(e){
      if(e.key === 'Escape' && overlay.classList.contains('is-open')) close();
    });

    render();
  }

  window.SnackCart = {
    init, open, close, add, changeQty, remove, clear, render, total, count, itemTotal, getItems,
    isOpen: () => Boolean($('cartOverlay')?.classList.contains('is-open'))
  };

  if(document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, {once:true});
  } else {
    init();
  }
})();
