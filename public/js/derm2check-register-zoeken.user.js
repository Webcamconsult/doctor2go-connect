// ==UserScript==
// @name         Derm2Check – registratie automatisch opzoeken
// @namespace    https://derm2check.com/
// @version      1.0
// @description  Vult op de zoekpagina van een artsenregister automatisch naam of registratienummer in en start de zoekopdracht. Werkt met links uit registraties-dermatologen.html (met #d2c=... achter de URL).
// @match        https://zoeken.bigregister.nl/*
// @match        https://sisa.msal.gov.ar/*
// @match        https://cedulaprofesional.sep.gob.mx/*
// @match        https://webappsa.riziv-inami.fgov.be/*
// @match        https://annuaire.sante.fr/*
// @match        https://consultapublica.cgcom.es/*
// @match        https://ordemdosmedicos.pt/*
// @match        https://portal.cfm.org.br/*
// @run-at       document-idle
// @grant        none
// ==/UserScript==

(function () {
  'use strict';

  // ---- 1. Zoekopdracht uit de URL halen (en bewaren voor als de site doorstuurt) ----
  const KEY = 'd2c-zoekopdracht';
  const store = {
    get() { try { return sessionStorage.getItem(KEY); } catch (e) { return null; } },
    set(v) { try { sessionStorage.setItem(KEY, v); } catch (e) { /* niet beschikbaar */ } }
  };
  let q = null;
  const m = location.hash.match(/d2c=([^&]+)/);
  if (m) {
    try { q = JSON.parse(decodeURIComponent(m[1])); } catch (e) { /* ongeldig */ }
    if (q) {
      q.t = Date.now();
      store.set(JSON.stringify(q));
      history.replaceState(null, '', location.href.replace(/#?d2c=[^&]+/, ''));
    }
  } else {
    try { q = JSON.parse(store.get() || 'null'); } catch (e) { q = null; }
    if (q && Date.now() - q.t > 60000) q = null; // ouder dan 1 minuut: negeren
  }
  if (!q || q.done) return;

  const full = [q.f, q.l].filter(Boolean).join(' ');
  const shown = q.m === 'number' ? 'registratienummer ' + q.n : full;

  // ---- 2. Banner, zodat altijd zichtbaar is wat er gezocht wordt ----
  const bar = document.createElement('div');
  bar.style.cssText = 'position:fixed;z-index:2147483647;left:12px;right:12px;bottom:12px;' +
    'background:#0b5f73;color:#fff;font:14px/1.4 system-ui,sans-serif;padding:10px 14px;' +
    'border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,.25);display:flex;gap:10px;align-items:center;flex-wrap:wrap';
  const msg = document.createElement('span');
  msg.textContent = 'Derm2Check: zoeken naar ' + shown + '…';
  const copy = document.createElement('button');
  copy.textContent = 'Kopieer zoekterm';
  copy.style.cssText = 'margin-left:auto;background:#fff;color:#0b5f73;border:0;border-radius:6px;padding:4px 10px;cursor:pointer';
  copy.onclick = () => navigator.clipboard && navigator.clipboard.writeText(q.m === 'number' ? q.n : full);
  const close = document.createElement('button');
  close.textContent = '×';
  close.style.cssText = 'background:transparent;color:#fff;border:0;font-size:18px;cursor:pointer';
  close.onclick = () => bar.remove();
  bar.append(msg, copy, close);
  document.body.appendChild(bar);

  // ---- 3. Velden herkennen ----
  const RX = {
    number: /(n[uú]m|number|nummer|big|colegiad|col·legiat|c[eé]dula|crm|matr[ií]cula|inami|riziv|rpps|registr|inscri)/i,
    last:   /(apellido|surname|last|achternaam|famil|cognom|sobrenome|\bnom\b(?!bre))/i,
    first:  /(nombre|first|voornaam|pr[ée]nom|\bnome\b|given)/i,
    full:   /(name|naam|search|zoek|buscar|recherche|rechercher|pesquis|query|\bq\b|qui)/i
  };
  const SUBMIT = /(zoek|search|buscar|consultar|rechercher|recherche|pesquisar|enviar|chercher|ok\b|go\b|submit)/i;

  function hint(el) {
    let t = [el.name, el.id, el.placeholder, el.getAttribute('aria-label'), el.title, el.getAttribute('formcontrolname')].join(' ');
    if (el.id) { const lab = document.querySelector('label[for="' + CSS.escape(el.id) + '"]'); if (lab) t += ' ' + lab.textContent; }
    const wrap = el.closest('label, .form-group, .field, mat-form-field, div');
    if (wrap) t += ' ' + (wrap.textContent || '').slice(0, 80);
    return t;
  }
  function visible(el) { const r = el.getBoundingClientRect(); return r.width > 0 && r.height > 0 && getComputedStyle(el).visibility !== 'hidden'; }
  function setVal(el, v) {
    const proto = Object.getPrototypeOf(el);
    const setter = Object.getOwnPropertyDescriptor(proto, 'value');
    if (setter && setter.set) setter.set.call(el, v); else el.value = v;
    el.dispatchEvent(new Event('input', { bubbles: true }));
    el.dispatchEvent(new Event('change', { bubbles: true }));
    el.dispatchEvent(new Event('blur', { bubbles: true }));
  }

  function tryFill() {
    const inputs = [...document.querySelectorAll('input:not([type]),input[type=text],input[type=search],input[type=number],input[type=tel]')]
      .filter(el => visible(el) && !el.disabled && !el.readOnly);
    if (!inputs.length) return false;
    const filled = [];
    if (q.m === 'number') {
      const el = inputs.find(i => RX.number.test(hint(i))) || (inputs.length === 1 ? inputs[0] : null);
      if (el) { setVal(el, q.n); filled.push(el); }
    } else {
      const ln = inputs.find(i => RX.last.test(hint(i)) && !RX.number.test(i.name + i.id));
      const fn = inputs.find(i => i !== ln && RX.first.test(hint(i)) && !RX.number.test(i.name + i.id));
      if (ln) { setVal(ln, q.l); filled.push(ln); }
      if (fn) { setVal(fn, q.f); filled.push(fn); }
      if (!filled.length) {
        const el = inputs.find(i => RX.full.test(hint(i))) || inputs[0];
        setVal(el, full); filled.push(el);
      }
    }
    if (!filled.length) return false;
    filled.forEach(el => { el.style.outline = '3px solid #f5a623'; });

    setTimeout(() => {
      const form = filled[0].form;
      const scope = form || document;
      const btn = [...scope.querySelectorAll('button, input[type=submit], input[type=button], a.btn, [role=button]')]
        .filter(visible)
        .find(b => b.type === 'submit' || SUBMIT.test((b.textContent || '') + ' ' + (b.value || '') + ' ' + (b.getAttribute('aria-label') || '')));
      if (btn) btn.click();
      else if (form && form.requestSubmit) form.requestSubmit();
      else filled[filled.length - 1].dispatchEvent(new KeyboardEvent('keydown', { key: 'Enter', code: 'Enter', keyCode: 13, bubbles: true }));
      q.done = true;
      store.set(JSON.stringify(q));
      msg.textContent = 'Derm2Check: gezocht naar ' + shown + '. Klopt het resultaat niet? Kopieer de zoekterm en zoek handmatig.';
    }, 400);
    return true;
  }

  // Sites zijn vaak JavaScript-apps: tot 20 seconden wachten tot het formulier er is.
  const start = Date.now();
  (function loop() {
    if (tryFill()) return;
    if (Date.now() - start > 20000) {
      msg.textContent = 'Derm2Check: zoekformulier niet automatisch gevonden. Zoek handmatig op ' + shown + ' (knop rechts kopieert de zoekterm).';
      return;
    }
    setTimeout(loop, 500);
  })();
})();
