/**
 * Lead form ([d2gc_lead_form]): sends the form by AJAX and shows the answer in the form.
 * Other scripts can add fields before sending: listen to "d2gc:lead-form-submit" (on the form or the document) and
 * append to event.detail.data (FormData). Calling event.preventDefault() there stops the send.
 */
(function () {
  'use strict';

  if (!window.d2gcLeadForm) {
    return;
  }

  function showMessage(form, text, ok) {
    var box = form.querySelector('.d2gc-lead-message');
    box.textContent = text;
    box.classList.remove('d-none', 'alert-success', 'alert-danger');
    box.classList.add(ok ? 'alert-success' : 'alert-danger');
  }

  document.addEventListener('submit', function (event) {
    var form = event.target;
    if (!form.classList || !form.classList.contains('d2gc-lead-form')) {
      return;
    }
    event.preventDefault();

    if (!form.reportValidity()) {
      return;
    }

    var data = new FormData(form);
    data.append('action', window.d2gcLeadForm.action);

    var hook = new CustomEvent('d2gc:lead-form-submit', { bubbles: true, cancelable: true, detail: { form: form, data: data } });
    if (!form.dispatchEvent(hook)) {
      return;
    }

    var button = form.querySelector('button[type="submit"]');
    var label = button.textContent;
    button.disabled = true;
    button.textContent = window.d2gcLeadForm.i18n.sending;

    fetch(window.d2gcLeadForm.ajaxUrl, { method: 'POST', body: data, credentials: 'same-origin' })
      .then(function (response) { return response.json(); })
      .then(function (json) {
        var message = json && json.data && json.data.message ? json.data.message : window.d2gcLeadForm.i18n.error;
        showMessage(form, message, !!(json && json.success));
        if (json && json.success) {
          form.querySelectorAll('input, button').forEach(function (field) { field.disabled = true; });
          form.dispatchEvent(new CustomEvent('d2gc:lead-form-sent', { bubbles: true, detail: { form: form } }));
          return;
        }
        button.disabled = false;
        button.textContent = label;
      })
      .catch(function () {
        showMessage(form, window.d2gcLeadForm.i18n.error, false);
        button.disabled = false;
        button.textContent = label;
      });
  });

  // "No thanks" / close: hides the whole block (a popup then stays away for popup_days)
  document.addEventListener('click', function (event) {
    var link = event.target.closest ? event.target.closest('.d2gc-lead-dismiss') : null;
    if (!link) {
      return;
    }
    event.preventDefault();
    var block = link.closest('.d2gc-lead');
    if (block) {
      block.classList.add('d-none');
      rememberPopup(block);
    }
  });

  /* Popup (popup="idle"): appears after the visitor did nothing for data-popup-delay seconds -------------------- */

  function popupStorageKey(popup) {
    return 'd2gc_lead_popup_' + (popup.getAttribute('data-popup-key') || 'page');
  }

  function rememberPopup(block) {
    if (!block.classList.contains('d2gc-lead-popup')) {
      return;
    }
    try {
      localStorage.setItem(popupStorageKey(block), String(Date.now()));
    } catch (error) { /* not available: it may come back on the next page */ }
  }

  function popupAllowed(popup) {
    if (popup.hasAttribute('data-popup-off')) {
      return false;
    }
    var days = parseInt(popup.getAttribute('data-popup-days'), 10) || 0;
    try {
      var last = parseInt(localStorage.getItem(popupStorageKey(popup)), 10);
      return !last || Date.now() - last > days * 86400000;
    } catch (error) {
      return true;
    }
  }

  // not while a popup window is open or the visitor is filling in a field
  function visitorBusy() {
    var active = document.activeElement;
    return !!document.querySelector('.modal.show') ||
      !!(active && /^(INPUT|TEXTAREA|SELECT)$/.test(active.tagName));
  }

  function setupPopups() {
    var popups = Array.prototype.slice.call(document.querySelectorAll('.d2gc-lead-popup'));
    if (!popups.length) {
      return;
    }
    var timer = null;
    var shown = false;

    function show() {
      if (shown) {
        return;
      }
      if (visitorBusy()) {
        restart();
        return;
      }
      // the first box that is allowed (other scripts can switch one off with data-popup-off)
      var popup = popups.filter(popupAllowed)[0];
      if (!popup) {
        stop();
        return;
      }
      shown = true;
      stop();
      popup.classList.remove('d-none');
    }

    function restart() {
      clearTimeout(timer);
      var popup = popups.filter(popupAllowed)[0];
      if (popup && !shown) {
        timer = setTimeout(show, (parseInt(popup.getAttribute('data-popup-delay'), 10) || 60) * 1000);
      }
    }

    var events = ['scroll', 'mousemove', 'keydown', 'click', 'touchstart'];
    function stop() {
      clearTimeout(timer);
      events.forEach(function (name) { window.removeEventListener(name, restart, { passive: true }); });
    }
    events.forEach(function (name) { window.addEventListener(name, restart, { passive: true }); });
    restart();

    // sent: remember it and close the box after the thank-you message
    document.addEventListener('d2gc:lead-form-sent', function (event) {
      var block = event.detail.form.closest('.d2gc-lead-popup');
      if (block) {
        rememberPopup(block);
        setTimeout(function () { block.classList.add('d-none'); }, 6000);
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupPopups);
  } else {
    setupPopups();
  }
})();
