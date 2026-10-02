(function(){
  const search=document.getElementById('productSearch');
  const grid=document.getElementById('productGrid');
  const empty=document.getElementById('productEmpty');
  const filters=[...document.querySelectorAll('.filter-chip')];
  if(!grid) return;
  let category='all';
  function render(){
    const q=(search?.value||'').trim().toLowerCase();
    let visible=0;
    grid.querySelectorAll('.product-card-admin').forEach(card=>{
      const okCat=category==='all'||card.dataset.category===category;
      const okSearch=!q||card.dataset.search.includes(q);
      const show=okCat&&okSearch;
      card.classList.toggle('is-hidden',!show);
      if(show) visible++;
    });
    if(empty) empty.hidden=visible!==0;
  }
  search?.addEventListener('input',render);
  filters.forEach(btn=>btn.addEventListener('click',()=>{
    filters.forEach(x=>x.classList.remove('active')); btn.classList.add('active');
    category=btn.dataset.category||'all'; render();
  }));
  render();
  if(location.hash){setTimeout(()=>document.querySelector(location.hash)?.scrollIntoView({behavior:'smooth',block:'start'}),120);}
})();
