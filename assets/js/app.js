(function () {
  'use strict';

  const html = document.documentElement;
  const body = document.body;

  function getTheme() {
    return html.getAttribute('data-theme') || 'light';
  }

  function setTheme(theme) {
    html.setAttribute('data-theme', theme);
    body.classList.remove('theme-light', 'theme-dark');
    body.classList.add('theme-' + theme);
    document.querySelectorAll('.theme-toggle i').forEach(function (icon) {
      icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    });
    const url = new URL(window.location.href);
    url.searchParams.set('theme', theme);
    window.history.replaceState({}, '', url.pathname + url.search);
  }

  document.querySelectorAll('.theme-toggle').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const next = getTheme() === 'dark' ? 'light' : 'dark';
      setTheme(next);
      fetch(window.location.pathname + '?theme=' + next, { credentials: 'same-origin' }).catch(function () {});
    });
  });

  // Cart AJAX add
  document.querySelectorAll('[data-add-cart]').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      const id = btn.getAttribute('data-add-cart');
      const form = new FormData();
      form.append('action', 'add');
      form.append('product_id', id);
      fetch((window.FURUTH_BASE || '') + '/api/cart.php', {
        method: 'POST',
        body: form,
        credentials: 'same-origin'
      })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (data.success) {
            document.querySelectorAll('.cart-badge, .bottom-badge').forEach(function (el) {
              el.textContent = data.count;
              el.style.display = data.count > 0 ? '' : 'none';
            });
            btn.classList.add('btn-success');
            btn.innerHTML = '<i class="fas fa-check me-1"></i> Added';
            setTimeout(function () {
              btn.classList.remove('btn-success');
              btn.innerHTML = btn.getAttribute('data-original-text') || 'Add to Cart';
            }, 2000);
          }
        });
    });
    btn.setAttribute('data-original-text', btn.innerHTML);
  });

  // Coupon validate on checkout
  const couponForm = document.getElementById('couponForm');
  if (couponForm) {
    couponForm.addEventListener('submit', function (e) {
      e.preventDefault();
      const code = document.getElementById('coupon_code').value;
      const subtotal = parseFloat(couponForm.dataset.subtotal || '0');
      fetch((window.FURUTH_BASE || '') + '/api/coupon-validate.php?code=' + encodeURIComponent(code) + '&subtotal=' + subtotal)
        .then(function (r) { return r.json(); })
        .then(function (data) {
          const msg = document.getElementById('couponMessage');
          if (data.valid) {
            msg.className = 'text-success small mt-2';
            msg.textContent = 'Coupon applied: -' + data.discount_formatted;
            document.getElementById('applied_coupon').value = code;
            if (typeof window.updateCheckoutTotals === 'function') {
              window.updateCheckoutTotals(data.discount);
            }
          } else {
            msg.className = 'text-danger small mt-2';
            msg.textContent = data.message || 'Invalid coupon';
          }
        });
    });
  }
})();
