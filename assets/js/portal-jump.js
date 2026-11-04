/* Bubba Hub portal navigation: accessible compact jump menu on small screens. */
(function () {
  'use strict';
  var selectors = [
    '.bh-leader-unified .bh-unified-nav',
    '.bh-leader-business .bh-business-sidebar',
    '.bh3-family--sidebar .bh3-family__side',
    '.bh3-family-profile-preview nav'
  ];
  function init() {
    document.querySelectorAll(selectors.join(',')).forEach(function (nav, index) {
      if (nav.dataset.bhJumpReady === '1') return;
      var links = Array.from(nav.querySelectorAll('a[href]')).filter(function (a) {
        return a.getAttribute('href') && a.textContent.trim();
      });
      if (!links.length) return;
      var wrapper = document.createElement('div');
      wrapper.className = 'bh-portal-jump';
      var label = document.createElement('label');
      var select = document.createElement('select');
      var id = 'bh-portal-jump-' + index;
      select.id = id;
      label.htmlFor = id;
      label.textContent = 'Jump to section';
      select.setAttribute('aria-label', 'Jump to section');
      links.forEach(function (a) {
        var option = document.createElement('option');
        option.value = a.href;
        option.textContent = a.textContent.trim().replace(/\s+/g, ' ');
        option.selected = a.getAttribute('aria-current') === 'page' ||
          (a.pathname === window.location.pathname && a.hash === window.location.hash);
        select.appendChild(option);
      });
      select.addEventListener('change', function () {
        if (select.value) window.location.assign(select.value);
      });
      wrapper.appendChild(label);
      wrapper.appendChild(select);
      nav.parentNode.insertBefore(wrapper, nav);
      nav.classList.add('bh-portal-jump-source');
      nav.dataset.bhJumpReady = '1';
    });
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
