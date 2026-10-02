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
  const photoZoomModal=document.getElementById('photoZoomModal');
  const photoZoomBackdrop=document.getElementById('photoZoomBackdrop');
  const photoZoomImage=document.getElementById('photoZoomImage');
  const photoZoomCaption=document.getElementById('photoZoomCaption');
  const closePhotoZoomBtn=document.getElementById('closePhotoZoom');
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

        return `<article class="item" data-product-id="${Number(p.id)}" data-product-name="${esc(p.nombre)}" data-category-id="${Number(c.id)}" data-category-name="${esc(c.nombre)}" data-search="${esc(String(p.nombre)+' '+String(p.descripcion||''))}">
          ${hasImg?`<div class="imageWrap"><img class="itemImage" src="${esc(p.imagen)}" alt="${esc(p.nombre)}" data-gallery-count="${Array.isArray(p.imagenes)?p.imagenes.length:1}" loading="lazy"></div>`:''}
          <div class="itemInfo"><h3>${esc(p.nombre)}</h3>${p.descripcion?`<p>${esc(p.descripcion)}</p>`:''}${Number(p.toppings_gratis||0)>0?`<p class="includedToppings">Incluye ${Number(p.toppings_gratis)} topping${Number(p.toppings_gratis)==1?'':'s'} gratis</p>`:''}</div>
          <div class="itemActions"><span class="price">${money(p.precio)}</span><button class="addMenuProduct" type="button" data-product-id="${Number(p.id)}" aria-label="Agregar ${esc(p.nombre)} al pedido">＋</button></div>
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

  let photoGallery = [];
  let photoGalleryIndex = 0;

  function galleryImagesFor(img){
    const item=img?.closest('.item');
    const pid=Number(item?.dataset.productId||0);
    const product=allProducts().find(p=>Number(p.id)===pid);
    const imgs=Array.isArray(product?.imagenes)?product.imagenes.filter(Boolean):[];
    if(imgs.length)return imgs;
    return img?.currentSrc||img?.src ? [img.currentSrc||img.src] : [];
  }

  function renderPhotoGallery(){
    const thumbs=document.getElementById('photoZoomThumbs');
    const prev=document.getElementById('photoZoomPrev');
    const next=document.getElementById('photoZoomNext');
    if(!photoGallery.length)return;
    const src=photoGallery[photoGalleryIndex];
    photoZoomImage.src=src;
    photoZoomImage.alt=photoZoomImage.dataset.productName||photoZoomImage.alt||'Foto del producto';
    if(photoZoomCaption) photoZoomCaption.textContent=(photoZoomImage.dataset.productName||'') + (photoGallery.length>1?` · ${photoGalleryIndex+1}/${photoGallery.length}`:'');
    if(prev) prev.hidden=photoGallery.length<2;
    if(next) next.hidden=photoGallery.length<2;
    if(thumbs){
      thumbs.hidden=photoGallery.length<2;
      thumbs.innerHTML=photoGallery.map((url,i)=>`<button type="button" class="photoZoomThumb ${i===photoGalleryIndex?'active':''}" data-photo-index="${i}" aria-label="Ver foto ${i+1}"><img src="${esc(url)}" alt=""></button>`).join('');
    }
  }

  function openPhotoZoom(img){
    if(!img||!photoZoomModal)return;
    photoGallery=galleryImagesFor(img);
    photoGalleryIndex=Math.max(0,photoGallery.findIndex(x=>x===img.currentSrc||x===img.src));
    if(!photoGallery.length)return;
    photoZoomImage.dataset.productName=img.alt||'';
    photoZoomImage.alt=img.alt||'';
    renderPhotoGallery();
    photoZoomModal.hidden=false;
    photoZoomBackdrop.hidden=false;
    document.body.classList.add('photoZoomOpen');
    requestAnimationFrame(()=>{
      photoZoomModal.classList.add('isOpen');
      photoZoomBackdrop.classList.add('isOpen');
    });
  }

  function closePhotoZoom(){
    if(!photoZoomModal||photoZoomModal.hidden)return;
    photoZoomModal.classList.remove('isOpen');
    photoZoomBackdrop.classList.remove('isOpen');
    document.body.classList.remove('photoZoomOpen');
    setTimeout(()=>{
      photoZoomModal.hidden=true;
      photoZoomBackdrop.hidden=true;
      if(photoZoomImage){photoZoomImage.src='';photoZoomImage.dataset.productName='';}
      photoGallery=[];photoGalleryIndex=0;
    },220);
  }

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

  // Delegación en document: el grid se repinta dinámicamente y así el clic
  // sobre cualquier foto sigue funcionando aunque cambie su contenido.
  document.addEventListener('click',e=>{
    const img=e.target.closest?.('.itemImage');
    if(img && document.contains(img)){
      e.preventDefault();
      e.stopPropagation();
      openPhotoZoom(img);
      return;
    }
    const thumb=e.target.closest?.('.photoZoomThumb');
    if(thumb && photoGallery.length){
      photoGalleryIndex=Number(thumb.dataset.photoIndex||0);
      renderPhotoGallery();
    }
  });
  document.getElementById('photoZoomPrev')?.addEventListener('click',()=>{
    if(photoGallery.length<2)return;
    photoGalleryIndex=(photoGalleryIndex-1+photoGallery.length)%photoGallery.length;
    renderPhotoGallery();
  });
  document.getElementById('photoZoomNext')?.addEventListener('click',()=>{
    if(photoGallery.length<2)return;
    photoGalleryIndex=(photoGalleryIndex+1)%photoGallery.length;
    renderPhotoGallery();
  });
  closePhotoZoomBtn?.addEventListener('click',closePhotoZoom);
  photoZoomBackdrop?.addEventListener('click',closePhotoZoom);
  photoZoomModal?.addEventListener('click',e=>{
    if(e.target===photoZoomModal)closePhotoZoom();
  });

  search?.addEventListener('input',applySearch);
  openTop?.addEventListener('click',openCraving);
  floating?.addEventListener('click',openCraving);
  closeBtn?.addEventListener('click',closeCraving);
  backdrop?.addEventListener('click',closeCraving);
  viewAll?.addEventListener('click',()=>{closeCraving();document.getElementById('menu')?.scrollIntoView({behavior:'smooth',block:'start'});});
  document.addEventListener('keydown',e=>{
    if(e.key!=='Escape')return;
    if(photoZoomModal&&!photoZoomModal.hidden){closePhotoZoom();return;}
    if(!modal.hidden)closeCraving();
  });

  let scrollTriggered=false;
  window.addEventListener('scroll',()=>{
    floating.classList.toggle('show',window.scrollY>420);
    if(!scrollTriggered&&window.scrollY>680){scrollTriggered=true;if(!popupShown){popupShown=true;setTimeout(openCraving,450);}}
  },{passive:true});

  render(data);renderBusiness(data.business);setInterval(sync,60000);
})();
