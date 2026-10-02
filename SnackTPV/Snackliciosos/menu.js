(function(){
  const money=n=>'$'+Number(n).toFixed(2);
  const esc=s=>String(s).replace(/[&<>'"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[c]));
  const grid=document.getElementById('menuGrid');
  const categories=document.getElementById('categories');
  const status=document.getElementById('syncStatus');
  function waUrl(p){const d=String(p||'').replace(/\D/g,'');return d?'https://wa.me/'+d:'';}
  function render(data){
    const cats=data.categories||[];
    grid.innerHTML=cats.map(c=>`<section class="category" id="cat-${Number(c.id)}"><h2>${esc(c.nombre)}</h2><div class="items">${(c.productos||[]).map(p=>`<article class="item"><div class="itemInfo"><h3>${esc(p.nombre)}</h3>${p.descripcion?`<p>${esc(p.descripcion)}</p>`:''}</div><span class="price">${money(p.precio)}</span></article>`).join('')}</div></section>`).join('')+
      `<section class="toppingsSection"><h2>Complementos / toppings</h2><div class="toppingList">${(data.toppings||[]).map(t=>`<div><span>${esc(t.nombre)}</span><b>+${money(t.precio)}</b></div>`).join('')}</div></section>`;
    categories.innerHTML=cats.map((c,i)=>`<a class="cat ${i===0?'active':''}" href="#cat-${Number(c.id)}">${esc(c.nombre)}</a>`).join('');
  }
  function renderBusiness(b){
    if(!b)return;
    document.title=(b.nombre||'Snackliciosos')+' · Menú';
    const set=(id,text)=>{const e=document.getElementById(id);if(e)e.textContent=text||'';};
    set('businessName',b.nombre);set('footerName',b.nombre);set('businessAddress',b.direccion);set('thanks',b.mensaje);
    const phone=document.getElementById('businessPhone'); if(phone)phone.textContent=b.telefono?'☎ '+b.telefono:'';
    const fa=document.getElementById('footerAddress');if(fa)fa.textContent=b.direccion||'';
    const fp=document.getElementById('footerPhone');if(fp){const d=String(b.telefono||'').replace(/\D/g,'');fp.textContent=b.telefono?'☎ '+b.telefono:'';fp.href=d?'tel:'+d:'#';}
    const fi=document.getElementById('footerInstagram');if(fi)fi.textContent=b.instagram?'📷 '+b.instagram:'';
    let wb=document.getElementById('whatsappBtn');const url=waUrl(b.whatsapp||b.telefono);if(url){if(!wb){wb=document.createElement('a');wb.id='whatsappBtn';wb.className='whatsappBtn';wb.target='_blank';wb.rel='noopener';document.querySelector('.footerInner').appendChild(wb);}wb.href=url;wb.textContent='💬 WhatsApp';}else if(wb)wb.remove();
    const img=document.getElementById('businessLogo');if(b.logo){if(img)img.src=b.logo;else{const f=document.getElementById('businessLogoFallback');if(f){f.outerHTML=`<img class="logo" id="businessLogo" src="${esc(b.logo)}" alt="${esc(b.nombre||'')}"/>`;}}}
  }
  async function sync(){try{const r=await fetch('data.php?t='+Date.now(),{cache:'no-store'});const j=await r.json();if(j.ok){render(j);renderBusiness(j.business);status.textContent='Catálogo actualizado · '+new Date().toLocaleTimeString('es-MX',{hour:'2-digit',minute:'2-digit'});}}catch(e){status.textContent='Catálogo disponible';}}
  setInterval(sync,60000);
})();
