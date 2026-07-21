document.addEventListener('DOMContentLoaded',function(){
  const btn=document.querySelector('.pv-menu-toggle');
  const nav=document.querySelector('.pv-primary-nav');
  if(btn&&nav){btn.addEventListener('click',()=>nav.classList.toggle('is-open'));}
});
