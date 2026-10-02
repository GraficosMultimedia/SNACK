(function(){
  'use strict';
  const money=n=>'$'+Number(n||0).toFixed(2);
  const esc=s=>String(s??'').replace(/[&<>'"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[c]));
  const data=window.MENU_DATA||{categories:[],toppings:[],business:{}};
  const grid=document.getElementById('menuGrid');
  const categories=document.getElementById('categories');
  const search=document.getElementById('menuSearch');
  const status=document.getElementById('syncStatus');
  const modal=document.getElementById('cravingModal');
  const backdrop=document.getElementById('cravingBackdrop');
  const cravingGrid=document.getElementById('cravingGrid');
  const floating=document.getElementById('floatingCraving');
  const openTop=document.getElementById('openCravingTop');
  const closeBtn=document.getElementById('closeCraving');
  const viewAll=document.getElementById('viewAllCravings');
  const noResults=document.getElementById('noResults');
  let popupShown=false;
  let lastRandomIds=[];

  function waUrl(phone){const d=String(phone||'').replace(/\D/g,'');return d?'https://wa.me/'+d:'';}
  function productWa(phone,name,price){const u=waUrl(phone);return u?u+'?text='+encodeURIComponent('Hola, quiero pedir '+name+' ('+money(price)+')'):'';}
  function iconFor(name){
    const s=String(name||'').toLowerCase();
    if(s.includes('fresa')) return '🍓';
    if(s.includes('chicharr')) return '🍿';
    if(s.includes('tost')) return '🥣';
    if(s.includes('hotcake')||s.includes('waffle')) return '🥞';
    if(s.includes('beb')||s.includes('frapp')||s.includes('jugo')||s.includes('smooth')) return '🥤';
    if(s.includes('nach')) return '🧀';
    if(s.includes('gom')) return '🍬';
    return '✦';
  }

  function renderCategories(cats){
    categories.innerHTML='<a class="cat active" href="#menu" data-cat="all">▦ <span>Todos</span></a>'+
      cats.map(c=>`<a class="cat" href="#cat-${Number(c.id)}" data-cat="${Number(c.id)}"><span class="catIcon">${iconFor(c.nombre)}</span><span>${esc(c.nombre)}</span></a>`).join('');
    categories.querySelectorAll('.cat').forEach(a=>a.addEventListener('click',e=>{
      const id=a.dataset.cat;
      categories.querySelectorAll('.cat').forEach(x=>x.classList.remove('active'));
      a.classList.add('active');
      if(id==='all'){e.preventDefault();window.scrollTo({top:document.getElementById('menu').offsetTop-105,behavior:'smooth'});}
    }));
  }

  function render(data){
    const cats=data.categories||[];
    grid.innerHTML=cats.map(c=>`<section class="category" id="cat-${Number(c.id)}" data-category-id="${Number(c.id)}" data-category-name="${esc(c.nombre)}">
      <div class="categoryTitle"><h2>${esc(c.nombre)}</h2><span>${(c.productos||[]).length} opciones</span></div>
      <div class="items">${(c.productos||[]).map(p=>{
        const hasImg=!!p.imagen;
        const wa=productWa(data.business?.whatsapp||data.business?.telefono,p.nombre,p.precio);
        return `<article class="item" data-product-id="${Number(p.id)}" data-product-name="${esc(p.nombre)}" data-category-id="${Number(c.id)}" data-category-name="${esc(c.nombre)}" data-search="${esc(String(p.nombre)+' '+String(p.descripcion||''))}">
          ${hasImg?`<div class="imageWrap"><img class="itemImage" src="${esc(p.imagen)}" alt="${esc(p.nombre)}" loading="lazy"></div>`:''}
          <div class="itemInfo"><h3>${esc(p.nombre)}</h3>${p.descripcion?`<p>${esc(p.descripcion)}</p>`:''}${Number(p.toppings_gratis||0)>0?`<p class="includedToppings">Incluye ${Number(p.toppings_gratis)} topping${Number(p.toppings_gratis)==1?'':'s'} gratis</p>`:''}</div>
          <div class="itemActions"><span class="price">${money(p.precio)}</span>${wa?`<a class="productWa" href="${esc(wa)}" target="_blank" rel="noopener" aria-label="Pedir ${esc(p.nombre)} por WhatsApp">◉<span>WhatsApp</span></a>`:''}</div>
        </article>`;
      }).join('')}</div></section>`).join('')+
      `<section class="toppingsSection"><div class="categoryTitle"><h2>Complementos / toppings</h2><span>Hazlo a tu manera</span></div><div class="toppingList">${(data.toppings||[]).map(t=>`<div><span>${esc(t.nombre)}</span><b>+${money(t.precio)}</b></div>`).join('')}</div></section>`;
    renderCategories(cats);
    observeCategories();
    applySearch();
  }

  function renderBusiness(b){
    if(!b)return;
    document.title=(b.nombre||'Snackliciosos')+' · Menú';
    const set=(id,text)=>{const e=document.getElementById(id);if(e)e.textContent=text||'';};
    set('businessName',b.nombre);set('footerName',b.nombre);set('businessAddress','📍 '+(b.direccion||''));set('thanks',b.mensaje);
    const phone=document.getElementById('businessPhone');if(phone)phone.textContent=b.telefono?'☎ '+b.telefono:'';
    const fa=document.getElementById('footerAddress');if(fa)fa.textContent=b.direccion||'';
    const fp=document.getElementById('footerPhone');if(fp){const d=String(b.telefono||'').replace(/\D/g,'');fp.textContent=b.telefono?'☎ '+b.telefono:'';fp.href=d?'tel:'+d:'#';}
    const fi=document.getElementById('footerInstagram');if(fi)fi.textContent=b.instagram?'📷 '+b.instagram:'';
    const url=waUrl(b.whatsapp||b.telefono);let wb=document.getElementById('whatsappBtn');
    if(url){if(!wb){wb=document.createElement('a');wb.id='whatsappBtn';wb.className='whatsappBtn';wb.target='_blank';wb.rel='noopener';document.querySelector('.footerInner').appendChild(wb);}wb.href=url;wb.textContent='💬 WhatsApp';}else if(wb)wb.remove();
    const img=document.getElementById('businessLogo');if(b.logo&&img)img.src=b.logo;
  }

  function allProducts(){return (data.categories||[]).flatMap(c=>(c.productos||[]).map(p=>({...p,categoryId:c.id,categoryName:c.nombre})));}
  function randomProducts(count=4){
    const all=allProducts();if(!all.length)return [];
    const pool=all.filter(p=>!lastRandomIds.includes(Number(p.id)));
    const source=pool.length>=count?pool:all;
    const out=[];const copy=source.slice();
    while(out.length<Math.min(count,copy.length)){const i=Math.floor(Math.random()*copy.length);out.push(copy.splice(i,1)[0]);}
    lastRandomIds=out.map(p=>Number(p.id));return out;
  }

  function openCraving(){
    const picks=randomProducts(4);
    cravingGrid.innerHTML=picks.map(p=>`<button class="cravingCard" type="button" data-category-id="${Number(p.categoryId)}" data-product-id="${Number(p.id)}">
      ${p.imagen?`<img src="${esc(p.imagen)}" alt="${esc(p.nombre)}" loading="eager">`:`<div class="cravingNoImage">${iconFor(p.nombre)}</div>`}
      <span><b>${esc(p.nombre)}</b><small>${esc(p.categoryName)}</small><strong>${money(p.precio)} →</strong></span>
    </button>`).join('');
    cravingGrid.querySelectorAll('.cravingCard').forEach(card=>card.addEventListener('click',()=>{
      closeCraving();
      const sec=document.getElementById('cat-'+card.dataset.categoryId);
      if(sec){sec.classList.add('categoryFocus');sec.scrollIntoView({behavior:'smooth',block:'start'});setTimeout(()=>sec.classList.remove('categoryFocus'),2200);}
    }));
    modal.hidden=false;backdrop.hidden=false;document.body.classList.add('modalOpen');
    requestAnimationFrame(()=>{modal.classList.add('isOpen');backdrop.classList.add('isOpen');});
  }
  function closeCraving(){modal.classList.remove('isOpen');backdrop.classList.remove('isOpen');document.body.classList.remove('modalOpen');setTimeout(()=>{modal.hidden=true;backdrop.hidden=true;},240);}

  function applySearch(){
    const q=String(search?.value||'').trim().toLowerCase();let visible=0;
    document.querySelectorAll('.category').forEach(sec=>{
      if(sec.classList.contains('toppingsSection'))return;
      let sectionVisible=0;
      sec.querySelectorAll('.item').forEach(item=>{const hay=item.dataset.search||'';const show=!q||hay.includes(q);item.hidden=!show;if(show)sectionVisible++;});
      sec.hidden=!!q&&!sectionVisible;
      visible+=sectionVisible;
    });
    if(noResults)noResults.hidden=visible!==0||!q;
  }

  function observeCategories(){
    const secs=[...document.querySelectorAll('.category[data-category-id]')];
    if(!('IntersectionObserver' in window))return;
    const obs=new IntersectionObserver(entries=>{
      const visible=entries.filter(e=>e.isIntersecting).sort((a,b)=>b.intersectionRatio-a.intersectionRatio)[0];
      if(!visible)return;
      const id=visible.target.dataset.categoryId;
      categories.querySelectorAll('.cat').forEach(a=>a.classList.toggle('active',a.dataset.cat===id));
    },{rootMargin:'-28% 0px -55% 0px',threshold:[0,.15,.4]});
    secs.forEach(s=>obs.observe(s));
  }

  async function sync(){
    try{const r=await fetch('data.php?t='+Date.now(),{cache:'no-store'});const j=await r.json();if(j.ok){Object.assign(data,j);render(j);renderBusiness(j.business);if(status)status.textContent='Catálogo actualizado · '+new Date().toLocaleTimeString('es-MX',{hour:'2-digit',minute:'2-digit'});}}
    catch(e){if(status)status.textContent='Catálogo disponible';}
  }

  search?.addEventListener('input',applySearch);
  openTop?.addEventListener('click',openCraving);
  floating?.addEventListener('click',openCraving);
  closeBtn?.addEventListener('click',closeCraving);
  backdrop?.addEventListener('click',closeCraving);
  viewAll?.addEventListener('click',()=>{closeCraving();document.getElementById('menu')?.scrollIntoView({behavior:'smooth',block:'start'});});
  document.addEventListener('keydown',e=>{if(e.key==='Escape'&&!modal.hidden)closeCraving();});

  let scrollTriggered=false;
  window.addEventListener('scroll',()=>{
    floating.classList.toggle('show',window.scrollY>420);
    if(!scrollTriggered&&window.scrollY>680){scrollTriggered=true;if(!popupShown){popupShown=true;setTimeout(openCraving,450);}}
  },{passive:true});

  render(data);renderBusiness(data.business);setInterval(sync,60000);
})();
