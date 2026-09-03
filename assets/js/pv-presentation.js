(function () {
  'use strict';

  function parseNumber(value) {
    var raw = String(value || '').replace(/[^0-9,.-]/g, '').trim();
    if (!raw) return NaN;

    if (raw.indexOf(',') !== -1 && raw.indexOf('.') !== -1) {
      if (raw.lastIndexOf(',') > raw.lastIndexOf('.')) {
        raw = raw.replace(/\./g, '').replace(',', '.');
      } else {
        raw = raw.replace(/,/g, '');
      }
    } else if (raw.indexOf(',') !== -1) {
      raw = raw.replace(',', '.');
    }

    return Number(raw);
  }

  function formatPrice(value) {
    var number = parseNumber(value);
    if (!Number.isFinite(number)) return value;

    var abs = Math.abs(number);
    var decimals = abs >= 1000 ? 0 : (abs >= 1 ? 2 : 4);

    return new Intl.NumberFormat('tr-TR', {
      minimumFractionDigits: decimals,
      maximumFractionDigits: decimals
    }).format(number);
  }

  function formatRate(value) {
    var number = parseNumber(value);
    if (!Number.isFinite(number)) return value;

    return new Intl.NumberFormat('tr-TR', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    }).format(number);
  }

  function formatHeaderCoins() {
    var coinSymbols = ['BTC', 'ETH', 'XRP'];

    document.querySelectorAll('#pvHeaderTicker a[data-symbol]').forEach(function (item) {
      var symbol = String(item.getAttribute('data-symbol') || '').toUpperCase();
      if (coinSymbols.indexOf(symbol) === -1) return;

      var price = item.querySelector('b');
      var rate = item.querySelector('em');

      if (price) price.textContent = formatPrice(price.textContent);
      if (rate) {
        var arrow = rate.textContent.indexOf('▼') !== -1 ? '▼' : '▲';
        rate.textContent = arrow + ' %' + formatRate(rate.textContent);
      }
    });
  }

  function formatStickyCoins() {
    var names = ['Bitcoin', 'Ethereum', 'XRP'];

    document.querySelectorAll('.crypto-track > a').forEach(function (item) {
      var textNodes = Array.prototype.filter.call(item.childNodes, function (node) {
        return node.nodeType === Node.TEXT_NODE && node.textContent.trim();
      });

      if (!textNodes.length) return;

      var textNode = textNodes[0];
      var text = textNode.textContent.trim();
      var name = names.find(function (candidate) {
        return text.indexOf(candidate + ' ') === 0;
      });

      if (!name) return;

      var rawPrice = text.slice(name.length).trim();
      textNode.textContent = name + ' ' + formatPrice(rawPrice) + ' ';

      var rate = item.querySelector('span.up, span.down');
      if (rate) {
        var arrow = rate.textContent.indexOf('▼') !== -1 ? '▼' : '▲';
        rate.textContent = arrow + ' %' + formatRate(rate.textContent);
      }
    });
  }

  function run() {
    formatHeaderCoins();
    formatStickyCoins();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', run);
  } else {
    run();
  }
}());
