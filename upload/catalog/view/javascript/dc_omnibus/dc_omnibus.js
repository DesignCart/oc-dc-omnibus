(function ($) {
  'use strict';

  if (!$ || typeof $.fn === 'undefined') {
    return;
  }

  function isVisible($el) {
    return $el.length && $el.is(':visible') && $.trim($el.text()) !== '';
  }

  function buildPanel(cfg, formattedPrice) {
    var $panel = $('<div class="dc-omnibus-panel" role="note" aria-live="polite"></div>');
    $panel.css('background', cfg.panelBg || '#f4f7f9');

    var $label = $('<span class="dc-omnibus-panel__label"></span>').text(cfg.label || '');
    $label.css({
      color: cfg.labelColor || '#64748b',
      fontSize: (cfg.labelSize || 14) + 'px',
      fontWeight: cfg.labelBold ? '700' : '400',
      textTransform: cfg.labelUppercase ? 'uppercase' : 'none'
    });

    var $price = $('<span class="dc-omnibus-panel__price"></span>').text(formattedPrice);
    $price.css({
      color: cfg.priceColor || '#0f172a',
      fontSize: (cfg.priceSize || 15) + 'px',
      fontWeight: cfg.priceBold ? '700' : '400',
      textTransform: cfg.priceUppercase ? 'uppercase' : 'none'
    });

    $panel.append($label).append($price);
    return $panel;
  }

  function shouldDisplay(cfg) {
    if (cfg.displayMode === 'always') {
      return true;
    }

    var $special = $(cfg.specialSelector || '.product-price-special');
    return isVisible($special) || cfg.hasSpecial;
  }

  function insertPanel($panel, cfg) {
    var selector = cfg.insertSelector || '.product-rating';
    var $target = $(selector).first();

    if (!$target.length) {
      $target = $('#product-price, .product-price, #product, .product-info').first();
    }

    if (!$target.length) {
      return;
    }

    if (cfg.insertPosition === 'before') {
      $target.before($panel);
    } else {
      $target.after($panel);
    }
  }

  function render(cfg) {
    if (!cfg.lowestFormatted) {
      return;
    }

    if (!shouldDisplay(cfg)) {
      return;
    }

    $('.dc-omnibus-panel').remove();
    insertPanel(buildPanel(cfg, cfg.lowestFormatted), cfg);
  }

  function init() {
    var cfg = window.dcOmnibusConfig;

    if (!cfg || !cfg.productId) {
      return;
    }

    render(cfg);
  }

  $(document).ready(init);
})(window.jQuery || window.$);
