(function () {
  'use strict';

  function updatePreview() {
    var preview = document.getElementById('dcOmnibusPreview');
    if (!preview) return;

    var panelBg = document.querySelector('[name="module_dc_omnibus_panel_bg"]');
    var label = preview.querySelector('.dc-omnibus-preview__label');
    var price = preview.querySelector('.dc-omnibus-preview__price');

    if (panelBg && panelBg.value) {
      preview.style.background = panelBg.value;
    }

    if (label) {
      label.style.color = getVal('module_dc_omnibus_label_color', '#64748b');
      label.style.fontSize = getVal('module_dc_omnibus_label_size', 14) + 'px';
      label.style.fontWeight = isChecked('module_dc_omnibus_label_bold') ? '700' : '400';
      label.style.textTransform = isChecked('module_dc_omnibus_label_uppercase') ? 'uppercase' : 'none';
    }

    if (price) {
      price.style.color = getVal('module_dc_omnibus_price_color', '#0f172a');
      price.style.fontSize = getVal('module_dc_omnibus_price_size', 15) + 'px';
      price.style.fontWeight = isChecked('module_dc_omnibus_price_bold') ? '700' : '400';
      price.style.textTransform = isChecked('module_dc_omnibus_price_uppercase') ? 'uppercase' : 'none';
    }
  }

  function getVal(name, fallback) {
    var el = document.querySelector('[name="' + name + '"]');
    return el && el.value ? el.value : fallback;
  }

  function isChecked(name) {
    var el = document.querySelector('[name="' + name + '"]');
    return el && el.checked;
  }

  function bindPreview() {
    document.querySelectorAll('.dc-omnibus-typo, [name="module_dc_omnibus_panel_bg"]').forEach(function (el) {
      el.addEventListener('input', updatePreview);
      el.addEventListener('change', updatePreview);
    });

    document.addEventListener('dc:colorchange', updatePreview);
    updatePreview();
  }

  function openTabFromHash() {
    var hash = window.location.hash;

    if (!hash || hash.indexOf('#tab-') !== 0) {
      return;
    }

    var tabId = hash.substring(1);
    var tabBtn = document.querySelector('[data-dc-tab="' + tabId + '"]');

    if (tabBtn) {
      tabBtn.click();
    }
  }

  function bindSyncButton() {
    var btn = document.getElementById('dcOmnibusSyncBtn');

    if (!btn) {
      return;
    }

    btn.addEventListener('click', function (e) {
      var message = btn.getAttribute('data-confirm') || 'Continue?';

      if (!window.confirm(message)) {
        e.preventDefault();
      }
    });
  }

  function openHistoryFromUrl() {
    openTabFromHash();
  }

  document.addEventListener('DOMContentLoaded', function () {
    bindPreview();
    bindSyncButton();
    openHistoryFromUrl();
  });
})();
