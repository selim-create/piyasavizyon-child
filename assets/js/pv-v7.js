(function(){
  'use strict';
  function ready(fn){ if(document.readyState !== 'loading') fn(); else document.addEventListener('DOMContentLoaded', fn); }
  ready(function(){
    var root = document.documentElement;
    var header = document.querySelector('.site-header');
    function syncSticky(){
      if(!header) return;
      var h = header.offsetHeight || 68;
      root.style.setProperty('--pv-header-height', h+'px');
      root.style.setProperty('--pv-sticky-offset', h+'px');
    }
    syncSticky(); window.addEventListener('resize', syncSticky);
    var mast = document.querySelector('.masthead');
    function applySticky(){
      if(!header) return;
      var threshold = mast ? mast.offsetHeight : 0;
      var fixed = window.pageYOffset > threshold;
      header.classList.toggle('pv-is-fixed', fixed);
    }
    applySticky(); window.addEventListener('scroll', applySticky, {passive:true}); window.addEventListener('resize', applySticky);


    // Mobile menu toggle
    document.addEventListener('click', function(e){
      var btn = e.target.closest('.pv-mobile-menu-toggle');
      if(!btn) return;
      e.preventDefault();
      var open = !document.body.classList.contains('pv-mobile-menu-open');
      document.body.classList.toggle('pv-mobile-menu-open', open);
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
      var icon = btn.querySelector('i');
      if(icon){ icon.className = open ? 'fa-solid fa-xmark' : 'fa-solid fa-bars'; }
    });
    document.addEventListener('click', function(e){
      if(document.body.classList.contains('pv-mobile-menu-open') && !e.target.closest('.site-header .menu') && !e.target.closest('.pv-mobile-menu-toggle')){
        document.body.classList.remove('pv-mobile-menu-open');
        var b = document.querySelector('.pv-mobile-menu-toggle');
        if(b){ b.setAttribute('aria-expanded','false'); var ic=b.querySelector('i'); if(ic) ic.className='fa-solid fa-bars'; }
      }
    });

    // Hide unfilled GAM/widget ad wrappers after GPT had time to collapse empty divs.
    function pvAdHasCreative(slot){
      if(!slot) return false;
      if(slot.classList.contains('pv-ad-cta')) return true;
      var creatives = slot.querySelectorAll('iframe,img,ins,object,embed');
      for(var i=0;i<creatives.length;i++){
        var el = creatives[i];
        var rect = el.getBoundingClientRect();
        if((rect.width > 1 && rect.height > 1) || (el.offsetWidth > 1 && el.offsetHeight > 1)) return true;
      }
      var gpt = slot.querySelector('[id^="div-gpt-"]');
      if(gpt){
        var r = gpt.getBoundingClientRect();
        if(r.width > 1 && r.height > 1 && gpt.children.length) return true;
      }
      // Plain image/html ad widgets without iframe may have measurable children.
      var kids = slot.children;
      for(var k=0;k<kids.length;k++){
        var kr = kids[k].getBoundingClientRect();
        if(kr.width > 1 && kr.height > 1 && kids[k].textContent.trim() !== '') return true;
      }
      return false;
    }
    function pvCollapseEmptyAds(){
      document.querySelectorAll('.pv-ad-slot:not(.pv-ad-cta), .pv-gam-ad').forEach(function(slot){
        // v2.27: Sidebar and page-skin ad widgets must not be auto-hidden;
        // GAM can render them late/lazy and hiding the wrapper caused missing ads on inner pages.
        if(slot.closest('aside.sidebar') || slot.closest('.pv-pageskin')){
          slot.classList.remove('pv-ad-is-empty');
          return;
        }
        if(pvAdHasCreative(slot)) slot.classList.remove('pv-ad-is-empty');
        else slot.classList.add('pv-ad-is-empty');
      });
    }
    window.addEventListener('load', function(){ setTimeout(pvCollapseEmptyAds, 1200); setTimeout(pvCollapseEmptyAds, 3200); setTimeout(pvCollapseEmptyAds, 6500); });
    document.addEventListener('slotRenderEnded', function(){ setTimeout(pvCollapseEmptyAds, 100); });

    // Profile dropdown click support
    document.addEventListener('click', function(e){
      var trigger = e.target.closest('.pv-profile-trigger');
      if(trigger){
        e.preventDefault();
        var menu = trigger.closest('.pv-profile-menu');
        var open = menu && !menu.classList.contains('is-open');
        document.querySelectorAll('.pv-profile-menu.is-open').forEach(function(m){ m.classList.remove('is-open'); });
        if(menu) { menu.classList.toggle('is-open', open); trigger.setAttribute('aria-expanded', open ? 'true':'false'); }
        return;
      }
      if(!e.target.closest('.pv-profile-menu')){
        document.querySelectorAll('.pv-profile-menu.is-open').forEach(function(m){ m.classList.remove('is-open'); var b=m.querySelector('.pv-profile-trigger'); if(b) b.setAttribute('aria-expanded','false'); });
      }
    });

    // Active menu: exact match first, then section prefix match.
    var currentPath = window.location.pathname.replace(/\/$/, '') || '/';
    document.querySelectorAll('.site-header .menu a').forEach(function(a){
      try{
        var url = new URL(a.href, window.location.origin);
        var path = url.pathname.replace(/\/$/, '') || '/';
        if(path === currentPath || (path !== '/' && currentPath.indexOf(path + '/') === 0)){
          a.classList.add('is-active-link');
          var li = a.closest('li'); if(li) li.classList.add('current-menu-item','current-menu-ancestor');
        }
      }catch(e){}
    });

    // Search panel
    document.addEventListener('click', function(e){
      var btn = e.target.closest('.pv-search-toggle');
      if(btn && header){
        e.preventDefault();
        var open = !header.classList.contains('search-open');
        header.classList.toggle('search-open', open);
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        var panel = document.querySelector('.pv-search-panel');
        if(panel) panel.setAttribute('aria-hidden', open ? 'false' : 'true');
        var input = document.querySelector('.pv-search-form input');
        if(open && input) setTimeout(function(){ input.focus(); }, 80);
      }
      if(header && header.classList.contains('search-open') && !e.target.closest('.site-header')){
        header.classList.remove('search-open');
        var t = document.querySelector('.pv-search-toggle'); if(t) t.setAttribute('aria-expanded','false');
      }
    });
    document.addEventListener('keydown', function(e){ if(e.key === 'Escape' && header){ header.classList.remove('search-open'); } });

    // Generic tab system
    document.addEventListener('click', function(e){
      var btn = e.target.closest('[data-tabs] button, .pv-cat-tabs button');
      if(!btn) return;
      e.preventDefault();
      var tabs = btn.closest('[data-tabs]');
      if(tabs){
        var scope = tabs.getAttribute('data-tabs');
        var container = tabs.closest('.panel') || tabs.closest('.converter') || tabs.parentElement;
        if(!container) return;
        container.querySelectorAll('[data-tabs="'+scope+'"] button').forEach(function(b){ b.classList.remove('active'); });
        btn.classList.add('active');
        var target = btn.getAttribute('data-tab');
        container.querySelectorAll('.tabpane').forEach(function(p){ p.classList.toggle('active', p.getAttribute('data-pane') === target); });
        setTimeout(function(){ document.querySelectorAll('.converter-box.tabpane.active').forEach(convert); }, 10);
        return;
      }
      if(btn.closest('.pv-cat-tabs')){
        var block = btn.closest('.pv-category-block'); if(!block) return;
        var cat = (btn.getAttribute('data-cat') || 'all').toLowerCase();
        block.querySelectorAll('.pv-cat-tabs button').forEach(function(b){ b.classList.remove('active'); });
        btn.classList.add('active');
        block.querySelectorAll('.pv-cat-grid .mini-card').forEach(function(card){
          var cats = (card.getAttribute('data-cats') || '').toLowerCase();
          var show = cat === 'all' || cats.indexOf(cat) !== -1;
          card.classList.toggle('is-hidden', !show);
        });
      }
    });

    // Slider
    var slides = Array.prototype.slice.call(document.querySelectorAll('.slide'));
    var dots = Array.prototype.slice.call(document.querySelectorAll('.dot'));
    var i = 0;
    if(slides.length > 1){
      setInterval(function(){
        slides[i].classList.remove('active'); if(dots[i]) dots[i].classList.remove('active');
        i = (i + 1) % slides.length;
        slides[i].classList.add('active'); if(dots[i]) dots[i].classList.add('active');
      }, 4000);
    }

    function parseRates(){
      var fallback = {TRY:1, USD:0, EUR:0, GBP:0, CHF:0, JPY:0, AUD:0, CAD:0, RUB:0, BTC:0, ETH:0, BNB:0, SOL:0, XRP:0, DOGE:0, AVAX:0, ADA:0, ALTIN_GRAM_ALTIN:0, ALTIN_CEYREK_ALTIN:0};
      var el = document.getElementById('pv-converter-rates');
      if(!el) return fallback;
      try{
        var data = JSON.parse(el.textContent || '{}');
        Object.keys(fallback).forEach(function(k){ if(!data[k]) data[k] = fallback[k]; });
        return data;
      }catch(err){ return fallback; }
    }
    var rates = parseRates();
    function fmt(n){ return new Intl.NumberFormat('tr-TR', {maximumFractionDigits:4}).format(n); }
    function convert(box){
      if(!box) return;
      var amountEl = box.querySelector('.pv-conv-amount');
      var fromEl = box.querySelector('.pv-conv-from');
      var toEl = box.querySelector('.pv-conv-to');
      var result = box.querySelector('.pv-conv-result');
      var amount = parseFloat(String(amountEl ? amountEl.value : '0').replace(',', '.'));
      var from = fromEl ? fromEl.value : 'USD';
      var to = toEl ? toEl.value : 'TRY';
      if(!isFinite(amount)) amount = 0;
      if(typeof rates[from] === 'undefined' || typeof rates[to] === 'undefined' || parseFloat(rates[to]) === 0){ if(result) result.textContent = 'Bu kur için veri bulunamadı'; return; }
      var value = amount * parseFloat(rates[from] || 0) / parseFloat(rates[to]);
      if(result) result.textContent = fmt(value) + ' ' + to;
    }
    window.pvV7Convert = convert;
    document.querySelectorAll('.converter-box').forEach(function(box){ convert(box); });
    document.addEventListener('input', function(e){ if(e.target.matches('.converter-box input, .converter-box select')) convert(e.target.closest('.converter-box')); });
    document.addEventListener('change', function(e){ if(e.target.matches('.converter-box input, .converter-box select')) convert(e.target.closest('.converter-box')); });
    document.addEventListener('click', function(e){
      var swap = e.target.closest('.swap-btn');
      if(swap){
        e.preventDefault();
        var box = swap.closest('.converter-box');
        var f = box && box.querySelector('.pv-conv-from'), t = box && box.querySelector('.pv-conv-to');
        if(f && t){ var old=f.value; f.value=t.value; t.value=old; convert(box); }
      }
      var calc = e.target.closest('.pv-conv-btn');
      if(calc){ e.preventDefault(); convert(calc.closest('.converter-box')); }
    });

    function forexCalc(box){
      var amt = parseFloat(String((box.querySelector('.pv-forex-amount')||{}).value || '0').replace(',', '.'));
      if(!isFinite(amt)) amt=0;
      var pair = (box.querySelector('.pv-forex-pair')||{}).value || 'EUR/USD';
      var parts = pair.split('/');
      var from = parts[0], to = parts[1];
      var result = box.querySelector('.pv-forex-result');
      if(!rates[from] || !rates[to]) { if(result) result.textContent='Bu parite için veri bulunamadı'; return; }
      var val = amt * parseFloat(rates[from]) / parseFloat(rates[to]);
      if(result) result.textContent = fmt(val) + ' ' + to;
    }
    document.querySelectorAll('.forex-widget').forEach(forexCalc);
    document.addEventListener('click', function(e){ var b=e.target.closest('.pv-forex-btn'); if(b){ e.preventDefault(); forexCalc(b.closest('.forex-widget')); } });
    document.addEventListener('input', function(e){ if(e.target.matches('.pv-forex-amount,.pv-forex-pair')) forexCalc(e.target.closest('.forex-widget')); });
    document.addEventListener('change', function(e){ if(e.target.matches('.pv-forex-amount,.pv-forex-pair')) forexCalc(e.target.closest('.forex-widget')); });

    // Live-feel flash without changing real values
    var tickEls = Array.prototype.slice.call(document.querySelectorAll('#liveTicker .tick, .crypto-sticky a'));
    if(tickEls.length){
      setInterval(function(){
        var t = tickEls[Math.floor(Math.random()*tickEls.length)];
        if(!t) return;
        var down = t.classList.contains('down') || Math.random() < 0.35;
        t.classList.remove('pv-flash-up','pv-flash-down');
        void t.offsetWidth;
        t.classList.add(down ? 'pv-flash-down' : 'pv-flash-up');
        setTimeout(function(){ t.classList.remove('pv-flash-up','pv-flash-down'); }, 850);
      }, 1600);
    }
  });
})();


// v2.14 safety: keep mobile drawer above the header and prevent body scroll while open.
document.addEventListener('click', function(e){
  var btn = e.target.closest('.pv-mobile-menu-toggle');
  if(!btn) return;
  setTimeout(function(){
    var open = document.body.classList.contains('pv-mobile-menu-open');
    document.documentElement.classList.toggle('pv-mobile-menu-open', open);
  }, 0);
});
document.addEventListener('click', function(e){
  if(document.body.classList.contains('pv-mobile-menu-open') && e.target.closest('.site-header .menu a')){
    document.body.classList.remove('pv-mobile-menu-open');
    document.documentElement.classList.remove('pv-mobile-menu-open');
  }
});

// v2.32 emergency cleanup: no fixed sidebar JS. CSS handles sticky; JS only measures header and normalizes masthead size classes.
(function(){
  'use strict';
  function ready(fn){ if(document.readyState !== 'loading') fn(); else document.addEventListener('DOMContentLoaded', fn); }
  function syncHeaderHeight(){
    var header = document.querySelector('.site-header');
    var h = header ? Math.ceil(header.getBoundingClientRect().height || header.offsetHeight || 64) : 64;
    document.documentElement.style.setProperty('--pv-header-height', h + 'px');
  }
  function numberAttr(el, name){
    if(!el) return 0;
    var v = el.getAttribute(name) || '';
    var n = parseInt(String(v).replace('px',''), 10);
    return isFinite(n) ? n : 0;
  }
  function normalizeMasthead(){
    document.querySelectorAll('.pv-header-masthead').forEach(function(wrap){
      wrap.classList.remove('pv-mh-970x90','pv-mh-970x250');
      wrap.style.height = '';
      wrap.style.minHeight = '';
      wrap.style.maxHeight = '';
      wrap.style.overflow = 'visible';
      var creative = wrap.querySelector('iframe,img,ins,object,embed');
      var h = creative ? (numberAttr(creative, 'height') || Math.round(creative.getBoundingClientRect().height || creative.offsetHeight || 0)) : 0;
      if(h >= 180) wrap.classList.add('pv-mh-970x250');
      else if(h > 0) wrap.classList.add('pv-mh-970x90');
      wrap.querySelectorAll('.adbox,[id^="div-gpt"],.widget,.textwidget,.custom-html-widget').forEach(function(el){
        el.style.height = '';
        el.style.maxHeight = '';
        el.style.overflow = 'visible';
      });
      wrap.querySelectorAll('iframe,img,ins').forEach(function(el){
        el.style.maxHeight = 'none';
        el.style.overflow = 'visible';
      });
    });
  }
  function normalizeTickerPulse(){
    document.querySelectorAll('.market-ribbon .tick small').forEach(function(el){
      var pulse = el.querySelector('.pulse');
      var t = (el.textContent || '').trim();
      if(/Amerikan\s*Dolar[ıi]|AMER[İI]KAN\s*DOLARI/i.test(t)) t = '$ Dolar';
      if(/^Euro$/i.test(t) || /^EURO$/i.test(t)) t = '€ Euro';
      if(pulse){ el.textContent = ''; el.appendChild(pulse); el.appendChild(document.createTextNode(t)); }
      else { el.textContent = t; }
    });
  }
  ready(function(){
    syncHeaderHeight();
    normalizeMasthead();
    normalizeTickerPulse();
    [400,1200,2500,4500].forEach(function(ms){ setTimeout(function(){ syncHeaderHeight(); normalizeMasthead(); normalizeTickerPulse(); }, ms); });
  });
  window.addEventListener('resize', function(){ syncHeaderHeight(); normalizeMasthead(); }, {passive:true});
  window.addEventListener('load', function(){ syncHeaderHeight(); normalizeMasthead(); normalizeTickerPulse(); });
  window.googletag = window.googletag || {cmd:[]};
  if(window.googletag && window.googletag.cmd){
    window.googletag.cmd.push(function(){
      if(googletag.pubads){
        googletag.pubads().addEventListener('slotRenderEnded', function(event){
          if(event && event.slot && event.slot.getSlotElementId && event.slot.getSlotElementId() === 'div-gpt-970x250-masthead'){
            setTimeout(normalizeMasthead, 80);
          }
        });
      }
    });
  }
})();



// v2.38: restore original BirFinans custom-template tabs under the child footer.
// The original parent footer carried this behavior; child footer replaces it, so native market pages need it here.
(function(){
  'use strict';
  function ready(fn){ if(document.readyState !== 'loading') fn(); else document.addEventListener('DOMContentLoaded', fn); }
  function showAt(items, tabs, index){
    items.forEach(function(el, i){ el.style.display = i === index ? '' : 'none'; });
    tabs.forEach(function(tab, i){ tab.classList.toggle('active', i === index); });
    setTimeout(function(){
      if(window.Highcharts && window.Highcharts.charts){
        window.Highcharts.charts.forEach(function(chart){ if(chart && chart.reflow) chart.reflow(); });
      }
    }, 80);
  }
  function initTabGroup(head, contents){
    if(!head || !contents || contents.length < 2) return;
    var tabs = Array.prototype.slice.call(head.querySelectorAll('ul > li'));
    if(!tabs.length) return;
    showAt(contents, tabs, 0);
    tabs.forEach(function(tab, idx){
      tab.addEventListener('click', function(e){
        e.preventDefault();
        showAt(contents, tabs, idx);
      });
    });
  }
  ready(function(){
    document.querySelectorAll('.pv-market-native .categoryTab').forEach(function(category){
      var mainHead = category.querySelector(':scope > .tabHead');
      var mainContents = Array.prototype.filter.call(category.children, function(el){ return el.classList && el.classList.contains('catTabContent'); });
      initTabGroup(mainHead, mainContents);
    });
    document.querySelectorAll('.pv-market-native .borsaTimerTabHead, .pv-market-native .borsaTimerTabHead1, .pv-market-native .borsaTimerTabHead2, .pv-market-native .borsaTimerTabHead3').forEach(function(head){
      var parent = head.parentElement;
      if(!parent) return;
      var contentClass = 'borsaTimerTabContent';
      Array.prototype.slice.call(head.classList).forEach(function(cls){
        var m = cls.match(/^borsaTimerTabHead(\d+)$/);
        if(m) contentClass = 'borsaTimerTabContent' + m[1];
      });
      var contents = Array.prototype.filter.call(parent.children, function(el){ return el.classList && el.classList.contains(contentClass); });
      initTabGroup(head, contents);
    });
  });
})();

// v2.39: hard layout/tab repair for native BirFinans market templates.
(function(){
  'use strict';
  function ready(fn){ if(document.readyState !== 'loading') fn(); else document.addEventListener('DOMContentLoaded', fn); }
  function setPane(el, active){
    if(!el) return;
    el.classList.toggle('pv-tab-active', !!active);
    try { el.style.setProperty('display', active ? 'block' : 'none', 'important'); } catch(e) { el.style.display = active ? 'block' : 'none'; }
  }
  function reflowCharts(){
    if(window.Highcharts && window.Highcharts.charts){
      window.Highcharts.charts.forEach(function(chart){
        if(chart && chart.reflow){
          try { chart.reflow(); } catch(e) {}
        }
      });
    }
  }
  function bindTabs(head, panes){
    if(!head || !panes || panes.length < 2) return;
    var tabs = Array.prototype.slice.call(head.querySelectorAll(':scope > ul > li'));
    if(!tabs.length) return;
    function show(index){
      panes.forEach(function(pane, i){ setPane(pane, i === index); });
      tabs.forEach(function(tab, i){ tab.classList.toggle('active', i === index); });
      [40, 180, 500].forEach(function(ms){ setTimeout(reflowCharts, ms); });
    }
    show(0);
    tabs.forEach(function(tab, index){
      if(tab.dataset.pvNativeTabBound === '1') return;
      tab.dataset.pvNativeTabBound = '1';
      tab.addEventListener('click', function(e){
        e.preventDefault();
        e.stopPropagation();
        show(index);
      }, true);
    });
  }
  function initNativeMarketTabs(){
    document.querySelectorAll('.pv-market-native .categoryTab').forEach(function(category){
      var head = category.querySelector(':scope > .tabHead');
      var panes = Array.prototype.filter.call(category.children, function(el){
        return el.classList && el.classList.contains('catTabContent');
      });
      bindTabs(head, panes);
    });

    document.querySelectorAll('.pv-market-native .borsaTimerTabHead, .pv-market-native .borsaTimerTabHead1, .pv-market-native .borsaTimerTabHead2, .pv-market-native .borsaTimerTabHead3').forEach(function(head){
      var parent = head.parentElement;
      if(!parent) return;
      var contentClass = 'borsaTimerTabContent';
      Array.prototype.slice.call(head.classList).forEach(function(cls){
        var m = cls.match(/^borsaTimerTabHead(\d+)$/);
        if(m) contentClass = 'borsaTimerTabContent' + m[1];
      });
      var panes = Array.prototype.filter.call(parent.children, function(el){
        return el.classList && el.classList.contains(contentClass);
      });
      bindTabs(head, panes);
    });
    [100, 350, 900, 1600, 3000].forEach(function(ms){ setTimeout(reflowCharts, ms); });
  }
  ready(initNativeMarketTabs);
  window.addEventListener('load', initNativeMarketTabs);
  window.addEventListener('resize', function(){ setTimeout(reflowCharts, 80); }, {passive:true});
})();

/* === v2.44: IPO tabs === */
(function(){
  function activate(root, btnSelector, panelSelector, attr){
    root.addEventListener('click', function(e){
      var btn = e.target.closest(btnSelector);
      if(!btn || !root.contains(btn)) return;
      e.preventDefault();
      var key = btn.getAttribute(attr);
      root.querySelectorAll(btnSelector).forEach(function(b){ b.classList.toggle('active', b === btn); });
      root.querySelectorAll(panelSelector).forEach(function(p){ p.classList.toggle('active', p.getAttribute(attr.replace('tab','panel')) === key); });
    });
  }
  document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.pv-ipo-calendar-card').forEach(function(card){ activate(card, '[data-pv-ipo-tab]', '[data-pv-ipo-panel]', 'data-pv-ipo-tab'); });
    document.querySelectorAll('[data-pv-ipo-single-tabs]').forEach(function(card){ activate(card, '[data-pv-single-tab]', '[data-pv-single-panel]', 'data-pv-single-tab'); });
  });
})();

/* === v2.46: Credit page tabs, inputs and estimator === */
(function(){
  function digits(value){ return String(value || '').replace(/[^0-9]/g, ''); }
  function formatTR(value){
    var n = digits(value);
    if (!n) return '';
    return n.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  }
  function parseTR(value){ return Number(digits(value) || 0); }
  function moneyTR(value){
    if (!isFinite(value)) value = 0;
    return value.toLocaleString('tr-TR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' TL';
  }

  document.querySelectorAll('[data-pv-credit-calculator]').forEach(function(calc){
    var tabs = calc.querySelectorAll('[data-pv-credit-tab]');
    var panels = calc.querySelectorAll('[data-pv-credit-panel]');
    tabs.forEach(function(tab){
      tab.addEventListener('click', function(){
        var key = tab.getAttribute('data-pv-credit-tab');
        tabs.forEach(function(t){ t.classList.toggle('active', t === tab); });
        panels.forEach(function(panel){ panel.classList.toggle('active', panel.getAttribute('data-pv-credit-panel') === key); });
      });
    });
  });

  document.querySelectorAll('.pv-credit-number').forEach(function(input){
    input.addEventListener('input', function(){
      var start = input.selectionStart || input.value.length;
      input.value = formatTR(input.value);
      try { input.setSelectionRange(input.value.length, input.value.length); } catch(e) {}
    });
    var form = input.closest('form');
    if (form) {
      form.addEventListener('submit', function(){ input.value = digits(input.value); });
    }
  });

  document.querySelectorAll('[data-pv-credit-estimator]').forEach(function(box){
    var amount = box.querySelector('.pv-est-amount');
    var term = box.querySelector('.pv-est-term');
    var rate = box.querySelector('.pv-est-rate');
    var result = box.querySelector('.pv-est-result');
    var btn = box.querySelector('.pv-est-button');
    function calc(){
      var a = parseTR(amount && amount.value);
      var n = Number(term && term.value || 0);
      var r = Number(String(rate && rate.value || '0').replace(',', '.')) / 100;
      var payment = 0;
      if (a > 0 && n > 0) {
        payment = r > 0 ? a * (r * Math.pow(1 + r, n)) / (Math.pow(1 + r, n) - 1) : a / n;
      }
      if (result) result.textContent = payment ? moneyTR(payment) : '-';
    }
    if (btn) btn.addEventListener('click', calc);
    [amount, term, rate].forEach(function(el){ if (el) el.addEventListener('change', calc); });
    calc();
  });
})();

/* === v2.47: Advanced credit calculator === */
(function(){
  function digits(value){ return String(value || '').replace(/[^0-9]/g, ''); }
  function parseTR(value){ return Number(digits(value) || 0); }
  function moneyTR(value){
    if (!isFinite(value)) value = 0;
    return value.toLocaleString('tr-TR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' TL';
  }
  function payment(amount, term, rate){
    amount = Number(amount || 0); term = Number(term || 0); rate = Number(rate || 0) / 100;
    if (amount <= 0 || term <= 0) return 0;
    if (rate <= 0) return amount / term;
    return amount * (rate * Math.pow(1 + rate, term)) / (Math.pow(1 + rate, term) - 1);
  }
  function initAdvanced(box){
    var form = box.querySelector('.pv-credit-advanced-form');
    var typeInput = box.querySelector('.pv-adv-type-input');
    var tabs = box.querySelectorAll('[data-pv-adv-type]');
    var amount = box.querySelector('.pv-adv-amount');
    var term = box.querySelector('.pv-adv-term');
    var rate = box.querySelector('.pv-adv-rate');
    var fee = box.querySelector('.pv-adv-fee');
    var tax = box.querySelector('.pv-adv-tax');
    var monthly = box.querySelector('.pv-adv-monthly');
    var total = box.querySelector('.pv-adv-total');
    var cost = box.querySelector('.pv-adv-cost');
    function calc(){
      var a = parseTR(amount && amount.value);
      var n = Number(term && term.value || 0);
      var r = Number(String(rate && rate.value || '0').replace(',', '.'));
      var f = parseTR(fee && fee.value);
      var t = Number(tax && tax.value || 0);
      var base = payment(a, n, r);
      var taxCost = base * t;
      var m = base + taxCost;
      var totalPay = (m * n) + f;
      var totalCost = totalPay - a;
      if (monthly) monthly.textContent = m ? moneyTR(m) : '-';
      if (total) total.textContent = totalPay ? moneyTR(totalPay) : '-';
      if (cost) cost.textContent = totalCost ? moneyTR(totalCost) : '-';
    }
    tabs.forEach(function(tab){
      tab.addEventListener('click', function(){
        var key = tab.getAttribute('data-pv-adv-type');
        tabs.forEach(function(t){ t.classList.toggle('active', t === tab); });
        if (typeInput) typeInput.value = key;
        if (form && tab.getAttribute('data-action')) form.setAttribute('action', tab.getAttribute('data-action'));
        if (key === 'konut') { if (amount) amount.value = '1.000.000'; if (term) term.value = '120'; if (rate) rate.value = '2.95'; }
        if (key === 'tasit') { if (amount) amount.value = '500.000'; if (term) term.value = '36'; if (rate) rate.value = '3.45'; }
        if (key === 'kobi') { if (amount) amount.value = '250.000'; if (term) term.value = '24'; if (rate) rate.value = '4.05'; }
        if (key === 'ihtiyac') { if (amount) amount.value = '100.000'; if (term) term.value = '12'; if (rate) rate.value = '3.25'; }
        calc();
      });
    });
    [amount, term, rate, fee, tax].forEach(function(el){ if (el) { el.addEventListener('input', calc); el.addEventListener('change', calc); } });
    if (form) form.addEventListener('submit', function(){ if (amount) amount.value = digits(amount.value); });
    calc();
  }
  document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('[data-pv-credit-advanced]').forEach(initAdvanced);
  });
})();

/* v2.50 footer disclaimer modal */
(function(){
  function ready(fn){
    if(document.readyState !== 'loading'){ fn(); }
    else { document.addEventListener('DOMContentLoaded', fn); }
  }
  ready(function(){
    var modal = document.getElementById('pvDisclaimerModal');
    var openBtn = document.getElementById('pvOpenDisclaimer');
    var closeBtn = document.getElementById('pvCloseDisclaimer');
    var understoodBtn = document.getElementById('pvUnderstoodDisclaimer');
    if(!modal || !openBtn){ return; }
    function openModal(){
      modal.classList.add('is-active');
      modal.setAttribute('aria-hidden', 'false');
      document.documentElement.classList.add('pv-footer-modal-open');
    }
    function closeModal(){
      modal.classList.remove('is-active');
      modal.setAttribute('aria-hidden', 'true');
      document.documentElement.classList.remove('pv-footer-modal-open');
    }
    openBtn.addEventListener('click', openModal);
    if(closeBtn){ closeBtn.addEventListener('click', closeModal); }
    if(understoodBtn){ understoodBtn.addEventListener('click', closeModal); }
    modal.addEventListener('click', function(e){
      if(e.target === modal){ closeModal(); }
    });
    document.addEventListener('keydown', function(e){
      if(e.key === 'Escape' && modal.classList.contains('is-active')){ closeModal(); }
    });
  });
})();

/* v2.56 centered header interactions */
(function(){
  function ready(fn){
    if(document.readyState !== 'loading'){ fn(); }
    else { document.addEventListener('DOMContentLoaded', fn); }
  }
  ready(function(){
    var toggle = document.querySelector('.pv-h-mobile-toggle');
    var panel = document.getElementById('pvHeaderMobilePanel');
    if(toggle && panel){
      toggle.addEventListener('click', function(){
        var open = !panel.classList.contains('is-open');
        panel.classList.toggle('is-open', open);
        panel.setAttribute('aria-hidden', open ? 'false' : 'true');
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      });
    }
    var profile = document.getElementById('pvHeaderProfile');
    if(profile){
      var btn = profile.querySelector('.pv-h-user-chip');
      if(btn){
        btn.addEventListener('click', function(e){
          e.preventDefault();
          var open = !profile.classList.contains('is-open');
          profile.classList.toggle('is-open', open);
          btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
      }
      document.addEventListener('click', function(e){
        if(!profile.contains(e.target)){
          profile.classList.remove('is-open');
          if(btn){ btn.setAttribute('aria-expanded', 'false'); }
        }
      });
    }
  });
})();

/* v2.57 header scroll state: only compact nav changes on scroll */
(function(){
  function ready(fn){ if(document.readyState !== 'loading') fn(); else document.addEventListener('DOMContentLoaded', fn); }
  ready(function(){
    var nav = document.querySelector('.pv-sticky-nav-v257');
    function sync(){
      var y = window.pageYOffset || document.documentElement.scrollTop || 0;
      document.body.classList.toggle('pv-header-scrolled', y > 96);
      if(nav){
        document.documentElement.style.setProperty('--pv-header-height', Math.ceil(nav.getBoundingClientRect().height || 64) + 'px');
      }
    }
    sync();
    window.addEventListener('scroll', sync, {passive:true});
    window.addEventListener('resize', sync);
  });
})();

/* v2.61 header: identity-only fixed sticky + scoped search panel */
(function(){
  function ready(fn){ if(document.readyState !== 'loading') fn(); else document.addEventListener('DOMContentLoaded', fn); }
  ready(function(){
    var header = document.getElementById('pvHeaderV260');
    if(!header) return;
    var identity = header.querySelector('.pv-h-identity');
    var rail = header.querySelector('.pv-h-rail');
    var searchToggle = header.querySelector('.pv-h-rail-search');
    var panel = header.querySelector('.pv-h-search-panel');
    var input = header.querySelector('.pv-h-search-form input');
    var initialTop = 0;
    var ticking = false;

    function adminOffset(){
      var bar = document.getElementById('wpadminbar');
      return bar ? Math.ceil(bar.getBoundingClientRect().height || 0) : 0;
    }
    function measure(){
      var admin = adminOffset();
      document.documentElement.style.setProperty('--pv-h-admin', admin + 'px');
      if(identity){
        var fixed = document.body.classList.contains('pv-header-identity-fixed');
        if(fixed){ document.body.classList.remove('pv-header-identity-fixed'); }
        var rect = identity.getBoundingClientRect();
        var h = Math.ceil(rect.height || 0);
        initialTop = Math.max(0, Math.round(rect.top + (window.pageYOffset || document.documentElement.scrollTop || 0) - admin));
        document.documentElement.style.setProperty('--pv-h-identity-h', h + 'px');
        document.documentElement.style.setProperty('--pv-header-height', h + 'px');
        if(fixed){ document.body.classList.add('pv-header-identity-fixed'); }
      }
    }
    function sync(){
      var y = window.pageYOffset || document.documentElement.scrollTop || 0;
      var shouldFix = identity && y > initialTop;
      document.body.classList.toggle('pv-header-scrolled', y > 92);
      document.body.classList.toggle('pv-header-identity-fixed', !!shouldFix);
      ticking = false;
    }
    function requestSync(){
      if(!ticking){
        window.requestAnimationFrame(sync);
        ticking = true;
      }
    }
    measure();
    sync();
    window.addEventListener('scroll', requestSync, {passive:true});
    window.addEventListener('resize', function(){ measure(); sync(); });
    window.addEventListener('load', function(){ measure(); sync(); });

    if(searchToggle && panel){
      searchToggle.addEventListener('click', function(e){
        e.preventDefault();
        var open = !header.classList.contains('search-open');
        header.classList.toggle('search-open', open);
        if(rail){ rail.classList.toggle('search-open', open); }
        panel.setAttribute('aria-hidden', open ? 'false' : 'true');
        searchToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        if(open && input){ setTimeout(function(){ input.focus(); }, 70); }
      });
      document.addEventListener('click', function(e){
        if(!header.classList.contains('search-open')) return;
        if(e.target.closest('.pv-h-search-panel') || e.target.closest('.pv-h-rail-search')) return;
        header.classList.remove('search-open');
        if(rail){ rail.classList.remove('search-open'); }
        panel.setAttribute('aria-hidden', 'true');
        searchToggle.setAttribute('aria-expanded', 'false');
      });
      document.addEventListener('keydown', function(e){
        if(e.key === 'Escape' && header.classList.contains('search-open')){
          header.classList.remove('search-open');
          if(rail){ rail.classList.remove('search-open'); }
          panel.setAttribute('aria-hidden', 'true');
          searchToggle.setAttribute('aria-expanded', 'false');
        }
      });
    }
  });
})();


/* === v2.69: force Highcharts to fit rebuilt currency detail cards === */
(function(){
  function resizeCurrencyCharts(){
    if(!document.body || !document.body.classList.contains('pv-page-currency-detail')){}
    var root=document.querySelector('.pv-market-currency-detail-native');
    if(!root || !window.Highcharts || !window.Highcharts.charts) return;
    window.Highcharts.charts.forEach(function(chart){
      if(!chart || !chart.renderTo || !root.contains(chart.renderTo)) return;
      var box=chart.renderTo;
      var w=Math.max(280, box.clientWidth || (box.parentElement ? box.parentElement.clientWidth : 0) || 0);
      var h=Math.max(300, box.clientHeight || 360);
      try{ chart.setSize(w, h, false); chart.reflow(); }catch(e){}
    });
  }
  function schedule(){ [60,180,450,900,1600,2600].forEach(function(ms){ setTimeout(resizeCurrencyCharts, ms); }); }
  if(document.readyState === 'loading') document.addEventListener('DOMContentLoaded', schedule); else schedule();
  window.addEventListener('load', schedule);
  window.addEventListener('resize', function(){ setTimeout(resizeCurrencyCharts, 120); }, {passive:true});
  document.addEventListener('click', function(e){
    if(e.target.closest('.pv-market-currency-detail-native .borsaTimerTabHead li')) schedule();
  }, true);
})();

/* === v2.70: align page-skin ads with content start and guard mobile header flow === */
(function(){
  function ready(fn){ if(document.readyState !== 'loading') fn(); else document.addEventListener('DOMContentLoaded', fn); }
  ready(function(){
    var root = document.documentElement;
    var header = document.getElementById('pvHeaderV260');

    function adminOffset(){
      var bar = document.getElementById('wpadminbar');
      return bar ? Math.ceil(bar.getBoundingClientRect().height || 0) : 0;
    }

    function syncPageSkinTop(){
      var rail = document.querySelector('.pv-pageskin');
      var content = document.querySelector('.pv-inner-layout') || document.querySelector('.wrap.main');
      if(!rail || !content) return;
      if(window.matchMedia && window.matchMedia('(max-width: 1319px)').matches){
        root.style.removeProperty('--pv-pageskin-top');
        return;
      }
      var identity = header ? header.querySelector('.pv-h-identity') : null;
      var safeTop = adminOffset() + Math.ceil(identity ? (identity.getBoundingClientRect().height || 70) : 70) + 18;
      var contentTop = Math.ceil(content.getBoundingClientRect().top || 0);
      var top = Math.max(safeTop, contentTop);
      root.style.setProperty('--pv-pageskin-top', top + 'px');
    }

    function guardMobileHeaderFlow(){
      if(!header) return;
      var first = document.querySelector('.pv-inner-masthead-wrap') || document.querySelector('.pv-hero-area');
      if(!first) return;
      if(!(window.matchMedia && window.matchMedia('(max-width: 980px)').matches)){
        if(first.dataset.pvMobileFlowGuard === '1'){
          first.style.marginTop = '';
          delete first.dataset.pvMobileFlowGuard;
        }
        return;
      }
      var hadGuard = first.dataset.pvMobileFlowGuard === '1';
      if(hadGuard){ first.style.marginTop = ''; }
      var headerBottom = Math.ceil(header.getBoundingClientRect().bottom || 0);
      var firstTop = Math.ceil(first.getBoundingClientRect().top || 0);
      var overlap = headerBottom - firstTop;
      if(overlap > 4){
        first.style.marginTop = (overlap + 14) + 'px';
        first.dataset.pvMobileFlowGuard = '1';
      } else if(hadGuard){
        delete first.dataset.pvMobileFlowGuard;
      }
    }

    function syncAll(){
      syncPageSkinTop();
      guardMobileHeaderFlow();
    }

    syncAll();
    window.addEventListener('load', syncAll);
    window.addEventListener('resize', syncAll, {passive:true});
    window.addEventListener('orientationchange', syncAll, {passive:true});
    window.addEventListener('scroll', syncPageSkinTop, {passive:true});
    setTimeout(syncAll, 250);
    setTimeout(syncAll, 900);
    setTimeout(syncAll, 1800);
  });
})();

/* === v2.71: slider hitbox and mobile masthead measured height guard === */
(function(){
  function ready(fn){ if(document.readyState !== 'loading') fn(); else document.addEventListener('DOMContentLoaded', fn); }
  ready(function(){
    function syncSliderHitboxes(){
      document.querySelectorAll('.slider').forEach(function(slider){
        slider.querySelectorAll('.slide').forEach(function(slide){
          var active = slide.classList.contains('active');
          slide.style.pointerEvents = active ? 'auto' : 'none';
          slide.style.visibility = active ? 'visible' : 'hidden';
          slide.style.zIndex = active ? '2' : '0';
        });
      });
    }

    syncSliderHitboxes();
    var obs = new MutationObserver(syncSliderHitboxes);
    document.querySelectorAll('.slider .slide').forEach(function(slide){
      obs.observe(slide, {attributes:true, attributeFilter:['class']});
    });

    function syncMobileMastheads(){
      if(!(window.matchMedia && window.matchMedia('(max-width: 980px)').matches)) return;
      document.querySelectorAll('.ad-mobile-masthead.pv-ad-slot').forEach(function(slot){
        if(slot.classList.contains('pv-ad-is-empty') || slot.classList.contains('pv-ad-empty')) return;
        var maxH = 0;
        slot.querySelectorAll('iframe,img,ins,[id^="google_ads_iframe"],[id^="div-gpt"] > div').forEach(function(el){
          var rect = el.getBoundingClientRect ? el.getBoundingClientRect() : null;
          var attrH = parseInt(el.getAttribute && el.getAttribute('height') || '', 10);
          var h = Math.ceil((rect && rect.height) || attrH || el.offsetHeight || 0);
          if(h > maxH) maxH = h;
        });
        if(maxH >= 220){ slot.style.minHeight = '250px'; }
        else if(maxH >= 130){ slot.style.minHeight = '150px'; }
        else if(maxH > 0){ slot.style.minHeight = Math.max(100, maxH) + 'px'; }
        else { slot.style.minHeight = '100px'; }
      });
    }
    function scheduleMastheads(){ [60,240,700,1500,3000].forEach(function(ms){ setTimeout(syncMobileMastheads, ms); }); }
    scheduleMastheads();
    window.addEventListener('load', scheduleMastheads);
    window.addEventListener('resize', scheduleMastheads, {passive:true});
    window.addEventListener('orientationchange', scheduleMastheads, {passive:true});
  });
})();
