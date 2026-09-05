// ==UserScript==
// @name         NOEST Order Popup v10.2
// @namespace    noest
// @version      10.2
// @description  [v10.2] Bulk actions, Delivery Fees, Communes, Remarks, Returns, New Attempt — full API coverage + 25+ improvements
// @author       You
// @match        https://app.manychat.com/*
// @icon         https://app.noest-dz.com/favicon.ico
// @grant        GM_addStyle
// @grant        GM_xmlhttpRequest
// @connect      app.noest-dz.com
// @connect      noest-dz.s3.eu-west-3.amazonaws.com
// @connect      s3.eu-west-3.amazonaws.com
// @connect      amazonaws.com
// ==/UserScript==

(function () {
  'use strict';

  /* ══════════════════════════════════════════════════════════
   *  MODULE 1 ▸ STORAGE MANAGER
   *  Central LocalStorage handler with versioning + validation
   * ══════════════════════════════════════════════════════════ */
  const StorageManager = (() => {
    const NS      = 'noest_v10_';
    const VERSION = '10.2'; // [v10.2 QUALITY] version sync
    const MAX_PDF_HISTORY   = 20;
    const MAX_ORDER_HISTORY = 20;

    const ALLOWED_SETTINGS = [
      'TOKEN','GUID','API_BASE','REQUEST_TIMEOUT_MS','AUTO_RETRY_COUNT',
      'autoDownloadPdf','openPdfNewTab','showConfirmCreate','enableAnimations',
      'enableSounds','darkMode','compactMode','autoCopyTracking',
      'rememberWilaya','rememberStation','rememberCustomer','enableAdvancedLogs',
    ];

    function _key(k)    { return NS + k; }
    function _set(k, v) { try { localStorage.setItem(_key(k), JSON.stringify(v)); } catch(_) {} }
    function _get(k, d) {
      try {
        const raw = localStorage.getItem(_key(k));
        return raw !== null ? JSON.parse(raw) : d;
      } catch(_) { return d; }
    }
    function _del(k) { try { localStorage.removeItem(_key(k)); } catch(_) {} }

    // [v10.2 PERF] skip migration if already done
    function _migrate() {
      if (_get('migrated_from_v9')) return;
      const oldNS = 'noest_v9_';
      const keys  = ['settings','preferences','lastOrder'];
      keys.forEach(k => {
        try {
          const old = localStorage.getItem(oldNS + k);
          if (old && !localStorage.getItem(_key(k))) {
            localStorage.setItem(_key(k), old);
          }
        } catch(_) {}
      });
      _set('migrated_from_v9', true);
    }
    _migrate();

    const defaults = {
      settings: {
        TOKEN:              '',
        GUID:               '',
        API_BASE:           'https://app.noest-dz.com/api/public',
        REQUEST_TIMEOUT_MS: 30000,
        AUTO_RETRY_COUNT:   0,
        autoDownloadPdf:    false,
        openPdfNewTab:      false,
        showConfirmCreate:  true,
        enableAnimations:   true,
        enableSounds:       false,
        darkMode:           false,
        compactMode:        false,
        autoCopyTracking:   false,
        rememberWilaya:     true,
        rememberStation:    true,
        rememberCustomer:   false,
        enableAdvancedLogs: false,
      },
      preferences: {
        lastWilayaId:    '',
        lastWilayaName:  '',
        lastStationCode: '',
        lastStationName: '',
        lastDeliveryType:'1',
        lastProductIdx:  '',
        lastCustomer:   { client:'', phone:'', phone2:'', adresse:'' },
        lastFilters:    {},
      },
      lastOrder: null,
    };

    return {
      getSettings()      { return { ...defaults.settings, ..._get('settings', {}) }; },
      saveSettings(s)    { _set('settings', s); },
      getPreferences()   { return { ...defaults.preferences, ..._get('preferences', {}) }; },
      savePreferences(p) { _set('preferences', p); },
      getLastOrder()     { return _get('lastOrder', null); },
      saveLastOrder(o)   {
        _set('lastOrder', o);
        // Also push to order history
        const h = _get('orderHistory', []);
        h.unshift({ ...o, savedAt: new Date().toISOString() });
        _set('orderHistory', h.slice(0, MAX_ORDER_HISTORY));
      },

      /* ── PDF History ── */
      getPdfHistory()    { return _get('pdfHistory', []); },
      addPdfHistory(entry) {
        const h = _get('pdfHistory', []);
        h.unshift({ ...entry, savedAt: new Date().toISOString() });
        _set('pdfHistory', h.slice(0, MAX_PDF_HISTORY));
      },
      clearPdfHistory()  { _del('pdfHistory'); },

      /* ── Order History ── */
      getOrderHistory()  { return _get('orderHistory', []); },
      getTotalOrderCount() { return _get('orderHistory', []).length; },

      /* ── Export / Import ── */
      exportAll() {
        return JSON.stringify({
          version:      VERSION,
          exportedAt:   new Date().toISOString(),
          settings:     _get('settings', {}),
          preferences:  _get('preferences', {}),
          lastOrder:    _get('lastOrder', null),
          pdfHistory:   _get('pdfHistory', []),
          orderHistory: _get('orderHistory', []),
        }, null, 2);
      },
      importAll(jsonStr) {
        let data;
        try { data = JSON.parse(jsonStr); }
        catch { throw new Error('ملف JSON غير صالح'); }
        if (typeof data !== 'object' || !data) throw new Error('بنية البيانات غير صالحة');

        // Whitelist settings to prevent injection
        if (data.settings && typeof data.settings === 'object') {
          const safe = {};
          ALLOWED_SETTINGS.forEach(k => {
            if (k in data.settings) safe[k] = data.settings[k];
          });
          _set('settings', safe);
        }
        if (data.preferences && typeof data.preferences === 'object') _set('preferences', data.preferences);
        if (data.lastOrder)    _set('lastOrder', data.lastOrder);
        if (Array.isArray(data.pdfHistory))   _set('pdfHistory',   data.pdfHistory.slice(0, MAX_PDF_HISTORY));
        if (Array.isArray(data.orderHistory)) _set('orderHistory', data.orderHistory.slice(0, MAX_ORDER_HISTORY));
        return true;
      },

      // [v10.2 UX] track last settings save time
      getLastSaveTs()    { return _get('lastSaveTs', null); },
      setLastSaveTs(ts)  { _set('lastSaveTs', ts); },
      clearCache() {
        _del('communes_cache');
        _del('wilayas_cache');
        _del('desks_cache');
      },
      resetAll() {
        Object.keys(localStorage)
          .filter(k => k.startsWith(NS))
          .forEach(k => localStorage.removeItem(k));
      },
      version: VERSION,
    };
  })();

  /* ══════════════════════════════════════════════════════════
   *  MODULE 2 ▸ CONFIG  (live from StorageManager)
   * ══════════════════════════════════════════════════════════ */
  let _settings = StorageManager.getSettings();

  const Config = {
    get TOKEN()              { return _settings.TOKEN              || 'TOKEN'; },
    get GUID()               { return _settings.GUID               || 'GUID'; },
    get API_BASE()           { return _settings.API_BASE           || 'https://app.noest-dz.com/api/public'; },
    get REQUEST_TIMEOUT_MS() { return _settings.REQUEST_TIMEOUT_MS || 30000; },
    get AUTO_RETRY_COUNT()   { return _settings.AUTO_RETRY_COUNT   || 0; },
    get autoDownloadPdf()    { return !!_settings.autoDownloadPdf; },
    get showConfirmCreate()  { return _settings.showConfirmCreate !== false; },
    get autoCopyTracking()   { return !!_settings.autoCopyTracking; },
    get rememberWilaya()     { return _settings.rememberWilaya !== false; },
    get rememberStation()    { return _settings.rememberStation !== false; },
    get rememberCustomer()   { return !!_settings.rememberCustomer; },
    get enableAdvancedLogs() { return !!_settings.enableAdvancedLogs; },
    DEBOUNCE_MS:  180,
    CACHE_TTL_MS: 30 * 60 * 1000,
    reload() { _settings = StorageManager.getSettings(); },
  };

  /* ══════════════════════════════════════════════════════════
   *  MODULE 3 ▸ PRODUCTS
   * ══════════════════════════════════════════════════════════ */
  const Products = [
    { name: 'Leader Plan 01',      price: 20000, description: 'احصل على 10000 طلبية لمدة 180 يوم' },
    { name: 'Leader Plan 02',      price: 30000, description: 'طلبيات غير محدودة لمدة 180 يوم' },
    { name: 'Leader Plan 5000',    price: 10000, description: 'احصل على 5000 طلبية لمدة 90 يوم' },
    { name: 'Leader Plan 02 3Moi', price: 15000, description: 'طلبيات غير محدودة لمدة 90 يوم' },
    { name: 'Leader Plan 02 2Moi', price: 10000, description: 'طلبيات غير محدودة لمدة 60 يوم' },
    { name: 'Silver Plan',         price:  4200, description: 'احصل على 600 + 400 طلبية لمدة 90 يوم' },
    { name: 'Bronze Plan',         price:  6500, description: 'احصل على 1000 + 500 طلبية لمدة 90 يوم' },
    { name: 'Win Win Plan',        price:  2000, description: '10 DA / طلبية لمدة 90 يوم' },
  ];

  /* ══════════════════════════════════════════════════════════
   *  MODULE 4 ▸ UTILS
   * ══════════════════════════════════════════════════════════ */
  const Utils = {
    generateRef() {
      const c = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
      let r = '';
      for (let i = 0; i < 6; i++) r += c[Math.floor(Math.random() * c.length)];
      return 'REF-' + r;
    },
    debounce(fn, ms) {
      let t;
      return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), ms); };
    },
    copyToClipboard(text) {
      if (navigator.clipboard) return navigator.clipboard.writeText(text);
      const ta = document.createElement('textarea');
      ta.value = text; ta.style.cssText = 'position:fixed;opacity:0';
      document.body.appendChild(ta); ta.select();
      document.execCommand('copy'); ta.remove();
      return Promise.resolve();
    },
    escapeHtml(str) {
      return String(str ?? '')
        .replace(/&/g, '&amp;').replace(/</g, '&lt;')
        .replace(/>/g, '&gt;').replace(/"/g, '&quot;')
        .replace(/'/g, '&#x27;');
    },
    setText(el, text) { if (el) el.textContent = String(text ?? ''); },
    formatPhone(raw)  { return raw.replace(/\D/g, '').slice(0, 10); },
    isValidAlgerianPhone(p) { return /^(05|06|07)\d{8}$/.test(p); },
    formatDA(n) { return Number(n).toLocaleString('fr-DZ') + ' DA'; },
    typeLabel(t) { return t == 1 ? 'توصيل' : t == 2 ? 'استبدال' : t == 3 ? 'استرجاع' : '—'; },
  };

  /* ══════════════════════════════════════════════════════════
   *  MODULE 5 ▸ CACHE SERVICE
   * ══════════════════════════════════════════════════════════ */
  const CacheService = (() => {
    const store    = new Map();
    const inflight = new Map();
    return {
      get(key) {
        const e = store.get(key);
        if (!e) return null;
        // [v10.1 PERF] respect per-entry maxAge
        const ttl = e.maxAge || Config.CACHE_TTL_MS;
        if (Date.now() - e.ts > ttl) { store.delete(key); return null; }
        return e.data;
      },
      // [v10.2 BUG] explicit maxAge guard (falsy 0 should not fallback to TTL)
      set(key, data, maxAge) {
        const ttl = maxAge !== undefined ? maxAge : Config.CACHE_TTL_MS;
        store.set(key, { data, ts: Date.now(), maxAge: ttl });
      },
      del(key)               { store.delete(key); },
      clear()                { store.clear(); },
      getInflight(key)       { return inflight.get(key) || null; },
      setInflight(key, p)    { inflight.set(key, p); },
      clearInflight(key)     { inflight.delete(key); },
    };
  })();

  /* ══════════════════════════════════════════════════════════
   *  MODULE 6 ▸ ERROR MANAGER
   * ══════════════════════════════════════════════════════════ */
  const ErrorManager = {
    _codes: {
      account_suspended:   'الحساب موقوف، تواصل مع NOEST',
      duplicate_order:     'طلبية بهذا المرجع موجودة مسبقاً',
      inactive_commune:    'البلدية غير موجودة أو غير مفعّلة',
      zip_code:            'الرمز البريدي غير صالح',
      max_amount_exceeded: 'المبلغ يتجاوز الحد المسموح',
      stopdesk_disabled:   'Stop Desk غير متاح لهذه الولاية',
      station_expedition:  'كود محطة الشحن غير صالح',
      station_code:        'كود المحطة لا يطابق الولاية',
      disabled_module:     'وحدة المخزون غير مفعّلة',
      wrong_quantities:    'عدد الكميات لا يطابق عدد المنتجات',
      invalid_product:     'المنتج غير موجود أو معطّل',
      out_of_stock:        'المخزون غير كافٍ',
      already_validated:   'الطلبية تم اعتمادها مسبقاً',
    },
    _messages: {
      'Commande introuvable':              'الطلبية غير موجودة أو لا تنتمي لحسابك',
      'Commande déjà validée':             'تم اعتماد هذه الطلبية مسبقاً',
      'Stock insuffisant':                 'المخزون غير كافٍ لاعتماد هذه الطلبية',
      'Trackings not found':               'رقم التتبع غير موجود',
      "Commande non trouvée dans l'étape de modification": 'الطلبية غير موجودة أو تم شحنها مسبقاً',
    },
    parse(data) {
      if (!data) return 'خطأ غير معروف';
      if (typeof data === 'string') return this._messages[data] || data;
      if (data.message && this._codes[data.message])    return this._codes[data.message];
      if (data.message && this._messages[data.message]) return this._messages[data.message];
      const errKeys = Object.keys(data).filter(k => Array.isArray(data[k]) && k !== 'trackings');
      if (errKeys.length) return errKeys.map(k => `${k}: ${data[k][0]}`).join(' | ');
      return data.message || 'خطأ غير معروف من الخادم';
    },
    log(context, err) {
      if (Config.enableAdvancedLogs) console.error(`[NOEST v10.2][${context}]`, err); // [v10.2 QUALITY] version string
    },
  };

  /* ══════════════════════════════════════════════════════════
   *  MODULE 7 ▸ API SERVICE
   * ══════════════════════════════════════════════════════════ */
  const ApiService = {
    // [v10.2 SEC] sanitize TOKEN against header injection
    _sanitizedToken()    { return String(Config.TOKEN).replace(/[\r\n]/g, ''); },
    _headers(extra = {}) {
      return { Authorization: `Bearer ${this._sanitizedToken()}`, 'Content-Type': 'application/json', ...extra };
    },
    // [v10.2 PERF] include API_BASE in cache key to avoid stale data after config change
    _cacheKey(path)      { return `${Config.API_BASE}${path}`; },
    async get(path) {
      const ck = this._cacheKey(path);
      const cached = CacheService.get(ck);
      if (cached) return cached;
      const existing = CacheService.getInflight(ck);
      if (existing) return existing;
      const promise = this._fetchWithTimeout(`${Config.API_BASE}${path}`, {
        headers: this._headers(),
      }).then(async res => {
        if (!res.ok) throw new Error(`HTTP ${res.status} on GET ${path}`);
        const data = await res.json();
        CacheService.set(ck, data);
        return data;
      }).finally(() => CacheService.clearInflight(ck));
      CacheService.setInflight(ck, promise);
      return promise;
    },
    async post(path, body) {
      const res = await this._fetchWithTimeout(`${Config.API_BASE}${path}`, {
        method: 'POST',
        headers: this._headers(),
        body: JSON.stringify(body),
      });
      const data = await res.json();
      return { ok: res.ok, status: res.status, data };
    },
    _fetchWithTimeout(url, opts) {
      const controller = new AbortController();
      const id = setTimeout(() => controller.abort(), Config.REQUEST_TIMEOUT_MS);
      return fetch(url, { ...opts, signal: controller.signal })
        .catch(err => {
          if (err.name === 'AbortError') throw new Error('انتهت مهلة الطلب');
          throw new Error('فشل الاتصال بالخادم');
        })
        .finally(() => clearTimeout(id));
    },
    getWilayas()  { return this.get('/get/wilayas'); },
    getDesks()    { return this.get('/desks'); },
    createOrder(p)                 { return this.post('/create/order', p); },
    deleteOrder(tracking)          { return this.post('/delete/order', { user_guid: Config.GUID, tracking }); },
    updateOrder(p)                 { return this.post('/update/order', p); },
    updateBeforeExpedition(p)      { return this.post('/update/order/before/expedition', p); },
    validateOrder(tracking)        { return this.post('/valid/order', { user_guid: Config.GUID, tracking }); },
    getTrackingInfo(trackings)     { return this.post('/get/trackings/info', { trackings }); },
    getLabelUrl(tracking)          { return `${Config.API_BASE}/get/order/label?tracking=${encodeURIComponent(tracking)}`; },

    // ── v10.2 API additions ──────────────────────────────
    // [v10.2 API] Add Remark / Update delivery info
    addRemark(tracking, content)    { return this.post('/add/maj', { tracking, content }); },
    // [v10.2 API] New Delivery Attempt
    askNewAttempt(tracking)         { return this.post('/ask/new-tentative', { tracking }); },
    // [v10.2 API] Request Return
    askReturn(tracking)             { return this.post('/ask/return', { tracking }); },
    // [v10.2 API] Bulk Create (max 100)
    createOrders(userGuid, orders)  { return this.post('/create/orders', { user_guid: userGuid, orders }); },
    // [v10.2 API] Bulk Validate
    validateOrders(userGuid, trackings) { return this.post('/valid/orders', { user_guid: userGuid, trackings }); },
    // [v10.2 API] Delivery Fees table
    getFees()                       { return this.get('/fees'); },
    // [v10.2 API] Communes for a wilaya
    getCommunes(wilayaId)           { return this.get(`/get/communes/${wilayaId}`); },

    /** Test connection — fetches wilayas and returns success/fail */
    async testConnection() {
      try {
        CacheService.del(this._cacheKey('/get/wilayas'));
        const data = await this.get('/get/wilayas');
        return { ok: Array.isArray(data) && data.length > 0, count: data.length };
      } catch(e) {
        return { ok: false, error: e.message };
      }
    },
  };

  /* ══════════════════════════════════════════════════════════
   *  MODULE 8 ▸ LABEL SERVICE
   * ══════════════════════════════════════════════════════════ */
  const LabelService = {
    // [v10.1 PERF] rate-limit fallback to 1 per 2s
    _lastFallbackTs: 0,
    download(tracking, onStart, onDone, onError) {
      onStart && onStart();
      GM_xmlhttpRequest({
        method: 'GET',
        url: ApiService.getLabelUrl(tracking),
        headers: { Authorization: `Bearer ${Config.TOKEN}` },
        responseType: 'blob',
        anonymous: false,
        redirect: 'follow',
        onload: (res) => {
          if (res.status >= 200 && res.status < 300 && res.response instanceof Blob) {
            const blob = new Blob([res.response], { type: 'application/pdf' });
            const url = URL.createObjectURL(blob);
            const a = Object.assign(document.createElement('a'), {
              href: url, download: `NOEST-${tracking}.pdf`, style: 'display:none',
            });
            document.body.appendChild(a); a.click();
            setTimeout(() => { URL.revokeObjectURL(url); a.remove(); }, 3000);
            onDone && onDone('download');
          } else if (res.status >= 300 && res.status < 400 && res.finalUrl) {
            window.open(res.finalUrl, '_blank');
            onDone && onDone('redirect');
          } else {
            this._fallback(tracking);
            onDone && onDone('direct');
          }
        },
        // [v10.2 BUG] call onError when provided, fallback to onDone for backward compat
        onerror:  () => { this._fallback(tracking); if (onError) onError(); else onDone && onDone('fallback'); },
        onabort:  () => { this._fallback(tracking); if (onError) onError(); else onDone && onDone('fallback'); },
      });
    },
    _fallback(t) {
      // [v10.1 PERF] rate limit + URL cleanup
      const now = Date.now();
      if (now - this._lastFallbackTs < 2000) { console.warn('[NOEST] PDF fallback rate-limited'); return; }
      this._lastFallbackTs = now;
      const url = ApiService.getLabelUrl(t) + `&t=${now}`;
      const w = window.open(url, '_blank', 'noopener');
      // cleanup if blocked by popup blocker
      if (!w || w.closed) { /* silent fail */ }
    },
  };

  /* ══════════════════════════════════════════════════════════
   *  MODULE 9 ▸ NOTIFICATION SERVICE  (toast queue)
   * ══════════════════════════════════════════════════════════ */
  const Notify = (() => {
    let container;

    function ensureContainer() {
      if (container && document.body.contains(container)) return;
      container = document.createElement('div');
      container.id = 'n9-toasts';
      document.body.appendChild(container);
    }

    function show(type, title, message, duration = 4500) {
      ensureContainer();
      // [v10.1 PERF] cap at 5 visible toasts; oldest auto-dismissed when 6th arrives
      while (container.children.length >= 5) {
        const oldest = container.children[0];
        if (oldest) remove(oldest);
      }
      const icons = { success: '✅', error: '❌', warning: '⚠️', info: 'ℹ️' };
      const t = document.createElement('div');
      t.className = `n9-toast n9-toast--${type}`;

      const iconSpan = document.createElement('span');
      iconSpan.className = 'n9-toast-icon';
      iconSpan.textContent = icons[type] || '•';

      const bodyDiv = document.createElement('div');
      bodyDiv.className = 'n9-toast-body';
      const strong = document.createElement('strong');
      strong.textContent = title;
      bodyDiv.appendChild(strong);
      if (message) {
        const msgSpan = document.createElement('span');
        msgSpan.textContent = message;
        bodyDiv.appendChild(msgSpan);
      }

      // Progress bar
      const progress = document.createElement('div');
      progress.className = 'n9-toast-progress';
      const progressBar = document.createElement('div');
      progressBar.className = 'n9-toast-progress-bar';
      if (duration > 0) {
        progressBar.style.animationDuration = `${duration}ms`;
        progress.appendChild(progressBar);
        t.appendChild(progress);
      }

      const closeBtn = document.createElement('button');
      closeBtn.className = 'n9-toast-close';
      closeBtn.textContent = '×';
      closeBtn.addEventListener('click', () => remove(t));

      t.append(iconSpan, bodyDiv, closeBtn);
      container.appendChild(t);
      requestAnimationFrame(() => t.classList.add('in'));
      if (duration > 0) setTimeout(() => remove(t), duration);
    }

    function remove(t) {
      t.classList.remove('in');
      t.classList.add('out');
      setTimeout(() => t.remove(), 350);
    }

    return {
      success: (t, m, d) => show('success', t, m, d),
      error:   (t, m, d) => show('error',   t, m, d),
      warning: (t, m, d) => show('warning', t, m, d),
      info:    (t, m, d) => show('info',    t, m, d),
    };
  })();

  /* ══════════════════════════════════════════════════════════
   *  MODULE 10 ▸ VALIDATION SERVICE
   * ══════════════════════════════════════════════════════════ */
  const ValidationService = {
    _errs: new Map(),
    clearAll(scope) {
      this._errs.clear();
      const root = scope || document;
      root.querySelectorAll('.n9-err').forEach(e => e.remove());
      root.querySelectorAll('.n9-field--err').forEach(f => f.classList.remove('n9-field--err'));
    },
    mark(id, msg) {
      this._errs.set(id, msg);
      const el = document.getElementById(id);
      if (!el) return;
      const wrap = el.closest('.n9-field') || el.parentElement;
      if (!wrap) return;
      wrap.classList.add('n9-field--err');
      let e = wrap.querySelector('.n9-err');
      if (!e) { e = document.createElement('span'); e.className = 'n9-err'; wrap.appendChild(e); }
      e.textContent = msg;
    },
    hasErrors() { return this._errs.size > 0; },
    run(fields) {
      this.clearAll();
      fields.forEach(({ id, value, label, rules }) => {
        for (const rule of rules) {
          const err = rule(value, label);
          if (err) { this.mark(id, err); break; }
        }
      });
      if (this.hasErrors()) {
        const firstErrId = [...this._errs.keys()][0];
        const firstEl = document.getElementById(firstErrId);
        if (firstEl) firstEl.focus();
      }
      return !this.hasErrors();
    },
    rules: {
      required: (v, l) => (!v || !String(v).trim()) ? `${l} مطلوب` : null,
      algPhone: (v)    => v && !Utils.isValidAlgerianPhone(v) ? 'رقم جزائري غير صالح (05/06/07)' : null,
      positive: (v)    => v !== '' && Number(v) < 0 ? 'لا يمكن أن تكون سالبة' : null,
      notNaN:   (v)    => isNaN(Number(v)) ? 'قيمة غير صالحة' : null,
      minLen:   (min)  => (v) => v && String(v).trim().length < min ? `يجب أن يكون ${min} أحرف على الأقل` : null,
    },
  };

  /* ══════════════════════════════════════════════════════════
   *  MODULE 11 ▸ STATE MANAGER
   * ══════════════════════════════════════════════════════════ */
  const StateManager = (() => {
    const state = {
      currentTab:      'create',
      modalOpen:       false,
      createPending:   false,
      editTracking:    null,
      editOrderData:   null,
      editMode:        'before',
      editPending:     false,
      deleteTracking:  null,
      deleteOrderData: null,
      deletePending:   false,
      validatePending: false,
      wilayas:         [],
      desks:           {},
    };
    const listeners = new Map();
    return {
      get(key)       { return state[key]; },
      set(key, value){
        state[key] = value;
        (listeners.get(key) || []).forEach(fn => fn(value));
      },
      on(key, fn)    {
        if (!listeners.has(key)) listeners.set(key, []);
        listeners.get(key).push(fn);
      },
      getAll() { return { ...state }; },
    };
  })();

  /* ══════════════════════════════════════════════════════════
   *  MODULE 12 ▸ SEARCHABLE DROPDOWN COMPONENT
   * ══════════════════════════════════════════════════════════ */
  function createDropdown({ inputId, dropId, pillId, pillTxtId, clearId, hiddenId, onSelect, onClear, placeholder }) {
    const inputEl  = document.getElementById(inputId);
    const dropEl   = document.getElementById(dropId);
    const pillEl   = document.getElementById(pillId);
    const pillTxt  = document.getElementById(pillTxtId);
    const clearEl  = document.getElementById(clearId);
    const hiddenEl = document.getElementById(hiddenId);
    let _items = [];

    const search = Utils.debounce((q) => _render(q), Config.DEBOUNCE_MS);

    function setItems(items) { _items = items; }

    function _hl(text, q) {
      if (!q) return Utils.escapeHtml(text);
      const re = new RegExp(`(${q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
      return Utils.escapeHtml(text).replace(re, '<mark>$1</mark>');
    }

    function _render(q = '') {
      q = (q || '').toLowerCase().trim();
      const filtered = _items.filter(it =>
        !q || it.label.toLowerCase().includes(q) || String(it.badge || '').toLowerCase().includes(q)
      );
      dropEl.innerHTML = '';
      if (!filtered.length) {
        const empty = document.createElement('div');
        empty.className = 'n9-drop-empty';
        empty.textContent = 'لا توجد نتائج';
        dropEl.appendChild(empty);
      } else {
        const frag = document.createDocumentFragment();
        filtered.forEach(it => {
          const d = document.createElement('div');
          d.className = 'n9-drop-item';
          if (it.badge) {
            const badge = document.createElement('span');
            badge.className = 'n9-badge';
            badge.textContent = String(it.badge);
            const labelSpan = document.createElement('span');
            labelSpan.innerHTML = _hl(it.label, q);
            d.append(badge, labelSpan);
          } else {
            const labelSpan = document.createElement('span');
            labelSpan.innerHTML = _hl(it.label, q);
            d.appendChild(labelSpan);
          }
          d.addEventListener('mousedown', ev => { ev.preventDefault(); _pick(it); });
          frag.appendChild(d);
        });
        dropEl.appendChild(frag);
      }
      dropEl.classList.add('open');
    }

    function _pick(it, silent = false) {
      hiddenEl.value = it.value;
      pillTxt.textContent = it.badge ? `[${it.badge}]  ${it.label}` : it.label;
      pillEl.style.display = 'flex';
      inputEl.style.display = 'none';
      dropEl.classList.remove('open');
      if (!silent) onSelect && onSelect(it);
    }

    function reset(silent = false) {
      hiddenEl.value = '';
      inputEl.value  = '';
      inputEl.style.display = '';
      inputEl.placeholder   = placeholder || 'ابحث...';
      pillEl.style.display  = 'none';
      dropEl.classList.remove('open');
      if (!silent) onClear && onClear();
    }

    function pickByValue(value, silent = false) {
      const item = _items.find(it => String(it.value) === String(value));
      if (item) _pick(item, silent);
    }

    function enable(ph)  { inputEl.disabled = false; inputEl.placeholder = ph || placeholder || 'ابحث...'; }
    function disable(ph) { reset(true); inputEl.disabled = true; inputEl.placeholder = ph || 'غير متاح'; }

    inputEl.addEventListener('focus', () => _render(inputEl.value));
    inputEl.addEventListener('input', () => search(inputEl.value));
    clearEl.addEventListener('click', () => reset());

    return { setItems, reset, enable, disable, pick: _pick, pickByValue };
  }

  /* ══════════════════════════════════════════════════════════
   *  MODULE 13 ▸ ORDER SERVICE
   * ══════════════════════════════════════════════════════════ */
  const OrderService = {
    async create(payload) {
      const { ok, data } = await ApiService.createOrder(payload);
      if (!ok && !data) throw new Error('فشل الاتصال بالخادم');
      return { success: data.success, data };
    },
    async loadTracking(tracking) {
      const { ok, data } = await ApiService.getTrackingInfo([tracking]);
      if (!ok) throw new Error('فشل الاتصال');
      if (data.message === 'Trackings not found' || !data[tracking]) {
        throw new Error('الطلبية غير موجودة');
      }
      return data[tracking];
    },
  };

  /* ══════════════════════════════════════════════════════════
   *  MODULE 14 ▸ TAB MANAGER
   * ══════════════════════════════════════════════════════════ */
  const TabManager = (() => {
    // [v10.1 FEATURE] added 'pdf' tab
    const _tabs = ['create', 'validate', 'edit', 'delete', 'pdf', 'settings'];
    // [v10.2 A11Y] toggle aria-selected on tab switch
    function switchTo(tab) {
      StateManager.set('currentTab', tab);
      _tabs.forEach(t => {
        const btn  = document.getElementById(`n9-tab-${t}`);
        const pane = document.getElementById(`n9-pane-${t}`);
        if (btn)  { btn.classList.toggle('active', t === tab); btn.setAttribute('aria-selected', String(t === tab)); }
        if (pane) pane.style.display = t === tab ? 'flex' : 'none';
      });
    }
    return { switchTo, current: () => StateManager.get('currentTab') };
  })();

  /* ══════════════════════════════════════════════════════════
   *  MODULE 15 ▸ MODAL MANAGER
   * ══════════════════════════════════════════════════════════ */
  const ModalManager = (() => {
    let _overlay, _modal;
    // [v10.2 BUG] store handler for cleanup to prevent listener leak
    let _focusHandler = null;
    function init(o, m) { _overlay = o; _modal = m; }
    // [v10.1 A11Y] focus trap helper
    function _trapFocus(modalEl) {
      // [v10.2 BUG] remove previous listener if any
      if (_focusHandler) modalEl.removeEventListener('keydown', _focusHandler);
      const focusable = modalEl.querySelectorAll(
        'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
      );
      const first = focusable[0];
      const last  = focusable[focusable.length - 1];
      _focusHandler = function handler(e) {
        if (e.key !== 'Tab') return;
        if (e.shiftKey) {
          if (document.activeElement === first) { e.preventDefault(); last && last.focus(); }
        } else {
          if (document.activeElement === last) { e.preventDefault(); first && first.focus(); }
        }
      };
      modalEl.addEventListener('keydown', _focusHandler);
    }
    function open(tab = 'create') {
      if (StateManager.get('modalOpen')) { TabManager.switchTo(tab); updateFooter(tab); return; }
      StateManager.set('modalOpen', true);
      _overlay.style.display = 'block';
      _modal.style.pointerEvents = 'auto';
      requestAnimationFrame(() => {
        _overlay.classList.add('in');
        _modal.classList.add('open');
      });
      TabManager.switchTo(tab);
      updateFooter(tab);
      setTimeout(() => {
        const closeBtn = document.getElementById('n9-close');
        if (closeBtn) closeBtn.focus();
        _trapFocus(_modal);
      }, 320);
    }
    function close() {
      if (!StateManager.get('modalOpen')) return;
      StateManager.set('modalOpen', false);
      // [v10.2 BUG] clean up focus handler
      if (_focusHandler && _modal) _modal.removeEventListener('keydown', _focusHandler);
      _focusHandler = null;
      _overlay.classList.remove('in');
      _modal.classList.remove('open');
      setTimeout(() => {
        if (!StateManager.get('modalOpen')) {
          _overlay.style.display = 'none';
          _modal.style.pointerEvents = 'none';
        }
      }, 320);
    }
    return { init, open, close };
  })();

  /* ══════════════════════════════════════════════════════════
   *  MODULE 16 ▸ CONFIRMATION DIALOG
   * ══════════════════════════════════════════════════════════ */
  const ConfirmDialog = (() => {
    let _el = null;
    let _resolve = null;

    function _build() {
      const el = document.createElement('div');
      el.id = 'n9-confirm-overlay';
      el.innerHTML = `
        <div id="n9-confirm-box" role="dialog" aria-modal="true" aria-labelledby="n9-confirm-title">
          <div class="n9-confirm-icon" id="n9-confirm-icon">⚠️</div>
          <h3 class="n9-confirm-title" id="n9-confirm-title"></h3>
          <p  class="n9-confirm-msg"  id="n9-confirm-msg"></p>
          <div class="n9-confirm-details" id="n9-confirm-details"></div>
          <div class="n9-confirm-actions">
            <button class="n9-btn n9-btn--ghost" id="n9-confirm-cancel">✕ إلغاء</button>
            <button class="n9-btn"               id="n9-confirm-ok"></button>
          </div>
        </div>`;
      document.body.appendChild(el);
      el.addEventListener('click', e => { if (e.target === el) _done(false); });
      document.getElementById('n9-confirm-cancel').addEventListener('click', () => _done(false));
      document.getElementById('n9-confirm-ok').addEventListener('click',     () => _done(true));
      el.addEventListener('keydown', e => {
        if (e.key === 'Escape') _done(false);
        if (e.key === 'Enter')  _done(true);
      });
      _el = el;
    }

    function _done(result) {
      if (!_el) return;
      _el.classList.remove('in');
      setTimeout(() => { if (_el) _el.style.display = 'none'; }, 280);
      if (_resolve) { _resolve(result); _resolve = null; }
    }

    function show({ title, message, confirmLabel, confirmClass, icon = '⚠️', details = [] }) {
      if (!_el) _build();
      document.getElementById('n9-confirm-icon').textContent  = icon;
      document.getElementById('n9-confirm-title').textContent = title;
      document.getElementById('n9-confirm-msg').textContent   = message;

      const detailsEl = document.getElementById('n9-confirm-details');
      detailsEl.innerHTML = '';
      if (details.length) {
        details.forEach(({ label, value }) => {
          const row = document.createElement('div');
          row.className = 'n9-confirm-detail-row';
          const lbl = document.createElement('span');
          lbl.className = 'n9-confirm-detail-lbl';
          lbl.textContent = label;
          const val = document.createElement('span');
          val.className = 'n9-confirm-detail-val';
          val.textContent = value;
          row.append(lbl, val);
          detailsEl.appendChild(row);
        });
      }

      const okBtn = document.getElementById('n9-confirm-ok');
      okBtn.textContent = confirmLabel || 'تأكيد';
      okBtn.className = `n9-btn ${confirmClass || 'n9-btn--create'}`;

      _el.style.display = 'flex';
      requestAnimationFrame(() => _el.classList.add('in'));
      okBtn.focus();
      return new Promise(resolve => { _resolve = resolve; });
    }

    return { show };
  })();

  /* ══════════════════════════════════════════════════════════
   *  MODULE 17 ▸ SUCCESS DIALOG
   * ══════════════════════════════════════════════════════════ */
  const SuccessDialog = (() => {
    let _el = null;
    let _currentTracking = null;
    let _currentRef = null;

    function _build() {
      const el = document.createElement('div');
      el.id = 'n9-success-overlay';
      el.innerHTML = `
        <div id="n9-success-box" role="dialog" aria-modal="true" aria-label="نتيجة إنشاء الطلبية">
          <div class="n9-suc-header">
            <div class="n9-suc-anim">✅</div>
            <div>
              <h3 class="n9-suc-title">تم إنشاء الطلبية بنجاح!</h3>
              <p  class="n9-suc-sub"  id="n9-suc-hub"></p>
            </div>
            <button class="n9-close-btn" id="n9-suc-close" aria-label="إغلاق">×</button>
          </div>
          <div class="n9-suc-track-block">
            <div class="n9-suc-track-row">
              <span class="n9-suc-track-lbl">رقم التتبع</span>
              <span class="n9-suc-track-val" id="n9-suc-tracking">—</span>
              <button class="n9-copy-btn" id="n9-suc-copy-track" title="نسخ رقم التتبع">📋</button>
            </div>
            <div class="n9-suc-track-row">
              <span class="n9-suc-track-lbl">المرجع</span>
              <span class="n9-suc-track-val" id="n9-suc-ref">—</span>
              <button class="n9-copy-btn" id="n9-suc-copy-ref" title="نسخ المرجع">📋</button>
            </div>
            <div class="n9-suc-track-row" id="n9-suc-hub-row" style="display:none">
              <span class="n9-suc-track-lbl">المركز الجهوي</span>
              <span class="n9-suc-track-val" id="n9-suc-hub-val">—</span>
            </div>
            <div class="n9-suc-track-row" id="n9-suc-rank-row" style="display:none">
              <span class="n9-suc-track-lbl">رتبة الولاية</span>
              <span class="n9-suc-track-val" id="n9-suc-rank-val">—</span>
            </div>
            <div class="n9-suc-track-row">
              <span class="n9-suc-track-lbl">تاريخ الإنشاء</span>
              <span class="n9-suc-track-val" id="n9-suc-date">—</span>
            </div>
          </div>
          <div class="n9-suc-actions">
            <button class="n9-act-btn n9-act-btn--label" id="n9-suc-label">
              <span class="n9-btn-lbl">📥 تحميل البوليصة</span>
              <span class="n9-spin" style="display:none" aria-label="جاري التحميل"></span>
            </button>
            <button class="n9-act-btn n9-act-btn--edit" id="n9-suc-edit-btn">✏️ تعديل</button>
            <button class="n9-act-btn n9-act-btn--del"   id="n9-suc-del-btn">🗑️ حذف الطلبية</button>
            <button class="n9-act-btn n9-act-btn--new"   id="n9-suc-new-btn">➕ طلبية جديدة</button>
          </div>
        </div>`;
      document.body.appendChild(el);

      document.getElementById('n9-suc-close').addEventListener('click', close);
      el.addEventListener('click', e => { if (e.target === el) close(); });
      el.addEventListener('keydown', e => { if (e.key === 'Escape') close(); });

      document.getElementById('n9-suc-copy-track').addEventListener('click', () => _copyTracking());
      document.getElementById('n9-suc-copy-ref').addEventListener('click',   () => {
        Utils.copyToClipboard(_currentRef).then(() => Notify.success('تم النسخ', _currentRef));
      });

      document.getElementById('n9-suc-label').addEventListener('click', () => {
        const btn = document.getElementById('n9-suc-label');
        const lbl = btn.querySelector('.n9-btn-lbl');
        const sp  = btn.querySelector('.n9-spin');
        LabelService.download(
          _currentTracking,
          () => { btn.disabled = true; lbl.style.display = 'none'; sp.style.display = 'inline-block'; },
          (m) => {
            btn.disabled = false; lbl.style.display = 'inline'; sp.style.display = 'none';
            m === 'download' ? Notify.success('تم التحميل', 'تم حفظ البوليصة') : Notify.info('تم الفتح', 'تم فتح البوليصة');
          },
        );
      });

      document.getElementById('n9-suc-edit-btn').addEventListener('click', () => {
        const tracking = _currentTracking;
        close();
        ModalManager.open('edit');
        document.getElementById('e-tracking').value = tracking;
        if (typeof window.loadEditOrder === 'function') window.loadEditOrder(tracking);
      });

      document.getElementById('n9-suc-del-btn').addEventListener('click', async () => {
        const confirmed = await ConfirmDialog.show({
          icon: '🗑️', title: 'حذف الطلبية',
          message: 'هل أنت متأكد من حذف هذه الطلبية؟ لا يمكن التراجع.',
          confirmLabel: '🗑️ حذف', confirmClass: 'n9-btn--delete',
          details: [{ label: 'رقم التتبع', value: _currentTracking }],
        });
        if (!confirmed) return;
        const btn = document.getElementById('n9-suc-del-btn');
        btn.disabled = true; btn.textContent = '⏳ جاري الحذف...';
        try {
          const { data } = await ApiService.deleteOrder(_currentTracking);
          if (data.success) { Notify.success('تم الحذف', _currentTracking); close(); }
          else {
            Notify.error('فشل الحذف', 'تأكد أن الطلبية لم تُعتمد بعد');
            btn.disabled = false; btn.textContent = '🗑️ حذف الطلبية';
          }
        } catch (e) {
          ErrorManager.log('SuccessDialog.delete', e);
          Notify.error('خطأ', e.message);
          btn.disabled = false; btn.textContent = '🗑️ حذف الطلبية';
        }
      });

      document.getElementById('n9-suc-new-btn').addEventListener('click', () => {
        close(); ModalManager.open('create');
      });

      _el = el;
    }

    function _copyTracking() {
      Utils.copyToClipboard(_currentTracking).then(() => Notify.success('تم النسخ', _currentTracking));
    }

    function show(result, reference) {
      if (!_el) _build();
      _currentTracking = result.tracking;
      _currentRef      = reference;

      document.getElementById('n9-suc-tracking').textContent = result.tracking;
      document.getElementById('n9-suc-ref').textContent      = reference;
      document.getElementById('n9-suc-date').textContent     = new Date().toLocaleString('ar-DZ');

      const hubRow  = document.getElementById('n9-suc-hub-row');
      const rankRow = document.getElementById('n9-suc-rank-row');
      const hubSub  = document.getElementById('n9-suc-hub');

      if (result.regional_hub_name) {
        hubRow.style.display = 'flex';
        document.getElementById('n9-suc-hub-val').textContent = result.regional_hub_name;
        hubSub.textContent = `المركز الجهوي: ${result.regional_hub_name}`;
      } else { hubRow.style.display = 'none'; hubSub.textContent = ''; }

      if (result.wilaya_rank) {
        rankRow.style.display = 'flex';
        document.getElementById('n9-suc-rank-val').textContent = result.wilaya_rank;
      } else { rankRow.style.display = 'none'; }

      const lblBtn = document.getElementById('n9-suc-label');
      lblBtn.disabled = false;
      lblBtn.querySelector('.n9-btn-lbl').style.display = 'inline';
      lblBtn.querySelector('.n9-spin').style.display = 'none';
      const delBtn = document.getElementById('n9-suc-del-btn');
      delBtn.disabled = false; delBtn.textContent = '🗑️ حذف الطلبية';

      _el.style.display = 'flex';
      requestAnimationFrame(() => _el.classList.add('in'));
      setTimeout(() => document.getElementById('n9-suc-label').focus(), 320);

      if (Config.autoCopyTracking) {
        Utils.copyToClipboard(result.tracking).then(() => Notify.info('تم النسخ تلقائياً', result.tracking));
      }
    }

    function close() {
      if (!_el) return;
      _el.classList.remove('in');
      setTimeout(() => { if (_el) _el.style.display = 'none'; }, 280);
    }

    return { show, close };
  })();

  /* ══════════════════════════════════════════════════════════
   *  MODULE 18 ▸ STYLES
   * ══════════════════════════════════════════════════════════ */
  GM_addStyle(`
    @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap');

    :root {
      --n9-blue:        #1d4ed8;
      --n9-blue-d:      #1e3a8a;
      --n9-blue-l:      #eff6ff;
      --n9-blue-ring:   rgba(59,130,246,.18);
      --n9-green:       #16a34a;
      --n9-green-l:     #f0fdf4;
      --n9-red:         #dc2626;
      --n9-red-l:       #fff1f2;
      --n9-violet:      #7c3aed;
      --n9-violet-d:    #5b21b6;
      --n9-violet-l:    #f5f3ff;
      --n9-violet-ring: rgba(124,58,237,.18);
      --n9-amber:       #d97706;
      --n9-teal:        #0891b2;
      --n9-g50:         #f8fafc;
      --n9-g100:        #f1f5f9;
      --n9-g200:        #e2e8f0;
      --n9-g300:        #cbd5e1;
      --n9-g400:        #94a3b8;
      --n9-g500:        #64748b;
      --n9-g700:        #334155;
      --n9-g900:        #0f172a;
      --n9-r:           10px;
      --n9-rlg:         16px;
      --n9-shadow:      0 24px 64px rgba(0,0,0,.22), 0 4px 16px rgba(0,0,0,.08);
      --n9-font:        'Cairo', system-ui, sans-serif;
    }

    /* ── FAB ── */
    #n9-fab {
      position:fixed; right:18px; top:68px; z-index:2147483640;
      display:flex; align-items:center; gap:8px;
      background:linear-gradient(135deg,#1e40af,#2563eb 60%,#3b82f6);
      color:#fff; border:none; border-radius:50px; padding:8px 22px;
      cursor:pointer; font-weight:800; font-family:var(--n9-font); font-size:13.5px;
      box-shadow:0 4px 22px rgba(37,99,235,.5);
      transition:transform .2s, box-shadow .2s; white-space:nowrap;
    }
    #n9-fab:hover  { transform:translateY(-3px); box-shadow:0 8px 30px rgba(37,99,235,.6); }
    #n9-fab:active { transform:scale(.97); }

    /* ── Overlay ── */
    #n9-overlay {
      position:fixed; inset:0; background:rgba(15,23,42,.7);
      z-index:2147483641; opacity:0; pointer-events:none;
      backdrop-filter:blur(4px); transition:opacity .28s ease;
    }
    #n9-overlay.in { opacity:1; pointer-events:auto; }

    /* ── Modal ── */
    #n9-modal {
      position:fixed; top:50%; left:50%;
      transform:translate(-50%,-48%) scale(.95);
      width:760px; max-width:97vw; max-height:94vh;
      display:flex; flex-direction:column;
      background:#fff; border-radius:var(--n9-rlg);
      z-index:2147483642; opacity:0; pointer-events:none;
      transition:opacity .3s cubic-bezier(.4,0,.2,1), transform .3s cubic-bezier(.34,1.56,.64,1);
      font-family:var(--n9-font); direction:rtl;
      box-shadow:var(--n9-shadow); overflow:hidden;
    }
    #n9-modal.open { opacity:1; pointer-events:auto; transform:translate(-50%,-50%) scale(1); }

    /* ── Header ── */
    .n9-header {
      display:flex; align-items:center; justify-content:space-between;
      padding:0 24px; flex-shrink:0; height:58px;
      background:linear-gradient(135deg,#0f2460 0%,#1d4ed8 100%);
    }
    .n9-header-brand { display:flex; align-items:center; gap:10px; font-size:15px; font-weight:800; color:#fff; letter-spacing:.3px; }
    .n9-close-btn {
      background:rgba(255,255,255,.13); border:1px solid rgba(255,255,255,.2);
      color:rgba(255,255,255,.85); border-radius:8px;
      width:32px; height:32px; cursor:pointer; font-size:19px;
      display:flex; align-items:center; justify-content:center;
      transition:background .15s; flex-shrink:0;
    }
    .n9-close-btn:hover { background:rgba(255,255,255,.25); color:#fff; }

    /* ── Tabs ── */
    .n9-tabs {
      display:flex; gap:0; border-bottom:2px solid var(--n9-g200);
      background:var(--n9-g50); flex-shrink:0; padding:0 12px;
      overflow-x:auto; scrollbar-width:none;
    }
    .n9-tabs::-webkit-scrollbar { display:none; }
    .n9-tab {
      display:flex; align-items:center; gap:6px;
      padding:12px 18px; cursor:pointer; border:none;
      background:none; font-family:var(--n9-font); font-size:12.5px;
      font-weight:700; color:var(--n9-g500);
      border-bottom:2.5px solid transparent; margin-bottom:-2px;
      transition:color .15s, border-color .15s; white-space:nowrap; flex-shrink:0;
    }
    .n9-tab:hover { color:var(--n9-blue); }
    .n9-tab.active { color:var(--n9-blue); border-bottom-color:var(--n9-blue); }
    .n9-tab.active[data-tab="create"]   { color:var(--n9-blue);   border-bottom-color:var(--n9-blue); }
    .n9-tab.active[data-tab="validate"] { color:var(--n9-green);  border-bottom-color:var(--n9-green); }
    .n9-tab.active[data-tab="edit"]     { color:var(--n9-violet); border-bottom-color:var(--n9-violet); }
    .n9-tab.active[data-tab="delete"]   { color:var(--n9-red);    border-bottom-color:var(--n9-red); }
    .n9-tab.active[data-tab="pdf"]      { color:var(--n9-teal);   border-bottom-color:var(--n9-teal); }
    .n9-tab.active[data-tab="settings"] { color:var(--n9-amber);  border-bottom-color:var(--n9-amber); }

    /* ── Panes ── */
    .n9-pane {
      flex:1; overflow-y:auto; padding:20px 24px;
      display:none; flex-direction:column; gap:14px;
      scrollbar-width:thin; scrollbar-color:var(--n9-g300) transparent;
    }
    .n9-pane::-webkit-scrollbar { width:5px; }
    .n9-pane::-webkit-scrollbar-thumb { background:var(--n9-g300); border-radius:4px; }

    /* ── Footer ── */
    .n9-footer {
      display:flex; gap:10px; justify-content:flex-end; align-items:center;
      padding:14px 24px; border-top:1.5px solid var(--n9-g100);
      background:var(--n9-g50); flex-shrink:0; min-height:62px;
    }

    /* ── Section ── */
    .n9-section {
      background:var(--n9-g50); border:1.5px solid var(--n9-g200);
      border-radius:var(--n9-r); padding:14px 16px;
    }
    .n9-section-title {
      font-size:11px; font-weight:800; color:var(--n9-blue);
      text-transform:uppercase; letter-spacing:.8px;
      margin-bottom:12px; display:flex; align-items:center; gap:6px;
    }
    .n9-section-title.violet { color:var(--n9-violet); }
    .n9-section-title.red    { color:var(--n9-red); }
    .n9-section-title.green  { color:var(--n9-green); }
    .n9-section-title.amber  { color:var(--n9-amber); }
    .n9-section-title.teal   { color:var(--n9-teal); }

    /* ── Grid ── */
    .n9-grid   { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
    .n9-grid-3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px; }
    .n9-full   { grid-column:1 / -1; }
    @media(max-width:600px){ .n9-grid,.n9-grid-3{ grid-template-columns:1fr; } }

    /* ── Fields ── */
    .n9-field  { display:flex; flex-direction:column; gap:4px; }
    .n9-label  { font-size:11.5px; font-weight:600; color:var(--n9-g500); }
    .n9-req    { color:#ef4444; margin-right:2px; }
    .n9-input, .n9-select {
      width:100%; padding:9px 11px;
      border:1.5px solid var(--n9-g200); border-radius:8px;
      box-sizing:border-box; font-family:var(--n9-font);
      font-size:13px; color:var(--n9-g900); background:#fff;
      outline:none; transition:border-color .18s, box-shadow .18s;
    }
    .n9-input:focus, .n9-select:focus {
      border-color:var(--n9-blue); box-shadow:0 0 0 3px var(--n9-blue-ring);
    }
    .n9-input[readonly] { background:var(--n9-g100); color:var(--n9-g500); cursor:default; }
    .n9-input:disabled, .n9-select:disabled { background:var(--n9-g100); color:var(--n9-g400); cursor:not-allowed; }
    textarea.n9-input { resize:vertical; }
    .n9-field--err .n9-input,
    .n9-field--err .n9-select { border-color:var(--n9-red); box-shadow:0 0 0 3px rgba(220,38,38,.1); }
    .n9-err { font-size:11px; color:var(--n9-red); font-weight:600; }

    /* ── Buttons ── */
    .n9-btn {
      display:inline-flex; align-items:center; justify-content:center; gap:6px;
      border:none; padding:10px 22px; border-radius:9px;
      cursor:pointer; font-family:var(--n9-font); font-size:13.5px; font-weight:700;
      transition:opacity .15s, transform .12s;
    }
    .n9-btn:not(:disabled):hover  { opacity:.9; transform:translateY(-1px); }
    .n9-btn:not(:disabled):active { transform:scale(.97); }
    .n9-btn:disabled { opacity:.5; cursor:not-allowed; }
    .n9-btn--ghost          { background:var(--n9-g100); color:var(--n9-g500); border:1.5px solid var(--n9-g200); }
    .n9-btn--create         { background:linear-gradient(135deg,#16a34a,#15803d); color:#fff; box-shadow:0 3px 12px rgba(22,163,74,.35); min-width:155px; }
    .n9-btn--edit           { background:linear-gradient(135deg,#7c3aed,#5b21b6); color:#fff; box-shadow:0 3px 12px rgba(124,58,237,.35); min-width:155px; }
    .n9-btn--delete         { background:linear-gradient(135deg,#dc2626,#b91c1c); color:#fff; box-shadow:0 3px 12px rgba(220,38,38,.35); min-width:155px; }
    .n9-btn--validate       { background:linear-gradient(135deg,#0891b2,#0e7490); color:#fff; box-shadow:0 3px 12px rgba(8,145,178,.35); min-width:155px; }
    .n9-btn--save           { background:linear-gradient(135deg,#d97706,#b45309); color:#fff; box-shadow:0 3px 12px rgba(217,119,6,.35); min-width:155px; }
    .n9-btn--outline-red    { background:#fff; color:var(--n9-red);    border:1.5px solid #fca5a5; }
    .n9-btn--outline-violet { background:#fff; color:var(--n9-violet); border:1.5px solid #c4b5fd; }
    .n9-btn--outline-teal   { background:#fff; color:var(--n9-teal);   border:1.5px solid #a5f3fc; }
    .n9-spin {
      width:14px; height:14px;
      border:2px solid rgba(255,255,255,.3); border-top-color:#fff;
      border-radius:50%; animation:n9-spin .55s linear infinite;
    }

    /* ── Final amount ── */
    .n9-final-row { display:flex; align-items:center; gap:8px; }
    .n9-final-row .n9-input { flex:1; }
    .n9-final-badge {
      background:linear-gradient(135deg,#1d4ed8,#3b82f6); color:#fff;
      padding:9px 12px; border-radius:8px; font-size:12.5px; font-weight:800;
      white-space:nowrap; flex-shrink:0; min-width:90px; text-align:center;
    }

    /* ── Searchable Dropdown ── */
    .n9-sw-wrap { display:flex; flex-direction:column; gap:0; }
    .n9-sw { position:relative; }
    .n9-sw-icon { position:absolute; right:9px; top:50%; transform:translateY(-50%); font-size:12px; pointer-events:none; opacity:.45; }
    .n9-sw .n9-input { padding-right:30px; }
    .n9-drop {
      position:absolute; top:calc(100% + 4px); right:0; left:0;
      background:#fff; border:1.5px solid #bfdbfe; border-radius:10px;
      box-shadow:0 10px 30px rgba(0,0,0,.13);
      z-index:2147483645; max-height:210px; overflow-y:auto;
      display:none; scrollbar-width:thin;
    }
    .n9-drop.open  { display:block; }
    .n9-drop-item  {
      padding:9px 13px; font-size:12.5px; cursor:pointer;
      border-bottom:1px solid var(--n9-g100);
      color:var(--n9-g900); display:flex; align-items:center; gap:8px;
      transition:background .1s;
    }
    .n9-drop-item:last-child  { border-bottom:none; }
    .n9-drop-item:hover       { background:var(--n9-blue-l); color:var(--n9-blue-d); }
    .n9-drop-item mark        { background:#fef08a; color:inherit; border-radius:2px; padding:0 1px; }
    .n9-drop-empty            { padding:14px; text-align:center; color:var(--n9-g400); font-size:12px; }
    .n9-pill {
      align-items:center; justify-content:space-between;
      padding:8px 12px; border:1.5px solid #93c5fd; border-radius:8px;
      background:var(--n9-blue-l); font-size:12.5px;
      color:var(--n9-blue-d); font-weight:700; gap:6px; margin-top:2px;
    }
    .n9-pill-x {
      background:none; border:none; color:#93c5fd;
      font-size:18px; line-height:1; cursor:pointer; padding:0;
      transition:color .15s;
    }
    .n9-pill-x:hover  { color:var(--n9-red); }
    .n9-badge {
      background:#dbeafe; color:var(--n9-blue-d);
      font-size:10px; font-weight:800; padding:2px 7px;
      border-radius:4px; flex-shrink:0; min-width:28px; text-align:center;
    }

    /* ── Toggle switch ── */
    .n9-toggle-row {
      display:flex; align-items:center; justify-content:space-between;
      padding:9px 12px; background:#fff; border:1.5px solid var(--n9-g200);
      border-radius:8px; cursor:pointer; user-select:none;
    }
    .n9-toggle-label { font-size:13px; color:var(--n9-g700); font-weight:600; flex:1; }
    .n9-toggle-desc  { font-size:11px; color:var(--n9-g400); margin-top:1px; }
    .n9-toggle       { position:relative; width:40px; height:22px; flex-shrink:0; }
    .n9-toggle input { opacity:0; width:0; height:0; }
    .n9-toggle-track {
      position:absolute; inset:0;
      background:var(--n9-g300); border-radius:11px; transition:background .2s;
    }
    .n9-toggle input:checked ~ .n9-toggle-track { background:var(--n9-blue); }
    .n9-toggle-thumb {
      position:absolute; top:3px; right:3px;
      width:16px; height:16px; background:#fff; border-radius:50%;
      transition:transform .2s; box-shadow:0 1px 4px rgba(0,0,0,.2);
    }
    .n9-toggle input:checked ~ .n9-toggle-thumb { transform:translateX(-18px); }

    /* ── Desk card ── */
    .n9-desk-card {
      background:#f0f9ff; border:1.5px solid #bae6fd;
      border-radius:9px; padding:10px 14px;
      font-size:12.5px; color:#0369a1; line-height:1.9; margin-top:6px;
    }

    /* ── Paste / input row ── */
    .n9-input-row { display:flex; align-items:center; gap:6px; }
    .n9-input-row .n9-input { flex:1; font-family:monospace; letter-spacing:.4px; }
    .n9-paste-btn {
      background:var(--n9-g100); border:1.5px solid var(--n9-g200);
      border-radius:8px; padding:9px 11px; cursor:pointer; font-size:15px;
      transition:background .15s; flex-shrink:0;
    }
    .n9-paste-btn:hover { background:var(--n9-g200); }

    /* ── Input with action ── */
    .n9-input-action { display:flex; gap:6px; align-items:center; }
    .n9-input-action .n9-input { flex:1; }
    .n9-field-hint { font-size:11px; color:var(--n9-g400); margin-top:3px; }

    /* ── Password / reveal ── */
    .n9-pw-wrap { position:relative; }
    .n9-pw-wrap .n9-input { padding-left:36px; }
    .n9-pw-eye {
      position:absolute; left:9px; top:50%; transform:translateY(-50%);
      background:none; border:none; cursor:pointer; font-size:14px;
      color:var(--n9-g400); padding:2px;
    }
    .n9-pw-eye:hover { color:var(--n9-blue); }

    /* ── Step bar ── */
    .n9-steps   { display:flex; gap:0; align-items:center; margin-bottom:4px; }
    .n9-step    {
      display:flex; align-items:center; gap:6px;
      font-size:11.5px; font-weight:700; color:var(--n9-g400);
      padding:6px 12px; border-radius:6px; transition:color .2s, background .2s;
    }
    .n9-step.active { color:var(--n9-violet); background:var(--n9-violet-l); }
    .n9-step.done   { color:var(--n9-green); }
    .n9-step-num {
      width:20px; height:20px; border-radius:50%;
      background:var(--n9-g200); color:var(--n9-g500);
      display:flex; align-items:center; justify-content:center;
      font-size:10px; font-weight:800; flex-shrink:0;
      transition:background .2s, color .2s;
    }
    .n9-step.active .n9-step-num { background:var(--n9-violet); color:#fff; }
    .n9-step.done  .n9-step-num  { background:var(--n9-green);  color:#fff; }
    .n9-step-sep   { color:var(--n9-g300); font-size:14px; margin:0 2px; }

    /* ── Order summary card ── */
    .n9-order-card {
      background:linear-gradient(135deg,#f5f3ff,#ede9fe);
      border:1.5px solid #c4b5fd; border-radius:var(--n9-r);
      padding:14px 16px; animation:n9-slidein .25s ease;
    }
    .n9-order-card-header     { display:flex; align-items:center; justify-content:space-between; margin-bottom:10px; }
    .n9-order-card-title      { font-size:13px; font-weight:800; color:var(--n9-violet-d); display:flex; align-items:center; gap:7px; }
    .n9-order-card-track      { font-size:11.5px; font-family:monospace; background:rgba(255,255,255,.6); padding:3px 9px; border-radius:5px; color:var(--n9-violet-d); font-weight:700; }
    .n9-order-grid            { display:grid; grid-template-columns:1fr 1fr 1fr; gap:8px; }
    @media(max-width:600px)   { .n9-order-grid{ grid-template-columns:1fr 1fr; } }
    .n9-order-item            { background:rgba(255,255,255,.55); border-radius:7px; padding:7px 10px; }
    .n9-order-item-label      { font-size:10px; font-weight:700; color:var(--n9-g500); text-transform:uppercase; letter-spacing:.5px; margin-bottom:2px; }
    .n9-order-item-value      { font-size:12.5px; font-weight:700; color:var(--n9-g900); }

    /* ── Skeleton ── */
    .n9-skeleton {
      background:linear-gradient(90deg, var(--n9-g100) 25%, var(--n9-g200) 50%, var(--n9-g100) 75%);
      background-size:200% 100%; animation:n9-shimmer 1.4s infinite; border-radius:6px;
    }
    .n9-skeleton-row   { height:16px; margin-bottom:8px; }
    .n9-skeleton-card  { border:1.5px solid var(--n9-g200); border-radius:var(--n9-r); padding:16px; display:flex; flex-direction:column; gap:10px; }
    @keyframes n9-shimmer { to { background-position:-200% 0; } }

    /* ── Mode picker ── */
    .n9-mode-picker { display:flex; gap:8px; flex-wrap:wrap; }
    .n9-mode-btn {
      flex:1; min-width:140px; padding:10px 14px;
      border-radius:9px; border:2px solid var(--n9-g200);
      background:#fff; cursor:pointer;
      font-family:var(--n9-font); font-size:12.5px; font-weight:700;
      color:var(--n9-g500); text-align:center; transition:all .18s;
    }
    .n9-mode-btn small { display:block; font-size:10.5px; font-weight:500; opacity:.7; margin-top:2px; }
    .n9-mode-btn.active { border-color:var(--n9-violet); color:var(--n9-violet); background:var(--n9-violet-l); box-shadow:0 2px 8px var(--n9-violet-ring); }
    .n9-mode-btn:not(.active):hover { border-color:var(--n9-violet); color:var(--n9-violet); }

    /* ── Diff table ── */
    .n9-diff-table     { width:100%; border-collapse:collapse; font-size:12.5px; }
    .n9-diff-table th  { text-align:right; font-size:10.5px; font-weight:800; color:var(--n9-g500); text-transform:uppercase; letter-spacing:.6px; padding:6px 10px; background:var(--n9-g50); border-bottom:1.5px solid var(--n9-g200); }
    .n9-diff-table td  { padding:8px 10px; border-bottom:1px solid var(--n9-g100); vertical-align:middle; }
    .n9-diff-table tr:last-child td { border-bottom:none; }
    .n9-diff-field     { font-weight:700; color:var(--n9-g700); }
    .n9-diff-old       { color:var(--n9-g500); text-decoration:line-through; font-size:12px; }
    .n9-diff-arrow     { color:var(--n9-g400); padding:0 6px; }
    .n9-diff-new       { color:var(--n9-green); font-weight:700; }

    /* ── Error card ── */
    .n9-error-card     { background:var(--n9-red-l); border:1.5px solid #fca5a5; border-radius:12px; padding:18px 20px; animation:n9-slidein .3s ease; }
    .n9-error-icon     { font-size:24px; margin-bottom:7px; }
    .n9-error-title    { font-size:14px; font-weight:700; color:#9f1239; margin-bottom:8px; }
    .n9-detail-btn     { background:none; border:1px solid #fca5a5; color:#be123c; padding:4px 10px; border-radius:6px; cursor:pointer; font-family:var(--n9-font); font-size:12px; font-weight:600; }
    .n9-error-pre      { font-size:11px; white-space:pre-wrap; color:#7f1d1d; background:rgba(255,255,255,.5); padding:10px; border-radius:7px; max-height:150px; overflow-y:auto; margin:8px 0 0; }

    /* ── Loading ── */
    .n9-loading {
      display:flex; align-items:center; gap:10px; padding:16px;
      background:var(--n9-blue-l); border:1.5px solid #bfdbfe; border-radius:9px;
      font-size:13px; font-weight:600; color:var(--n9-blue-d);
    }
    .n9-loading .n9-spin { border-top-color:var(--n9-blue); }

    /* ── Info / Warn ── */
    .n9-info { padding:10px 14px; background:var(--n9-blue-l); border:1.5px solid #93c5fd; border-radius:8px; font-size:12px; color:var(--n9-blue-d); font-weight:600; line-height:1.7; }
    .n9-warn { padding:10px 14px; background:#fffbeb; border:1.5px solid #fcd34d; border-radius:8px; font-size:12px; color:#92400e; font-weight:600; line-height:1.7; }
    .n9-success-banner { padding:10px 14px; background:var(--n9-green-l); border:1.5px solid #86efac; border-radius:8px; font-size:12px; color:#166534; font-weight:600; }

    /* ── Result ── */
    .n9-result          { padding:14px 16px; border-radius:9px; font-size:13px; line-height:1.8; animation:n9-slidein .25s ease; }
    .n9-result.ok       { background:var(--n9-green-l); color:#166534; border:1.5px solid #86efac; }
    .n9-result.err      { background:var(--n9-red-l);   color:#9f1239;  border:1.5px solid #fca5a5; }

    /* ── Checkbox row ── */
    .n9-check-row {
      display:flex; align-items:center; gap:8px; font-size:13px; color:var(--n9-g700);
      cursor:pointer; user-select:none; padding:9px 12px;
      background:#fff; border:1.5px solid var(--n9-g200); border-radius:8px;
    }
    .n9-check-row input[type=checkbox] { width:16px; height:16px; accent-color:var(--n9-blue); cursor:pointer; }

    /* ══ SETTINGS PANE ══ */
    .n9-settings-group  { display:flex; flex-direction:column; gap:6px; }
    .n9-settings-actions { display:flex; gap:8px; flex-wrap:wrap; margin-top:6px; }
    .n9-settings-btn {
      display:inline-flex; align-items:center; gap:6px;
      padding:8px 14px; border-radius:8px; border:1.5px solid var(--n9-g200);
      background:#fff; cursor:pointer; font-family:var(--n9-font);
      font-size:12.5px; font-weight:600; color:var(--n9-g700);
      transition:all .15s;
    }
    .n9-settings-btn:hover { background:var(--n9-g100); border-color:var(--n9-g300); }
    .n9-settings-btn.danger { color:var(--n9-red); border-color:#fca5a5; }
    .n9-settings-btn.danger:hover { background:var(--n9-red-l); }
    .n9-settings-btn.primary { color:var(--n9-blue); border-color:#93c5fd; }
    .n9-settings-btn.primary:hover { background:var(--n9-blue-l); }
    .n9-settings-btn.success { color:var(--n9-green); border-color:#86efac; }
    .n9-settings-btn.success:hover { background:var(--n9-green-l); }

    .n9-connection-status {
      display:inline-flex; align-items:center; gap:6px;
      padding:6px 12px; border-radius:20px; font-size:12px; font-weight:700;
      background:var(--n9-g100); color:var(--n9-g500);
    }
    .n9-connection-status.ok  { background:var(--n9-green-l); color:#166534; }
    .n9-connection-status.err { background:var(--n9-red-l);   color:#9f1239; }
    .n9-connection-dot        { width:7px; height:7px; border-radius:50%; background:currentColor; }

    /* ══ CONFIRMATION DIALOG ══ */
    #n9-confirm-overlay {
      position:fixed; inset:0;
      background:rgba(15,23,42,.75); backdrop-filter:blur(6px);
      z-index:2147483650; display:none;
      align-items:center; justify-content:center;
      opacity:0; transition:opacity .25s ease;
    }

    #n9-confirm-overlay.in { opacity:1; }
    #n9-confirm-box {
      background:#fff; border-radius:18px; padding:28px 28px 22px;
      width:420px; max-width:94vw; direction:rtl;
      font-family:var(--n9-font);
      box-shadow:0 30px 80px rgba(0,0,0,.25), 0 8px 24px rgba(0,0,0,.1);
      transform:scale(.93); transition:transform .28s cubic-bezier(.34,1.56,.64,1);
    }
    #n9-confirm-overlay.in #n9-confirm-box { transform:scale(1); }
    .n9-confirm-icon  { font-size:32px; margin-bottom:12px; display:block; }
    .n9-confirm-title { font-size:17px; font-weight:800; color:var(--n9-g900); margin:0 0 8px; }
    .n9-confirm-msg   { font-size:13.5px; color:var(--n9-g500); margin:0 0 14px; line-height:1.65; }
    .n9-confirm-details     { background:var(--n9-g50); border:1.5px solid var(--n9-g200); border-radius:10px; padding:10px 14px; margin-bottom:18px; }
    .n9-confirm-detail-row  { display:flex; justify-content:space-between; align-items:center; padding:6px 0; border-bottom:1px solid var(--n9-g100); }
    .n9-confirm-detail-row:last-child { border-bottom:none; }
    .n9-confirm-detail-lbl  { font-size:12px; color:var(--n9-g500); font-weight:600; }
    .n9-confirm-detail-val  { font-size:13px; font-weight:700; color:var(--n9-g900); }
    .n9-confirm-actions { display:flex; gap:10px; justify-content:flex-end; }
    .n9-confirm-actions .n9-btn { min-width:120px; }

    /* ══ SUCCESS DIALOG ══ */
    #n9-success-overlay {
      position:fixed; inset:0;
      background:rgba(15,23,42,.75); backdrop-filter:blur(6px);
      z-index:2147483645; display:none;
      align-items:center; justify-content:center;
      opacity:0; transition:opacity .25s ease;
    }
    #n9-success-overlay.in { opacity:1; }
    #n9-success-box {
      background:#fff; border-radius:20px; padding:28px;
      width:480px; max-width:95vw; direction:rtl;
      font-family:var(--n9-font);
      box-shadow:0 32px 80px rgba(0,0,0,.25), 0 8px 24px rgba(0,0,0,.1);
      transform:scale(.92) translateY(20px);
      transition:transform .32s cubic-bezier(.34,1.56,.64,1);
    }
    #n9-success-overlay.in #n9-success-box { transform:scale(1) translateY(0); }
    .n9-suc-header   { display:flex; align-items:flex-start; gap:14px; margin-bottom:18px; }
    .n9-suc-anim     { font-size:36px; line-height:1; animation:n9-pop .5s cubic-bezier(.34,1.56,.64,1); flex-shrink:0; }
    .n9-suc-title    { font-size:18px; font-weight:800; color:#166534; margin:0 0 4px; }
    .n9-suc-sub      { font-size:12.5px; color:#4b5563; margin:0; }
    .n9-suc-header .n9-close-btn { margin-right:auto; margin-left:0; background:var(--n9-g100); border:1.5px solid var(--n9-g200); color:var(--n9-g500); }
    .n9-suc-header .n9-close-btn:hover { background:var(--n9-g200); color:var(--n9-g900); }
    .n9-suc-track-block { background:linear-gradient(135deg,#f0fdf4,#dcfce7); border:1.5px solid #86efac; border-radius:12px; padding:12px 16px; margin-bottom:16px; }
    .n9-suc-track-row   { display:flex; align-items:center; gap:10px; padding:7px 0; border-bottom:1px solid rgba(0,0,0,.06); }
    .n9-suc-track-row:last-child { border-bottom:none; }
    .n9-suc-track-lbl   { font-size:11.5px; color:#4b5563; font-weight:600; min-width:85px; }
    .n9-suc-track-val   { font-size:14px; font-weight:800; color:#166534; flex:1; letter-spacing:.5px; }
    .n9-copy-btn        { background:none; border:none; cursor:pointer; font-size:15px; padding:2px 5px; border-radius:5px; transition:background .15s; }
    .n9-copy-btn:hover  { background:rgba(0,0,0,.06); }
    .n9-suc-actions { display:flex; flex-wrap:wrap; gap:8px; }
    .n9-act-btn {
      display:inline-flex; align-items:center; gap:6px;
      padding:8px 14px; border-radius:8px; border:none;
      cursor:pointer; font-family:var(--n9-font); font-size:13px; font-weight:700;
      transition:opacity .15s, transform .1s;
    }
    .n9-act-btn:hover  { opacity:.88; transform:translateY(-1px); }
    .n9-act-btn:active { transform:scale(.97); }
    .n9-act-btn:disabled { opacity:.5; cursor:not-allowed; transform:none; }
    .n9-act-btn--copy  { background:var(--n9-g100); color:var(--n9-g700); border:1.5px solid var(--n9-g200); }
    .n9-act-btn--label { background:linear-gradient(135deg,#0891b2,#0e7490); color:#fff; box-shadow:0 2px 8px rgba(8,145,178,.3); }
    .n9-act-btn--del   { background:#fee2e2; color:var(--n9-red);    border:1.5px solid #fca5a5; }
    .n9-act-btn--edit  { background:var(--n9-violet-l); color:var(--n9-violet); border:1.5px solid #c4b5fd; }
    .n9-act-btn--new   { background:linear-gradient(135deg,#16a34a,#15803d); color:#fff; box-shadow:0 2px 8px rgba(22,163,74,.3); }

    /* ══ TOASTS ══ */
    #n9-toasts {
      position:fixed; top:18px; left:50%; transform:translateX(-50%);
      z-index:2147483647; display:flex; flex-direction:column; gap:8px;
      pointer-events:none; min-width:300px;
    }
    .n9-toast {
      position:relative; overflow:hidden;
      display:flex; align-items:flex-start; gap:10px;
      padding:12px 16px 18px; border-radius:10px;
      font-family:var(--n9-font); font-size:13px; font-weight:600; color:#fff;
      box-shadow:0 4px 20px rgba(0,0,0,.18); pointer-events:auto;
      opacity:0; animation:n9-toast-in .3s cubic-bezier(.34,1.56,.64,1) forwards;
    }
    .n9-toast.in   { opacity:1; }
    .n9-toast.out  { animation:n9-toast-out .3s ease forwards; }
    .n9-toast--success { background:linear-gradient(135deg,#16a34a,#15803d); }
    .n9-toast--error   { background:linear-gradient(135deg,#dc2626,#b91c1c); }
    .n9-toast--warning { background:linear-gradient(135deg,#d97706,#b45309); }
    .n9-toast--info    { background:linear-gradient(135deg,#0284c7,#0369a1); }
    .n9-toast-icon  { font-size:16px; flex-shrink:0; }
    .n9-toast-body  { flex:1; display:flex; flex-direction:column; gap:2px; }
    .n9-toast-body strong { font-weight:700; }
    .n9-toast-body span   { font-size:11.5px; opacity:.88; }
    .n9-toast-close {
      background:rgba(255,255,255,.2); border:none; color:#fff;
      border-radius:5px; width:22px; height:22px; cursor:pointer;
      font-size:14px; display:flex; align-items:center; justify-content:center;
      flex-shrink:0;
    }
    .n9-toast-close:hover { background:rgba(255,255,255,.35); }
    .n9-toast-progress { position:absolute; bottom:0; left:0; right:0; height:3px; background:rgba(255,255,255,.2); }
    .n9-toast-progress-bar {
      height:100%; width:100%; background:rgba(255,255,255,.5);
      transform-origin:left; animation:n9-progress linear forwards;
    }

    @keyframes n9-slidein  { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:translateY(0)} }
    @keyframes n9-spin     { to{transform:rotate(360deg)} }
    @keyframes n9-pop      { 0%{transform:scale(0)} 60%{transform:scale(1.2)} 100%{transform:scale(1)} }
    @keyframes n9-toast-in { from{opacity:0;transform:translateY(-10px) scale(.95)} to{opacity:1;transform:translateY(0) scale(1)} }
    @keyframes n9-toast-out{ from{opacity:1} to{opacity:0;transform:translateY(-6px)} }
    @keyframes n9-progress { from{transform:scaleX(1)} to{transform:scaleX(0)} }
    @keyframes n9-shimmer  { to{background-position:-200% 0} }

    /* ══ PDF CENTER TAB — v10.1 ══ */
    #pdf-batch-counter { font-size:11px; color:var(--n9-g400); margin-top:3px; }
    .pdf-history-item {
      display:flex; align-items:center; gap:10px;
      padding:10px 12px; background:#fff;
      border:1.5px solid var(--n9-g200); border-radius:8px;
      font-size:13px; color:var(--n9-g700);
      transition:border-color .15s;
    }
    .pdf-history-item:hover { border-color:var(--n9-teal); }
    .pdf-history-redownload {
      background:var(--n9-g100); border:1.5px solid var(--n9-g200);
      border-radius:6px; padding:4px 8px; cursor:pointer;
      font-size:14px; flex-shrink:0; transition:background .15s;
    }
    .pdf-history-redownload:hover { background:var(--n9-teal); color:#fff; }
    .pdf-progress-wrap {
      display:flex; align-items:center; gap:10px;
      padding:10px 14px; background:var(--n9-g50);
      border:1.5px solid var(--n9-g200); border-radius:8px;
    }
    .pdf-batch-progress-bar {
      height:8px; background:linear-gradient(90deg,var(--n9-teal),var(--n9-blue));
      border-radius:4px; transition:width .3s ease; flex-shrink:0;
      min-width:4px;
    }
  `);

  /* ══════════════════════════════════════════════════════════
   *  MODULE 19 ▸ HTML BUILDER HELPERS
   * ══════════════════════════════════════════════════════════ */
  function field(id, label, required, inputHtml, fullWidth = false) {
    return `<div class="n9-field${fullWidth ? ' n9-full' : ''}" id="f-${id}">
      <label class="n9-label" for="${id}">${label}${required ? ' <span class="n9-req" aria-label="مطلوب">*</span>' : ''}</label>
      ${inputHtml}
    </div>`;
  }
  function inp(id, placeholder, extra = '') {
    return `<input id="${id}" class="n9-input" placeholder="${placeholder}" ${extra}>`;
  }
  function sel(id, options) {
    return `<select id="${id}" class="n9-select">${options}</select>`;
  }
  function swWrap(searchId, dropId, pillId, pillTxtId, clearId, hiddenId) {
    return `<div class="n9-sw-wrap">
      <div class="n9-sw">
        <span class="n9-sw-icon" aria-hidden="true">🔍</span>
        <input id="${searchId}" class="n9-input" autocomplete="off" spellcheck="false">
        <div class="n9-drop" id="${dropId}" role="listbox"></div>
      </div>
      <div class="n9-pill" id="${pillId}" style="display:none">
        <span id="${pillTxtId}"></span>
        <button class="n9-pill-x" id="${clearId}" aria-label="مسح الاختيار">×</button>
      </div>
    </div>
    <input type="hidden" id="${hiddenId}">`;
  }

  /* ══════════════════════════════════════════════════════════
   *  MODULE 19a ▸ SHARED DATA STATE
   * ══════════════════════════════════════════════════════════ */
  function getDesksForWilaya(wilayaId) {
    return Object.values(StateManager.get('desks')).filter(d => parseInt(d.code, 10) === Number(wilayaId));
  }
  function getDesk(code) {
    const desks = StateManager.get('desks');
    return desks[code] || Object.values(desks).find(d => d.code === code);
  }
  // [v10.2 QUALITY] moved from bootstrap for reuse
  function findOrderDesk(o) {
    if (!o.wilaya_id) return null;
    const desks = getDesksForWilaya(o.wilaya_id);
    if (o.station_code) {
      const byCode = desks.find(d => String(d.code) === String(o.station_code));
      if (byCode) return byCode;
    }
    const commune = (o.commune || '').trim();
    if (!commune) return null;
    const byName = desks.find(d => d.name && d.name.trim() === commune);
    if (byName) return byName;
    const byCommune = desks.find(d => d.commune && d.commune.trim() === commune);
    if (byCommune) return byCommune;
    return desks.find(d => d.name && d.name.includes(commune));
  }

  /* ══════════════════════════════════════════════════════════
   *  MODULE 19b ▸ SHARED SECTION BUILDERS
   * ══════════════════════════════════════════════════════════ */
  function buildProductSection(prefix) {
    return `
      <div class="n9-section">
        <div class="n9-section-title">📦 المنتج</div>
        <div class="n9-grid">
          ${field(`${prefix}-productSelect`, 'المنتج', true, `<select id="${prefix}-productSelect" class="n9-select"><option value="">-- اختر --</option></select>`, true)}
          <div class="n9-field n9-full" id="${prefix}-descWrap" style="display:none">
            <textarea id="${prefix}-productDesc" class="n9-input" rows="2" readonly aria-label="وصف المنتج"></textarea>
          </div>
          <input type="hidden" id="${prefix}-product">
        </div>
      </div>`;
  }

  function buildAmountSection(prefix, showDiscount) {
    return `
      <div class="n9-section">
        <div class="n9-section-title">💰 المبالغ</div>
        <div class="n9-grid-3">
          ${field(`${prefix}-amount`, 'السعر (DA)', true, inp(`${prefix}-amount`, '0', 'type="number" min="0" value="0"'))}
          ${showDiscount ? field(`${prefix}-discount`, 'التخفيض (DA)', false, inp(`${prefix}-discount`, '0', 'type="number" min="0" value="0"')) : ''}
          <div class="n9-field" id="f-${prefix}-final">
            <label class="n9-label">المبلغ النهائي</label>
            <div class="n9-final-row">
              <input id="${prefix}-finalAmount" class="n9-input" readonly placeholder="0" aria-label="المبلغ النهائي">
              <div class="n9-final-badge" id="${prefix}-finalBadge" aria-live="polite">0 DA</div>
            </div>
          </div>
        </div>
      </div>`;
  }

  function buildCustomerSection(prefix) {
    return `
      <div class="n9-section">
        <div class="n9-section-title">👤 بيانات الزبون</div>
        <div class="n9-grid-3">
          ${field(`${prefix}-client`,  'الاسم الكامل', true,  inp(`${prefix}-client`,  'أدخل اسم الزبون'))}
          ${field(`${prefix}-phone`,   'الهاتف',       true,  inp(`${prefix}-phone`,   '05xxxxxxxx', 'maxlength="10" inputmode="numeric"'))}
          ${field(`${prefix}-phone2`,  'هاتف إضافي',   false, inp(`${prefix}-phone2`,  '07xxxxxxxx', 'maxlength="10" inputmode="numeric"'))}
        </div>
        ${field(`${prefix}-adresse`, 'العنوان', true, inp(`${prefix}-adresse`, 'الشارع، الحي...'), true)}
      </div>`;
  }

  function buildWilayaSection(prefix) {
    return `
      <div class="n9-section">
        <div class="n9-section-title">📍 الولاية ومكتب التوصيل</div>
        <div class="n9-grid">
          ${field(`${prefix}-wilaya`, 'الولاية', true, swWrap(`${prefix}-wSearch`, `${prefix}-wDrop`, `${prefix}-wPill`, `${prefix}-wPillTxt`, `${prefix}-wClear`, `${prefix}-wilayaId`))}
          ${field(`${prefix}-desk`,   'مكتب التوصيل', true, swWrap(`${prefix}-dSearch`, `${prefix}-dDrop`, `${prefix}-dPill`, `${prefix}-dPillTxt`, `${prefix}-dClear`, `${prefix}-stationCode`))}
          <div class="n9-full" id="${prefix}-deskCard" style="display:none">
            <div class="n9-desk-card" id="${prefix}-deskCardInner"></div>
          </div>
          <input type="hidden" id="${prefix}-deskCommune">
        </div>
      </div>`;
  }

  function initProductSection(prefix) {
    const selProd = document.getElementById(`${prefix}-productSelect`);
    Products.forEach((p, i) => {
      const o = document.createElement('option');
      o.value = i;
      o.textContent = `${p.name} — ${p.price.toLocaleString('fr-DZ')} DA`;
      selProd.appendChild(o);
    });
    const amountEl   = document.getElementById(`${prefix}-amount`);
    const discountEl = document.getElementById(`${prefix}-discount`);
    const finalEl    = document.getElementById(`${prefix}-finalAmount`);
    const badgeEl    = document.getElementById(`${prefix}-finalBadge`);

    function updateFinal() {
      const discount = discountEl ? (+discountEl.value || 0) : 0;
      const v = Math.max((+amountEl.value || 0) - discount, 0);
      finalEl.value = v;
      badgeEl.textContent = Utils.formatDA(v);
    }

    selProd.addEventListener('change', function () {
      const p = Products[this.value];
      document.getElementById(`${prefix}-descWrap`).style.display = p ? 'block' : 'none';
      if (!p) return;
      amountEl.value = p.price;
      document.getElementById(`${prefix}-productDesc`).value = p.description;
      document.getElementById(`${prefix}-product`).value = p.name;
      updateFinal();
    });
    amountEl.addEventListener('input', updateFinal);
    if (discountEl) discountEl.addEventListener('input', updateFinal);
    return { updateFinal, amountEl, finalEl };
  }

  function initCustomerAndWilaya(prefix) {
    [`${prefix}-phone`, `${prefix}-phone2`].forEach(id => {
      const el = document.getElementById(id);
      if (el) el.addEventListener('input', function () { this.value = Utils.formatPhone(this.value); });
    });

    const wilayaDD = createDropdown({
      inputId: `${prefix}-wSearch`, dropId: `${prefix}-wDrop`, pillId: `${prefix}-wPill`,
      pillTxtId: `${prefix}-wPillTxt`, clearId: `${prefix}-wClear`, hiddenId: `${prefix}-wilayaId`,
      placeholder: 'ابحث عن الولاية...',
      onSelect: (item) => {
        const desks = getDesksForWilaya(item.value);
        deskDD.setItems(desks.map(d => ({ value: d.code, label: d.name, badge: d.code })));
        deskDD.enable('ابحث عن المكتب...');
        showDeskCard(prefix, null);
        document.getElementById(`${prefix}-stationCode`).value = '';
        document.getElementById(`${prefix}-deskCommune`).value = '';
      },
      onClear: () => {
        deskDD.setItems([]); deskDD.disable('اختر الولاية أولاً...');
        showDeskCard(prefix, null);
      },
    });
    const deskDD = createDropdown({
      inputId: `${prefix}-dSearch`, dropId: `${prefix}-dDrop`, pillId: `${prefix}-dPill`,
      pillTxtId: `${prefix}-dPillTxt`, clearId: `${prefix}-dClear`, hiddenId: `${prefix}-stationCode`,
      placeholder: 'اختر الولاية أولاً...',
      onSelect: (item) => {
        const desk = getDesk(item.value);
        document.getElementById(`${prefix}-deskCommune`).value = desk ? (desk.commune || desk.name) : item.label;
        showDeskCard(prefix, desk);
      },
      onClear: () => {
        document.getElementById(`${prefix}-deskCommune`).value = '';
        showDeskCard(prefix, null);
      },
    });
    deskDD.disable('اختر الولاية أولاً...');
    return { wilayaDD, deskDD };
  }

  function showDeskCard(prefix, desk) {
    const card  = document.getElementById(`${prefix}-deskCard`);
    const inner = document.getElementById(`${prefix}-deskCardInner`);
    if (!desk) { if (card) card.style.display = 'none'; return; }
    const phones = Object.values(desk.phones || {}).filter(Boolean).join(' / ');
    inner.textContent = '';
    const name = document.createElement('strong');
    name.textContent = `📍 ${desk.name}`;
    inner.appendChild(name);
    if (desk.address) inner.appendChild(Object.assign(document.createElement('span'), { textContent: `\n🏠 ${desk.address}` }));
    if (phones)       inner.appendChild(Object.assign(document.createElement('span'), { textContent: `\n📞 ${phones}` }));
    if (desk.email)   inner.appendChild(Object.assign(document.createElement('span'), { textContent: `\n📧 ${desk.email}` }));
    card.style.display = 'block';
  }

  /* ══════════════════════════════════════════════════════════
   *  MODULE 20 ▸ SETTINGS PANE BUILDER
   * ══════════════════════════════════════════════════════════ */
  function buildSettingsPane() {
    return `
      <!-- 1. API Configuration -->
      <div class="n9-section">
        <div class="n9-section-title amber">🔑 إعدادات API</div>
        <div class="n9-grid">
          <div class="n9-field">
            <label class="n9-label">API Token <span class="n9-req">*</span></label>
            <div class="n9-input-action n9-pw-wrap">
              <input id="s-token" class="n9-input" type="password" placeholder="أدخل التوكن...">
              <button class="n9-pw-eye" id="s-token-eye" title="إظهار/إخفاء">👁️</button>
            </div>
            <div class="n9-input-action" style="margin-top:4px">
              <button class="n9-settings-btn primary" id="s-token-copy">📋 نسخ</button>
            </div>
          </div>
          <div class="n9-field">
            <label class="n9-label">GUID <span class="n9-req">*</span></label>
            <div class="n9-pw-wrap">
              <input id="s-guid" class="n9-input" type="password" placeholder="أدخل GUID...">
              <button class="n9-pw-eye" id="s-guid-eye" title="إظهار/إخفاء">👁️</button>
            </div>
            <div class="n9-input-action" style="margin-top:4px">
              <button class="n9-settings-btn primary" id="s-guid-copy">📋 نسخ</button>
            </div>
          </div>
          ${field('s-apibase', 'API Base URL', false, inp('s-apibase', 'https://app.noest-dz.com/api/public'), true)}
          <div class="n9-field">
            <label class="n9-label">مهلة الطلب (ms)</label>
            <input id="s-timeout" class="n9-input" type="number" min="5000" max="120000" step="1000" placeholder="30000">
          </div>
          <div class="n9-field">
            <label class="n9-label">عدد إعادة المحاولة</label>
            <input id="s-retry" class="n9-input" type="number" min="0" max="5" placeholder="0">
          </div>
        </div>
        <div style="display:flex;align-items:center;gap:10px;margin-top:12px;flex-wrap:wrap">
          <button class="n9-settings-btn primary" id="s-test-conn">⚡ اختبار الاتصال</button>
          <span id="s-conn-status" class="n9-connection-status">
            <span class="n9-connection-dot"></span>
            <span id="s-conn-text">غير مختبر</span>
          </span>
        </div>
      </div>

      <!-- 2. General Settings -->
      <div class="n9-section">
        <div class="n9-section-title amber">⚙️ الإعدادات العامة</div>
        <div class="n9-settings-group">
          ${toggle('s-autoDownload',   'تحميل PDF تلقائياً بعد الإنشاء',   'autoDownloadPdf')}
          ${toggle('s-openNewTab',     'فتح PDF في تبويب جديد',             'openPdfNewTab')}
          ${toggle('s-showConfirm',    'إظهار تأكيد قبل إنشاء الطلبية',    'showConfirmCreate')}
          ${toggle('s-animations',     'تفعيل الرسوم المتحركة',             'enableAnimations')}
          ${toggle('s-autoCopy',       'نسخ رقم التتبع تلقائياً',          'autoCopyTracking')}
          ${toggle('s-remWilaya',      'تذكر آخر ولاية محددة',             'rememberWilaya')}
          ${toggle('s-remStation',     'تذكر آخر مكتب محدد',              'rememberStation')}
          ${toggle('s-remCustomer',    'تذكر بيانات الزبون',               'rememberCustomer')}
          ${toggle('s-advLogs',        'تفعيل السجلات المتقدمة (Console)', 'enableAdvancedLogs')}
        </div>
      </div>

      <!-- 3. User Preferences (read-only display) -->
      <div class="n9-section">
        <div class="n9-section-title amber">📌 آخر التفضيلات المحفوظة</div>
        <div id="s-prefs-display" class="n9-order-grid" style="grid-template-columns:1fr 1fr 1fr"></div>
      </div>

      <!-- 4. Data Management -->
      <div class="n9-section">
        <div class="n9-section-title amber">🗄️ إدارة البيانات</div>
        <div class="n9-settings-actions">
          <button class="n9-settings-btn success" id="s-export">📤 تصدير الإعدادات</button>
          <button class="n9-settings-btn primary" id="s-import">📥 استيراد الإعدادات</button>
          <input type="file" id="s-import-file" accept=".json" style="display:none">
          <button class="n9-settings-btn"         id="s-clear-cache">🧹 مسح الذاكرة المؤقتة</button>
          <button class="n9-settings-btn danger"  id="s-reset-all">⚠️ إعادة ضبط كل الإعدادات</button>
        </div>
      </div>
      <!-- [v10.2 API] Delivery Fees Table -->
      <div class="n9-section" id="s-fees-section" style="display:none">
        <div class="n9-section-title amber">💰 جدول رسوم التوصيل</div>
        <div class="n9-field">
          <label class="n9-label" for="s-fees-wilaya">الولاية</label>
          <select id="s-fees-wilaya" class="n9-input"></select>
        </div>
        <div style="margin-top:8px;display:flex;gap:8px;align-items:center">
          <button class="n9-settings-btn primary" id="s-fees-btn">💰 عرض الرسوم</button>
          <span id="s-fees-result" class="n9-result" style="display:none;margin:0"></span>
        </div>
        <div id="s-fees-table" style="margin-top:10px;display:none">
          <table style="width:100%;border-collapse:collapse;font-size:13px">
            <thead><tr style="background:var(--n9-b200)"><th style="padding:6px;border:1px solid var(--n9-b300)">الوصف</th><th style="padding:6px;border:1px solid var(--n9-b300)">المبلغ</th></tr></thead>
            <tbody id="s-fees-tbody"></tbody>
          </table>
        </div>
      </div>
    `;
  }

  function toggle(id, label, key) {
    const s = StorageManager.getSettings();
    const checked = s[key] ? 'checked' : '';
    return `
      <label class="n9-toggle-row" for="${id}">
        <div>
          <div class="n9-toggle-label">${label}</div>
        </div>
        <label class="n9-toggle">
          <input type="checkbox" id="${id}" ${checked} data-key="${key}">
          <span class="n9-toggle-track"></span>
          <span class="n9-toggle-thumb"></span>
        </label>
      </label>`;
  }

  /* ══════════════════════════════════════════════════════════
   *  MODULE 21 ▸ PANE HTML BUILDERS
   * ══════════════════════════════════════════════════════════ */
  function buildCreatePane() {
    return `
      <div id="c-loading" class="n9-loading" style="display:flex">
        <span class="n9-spin" style="border-top-color:var(--n9-blue)"></span>
        <span>جاري تحميل البيانات...</span>
      </div>
      <!-- [v10.2 UX] missing‑token banner -->
      <div id="c-token-warning" class="n9-section" style="display:none;border-color:#fbbf24;background:#fef9c3">
        <div style="display:flex;align-items:center;gap:8px;padding:8px 0">
          <span style="font-size:20px">⚠️</span>
          <div>
            <strong style="font-size:13px">إعدادات API مفقودة</strong>
            <p style="margin:4px 0 0;font-size:12px;color:var(--n9-g600)">
              يجب إدخال <strong>Token</strong> و <strong>GUID</strong> في تبويب ⚙️ الإعدادات قبل إنشاء الطلبيات.
            </p>
          </div>
        </div>
      </div>
      ${buildProductSection('c')}
      ${buildAmountSection('c', true)}
      ${buildCustomerSection('c')}
      ${buildWilayaSection('c')}
      <div class="n9-section">
        <div class="n9-section-title">⚙️ خيارات إضافية</div>
        <div class="n9-grid">
          ${field('c-note', 'ملاحظة للتوصيل', false, `<textarea id="c-note" class="n9-input" rows="2" placeholder="أي ملاحظة للسائق..."></textarea>`, true)}
          <label class="n9-check-row">
            <input type="checkbox" id="c-canOpen" checked>
            السماح بالمعاينة قبل الدفع (can_open)
          </label>
          <label class="n9-check-row">
            <input type="checkbox" id="c-remboursement" checked>
            تفعيل الاسترداد / التحصيل (remboursement)
          </label>
        </div>
      </div>
    `;
  }

  function buildEditPane() {
    return `
      <div class="n9-steps">
        <div class="n9-step active" id="e-step1"><div class="n9-step-num">1</div><span>رقم التتبع</span></div>
        <span class="n9-step-sep">›</span>
        <div class="n9-step" id="e-step2"><div class="n9-step-num">2</div><span>تحميل البيانات</span></div>
        <span class="n9-step-sep">›</span>
        <div class="n9-step" id="e-step3"><div class="n9-step-num">3</div><span>تعديل الحقول</span></div>
        <span class="n9-step-sep">›</span>
        <div class="n9-step" id="e-step4"><div class="n9-step-num">4</div><span>مراجعة وحفظ</span></div>
      </div>
      <div class="n9-section" id="e-section-tracking">
        <div class="n9-section-title violet">✏️ تعديل طلبية</div>
        <div class="n9-field">
          <label class="n9-label" for="e-tracking">رقم التتبع <span class="n9-req">*</span></label>
          <div class="n9-input-row">
            <input id="e-tracking" class="n9-input" placeholder="مثال: ECS1234567890" autocomplete="off" spellcheck="false">
            <button class="n9-paste-btn" id="e-paste" title="لصق" aria-label="لصق من الحافظة">📋</button>
            <button class="n9-btn n9-btn--outline-violet" id="e-load-btn" style="padding:8px 16px">🔍 تحميل</button>
          </div>
        </div>
      </div>
      <div id="e-summary-wrap" style="display:none"></div>
      <div id="e-form-wrap" style="display:none">
        <div class="n9-section">
          <div class="n9-section-title violet">⚡ نوع التعديل</div>
          <div class="n9-mode-picker">
            <button class="n9-mode-btn active" id="e-mode-before" data-mode="before">
              ⚡ تعديل مباشر<small>قبل الشحن — تطبيق فوري</small>
            </button>
            <button class="n9-mode-btn" id="e-mode-after" data-mode="after">
              📋 طلب تعديل<small>بعد الاعتماد — لا يمكن تغيير الولاية</small>
            </button>
          </div>
          <div class="n9-info" id="e-mode-hint" style="margin-top:8px">
            يُعدّل الطلبية مباشرةً — يشترط أن تكون <strong>لم تُشحن بعد</strong>.
          </div>
        </div>
        ${buildProductSection('e')}
        ${buildAmountSection('e', true)}
        ${buildCustomerSection('e')}
        <div class="e-before-only">${buildWilayaSection('e')}</div>
        <div class="n9-section">
          <div class="n9-section-title violet">⚙️ خيارات التوصيل</div>
          <div class="n9-grid">
            ${field('e-type', 'نوع التوصيل', false, sel('e-type', `
              <option value="">-- بدون تغيير --</option>
              <option value="1">1 — توصيل</option>
              <option value="2">2 — استبدال</option>
              <option value="3">3 — استرجاع</option>`))}
            <div class="n9-field e-after-only">
              <label class="n9-label">طريقة التوصيل</label>
              ${sel('e-stopdesk', `
                <option value="">-- بدون تغيير --</option>
                <option value="0">🏠 توصيل للمنزل</option>
                <option value="1">🏢 Stop Desk</option>`)}
            </div>
            <div class="n9-field e-after-only" id="f-e-stationCode" style="display:none">
              <label class="n9-label" for="e-stationCode">كود المحطة</label>
              ${inp('e-stationCode', 'مثال: 16B')}
            </div>
            ${field('e-remarque', 'ملاحظة للتوصيل', false, `<textarea id="e-remarque" class="n9-input" rows="2" placeholder="أي ملاحظة..."></textarea>`, 'n9-full')}
          </div>
          <p style="font-size:11.5px;color:var(--n9-g400);margin:10px 0 0;text-align:center;background:var(--n9-g50);border:1px dashed var(--n9-g200);border-radius:7px;padding:8px">
            💡 اتركِ الحقل فارغاً للإبقاء على القيمة الحالية.
          </p>
        </div>
      </div>
      <div id="e-diff-wrap" style="display:none">
        <div class="n9-section">
          <div class="n9-section-title violet">🔍 مقارنة التغييرات</div>
          <div id="e-diff-content"></div>
        </div>
      </div>
      <div id="e-result" style="display:none" role="status" aria-live="polite"></div>
      <!-- [v10.2 API] Add Remark -->
      <div class="n9-section" id="e-remark-section" style="display:none">
        <div class="n9-section-title teal">📝 إضافة ملاحظة سريعة</div>
        <div class="n9-field">
          <label class="n9-label">المحتوى <span class="n9-req">*</span></label>
          <textarea id="e-remark-content" class="n9-input" rows="2" maxlength="255" placeholder="أقصى 255 حرف..."></textarea>
        </div>
        <div style="margin-top:8px;display:flex;gap:8px;align-items:center">
          <button class="n9-btn n9-btn--outline-teal" id="e-remark-btn">📝 إضافة ملاحظة</button>
          <span id="e-remark-result" class="n9-result" style="display:none;margin:0"></span>
        </div>
      </div>
      <!-- [v10.2 API] New Delivery Attempt -->
      <div class="n9-section" id="e-newattempt-section" style="display:none">
        <div class="n9-section-title teal">🔄 طلب محاولة توصيل جديدة</div>
        <p style="font-size:12px;color:var(--n9-g500);margin:0 0 8px">يرسل طلب محاولة توصيل جديدة للوجستي.</p>
        <div style="display:flex;gap:8px;align-items:center">
          <button class="n9-btn n9-btn--outline-teal" id="e-newattempt-btn">🔄 طلب محاولة جديدة</button>
          <span id="e-newattempt-result" class="n9-result" style="display:none;margin:0"></span>
        </div>
      </div>
      <!-- [v10.2 API] Request Return -->
      <div class="n9-section" id="e-return-section" style="display:none">
        <div class="n9-section-title teal">↩️ طلب إرجاع</div>
        <p style="font-size:12px;color:var(--n9-g500);margin:0 0 8px">سيتم إرجاع الطلبية إلى المرسل.</p>
        <div style="display:flex;gap:8px;align-items:center">
          <button class="n9-btn n9-btn--outline-teal" id="e-return-btn">↩️ طلب إرجاع</button>
          <span id="e-return-result" class="n9-result" style="display:none;margin:0"></span>
        </div>
      </div>
    `;
  }

  function buildValidatePane() {
    return `
      <div class="n9-section">
        <div class="n9-section-title green">✅ إرسال / اعتماد</div>
        <div class="n9-info" style="margin-bottom:12px">
          بعد الاعتماد تصبح الطلبية مرئية للوجستياً ولا يمكن تعديلها أو حذفها.
        </div>
        <div class="n9-field">
          <label class="n9-label" for="v-tracking">رقم التتبع <span class="n9-req">*</span></label>
          <div class="n9-input-row">
            <input id="v-tracking" class="n9-input" placeholder="مثال: ECS1234567890" autocomplete="off" spellcheck="false">
            <button class="n9-paste-btn" id="v-paste" aria-label="لصق">📋</button>
            <button class="n9-btn n9-btn--ghost" id="v-load-btn" style="padding:8px 16px">🔍 تحميل</button>
          </div>
        </div>
      </div>
      <div id="v-summary-wrap" style="display:none"></div>
      <div id="v-pdf-actions" style="display:none;margin-top:4px">
        <button class="n9-btn n9-btn--ghost" id="v-download-btn" style="font-size:12px">🖨️ تحميل البوليصة</button>
      </div>
      <div id="v-result" style="display:none" role="status" aria-live="polite"></div>
      <!-- [v10.2 API] Bulk Validate -->
      <div class="n9-section" id="v-bulk-section" style="display:none;margin-top:12px">
        <div class="n9-section-title green">📚 اعتماد متعدد</div>
        <div class="n9-field">
          <label class="n9-label" for="v-bulk-input">أرقام التتبع (واحد لكل سطر، حد أقصى 20)</label>
          <textarea id="v-bulk-input" class="n9-input" rows="4" placeholder="ECS1234567890&#10;ECS0987654321" style="font-family:monospace"></textarea>
        </div>
        <div style="margin-top:8px;display:flex;gap:8px;align-items:center">
          <button class="n9-btn n9-btn--validate" id="v-bulk-btn">📚 اعتماد الكل</button>
          <span id="v-bulk-result" class="n9-result" style="display:none;margin:0"></span>
        </div>
      </div>
    `;
  }

  function buildDeletePane() {
    return `
      <div class="n9-section">
        <div class="n9-section-title red">🗑️ حذف طلبية</div>
        <div class="n9-warn">⚠️ لا يمكن حذف الطلبيات التي تم اعتمادها أو شحنها. يمكن حذف الطلبيات غير المعتمدة فقط.</div>
        <div class="n9-field" style="margin-top:12px">
          <label class="n9-label" for="d-tracking">رقم التتبع <span class="n9-req">*</span></label>
          <div class="n9-input-row">
            <input id="d-tracking" class="n9-input" placeholder="مثال: ECS1234567890" autocomplete="off" spellcheck="false">
            <button class="n9-paste-btn" id="d-paste" aria-label="لصق">📋</button>
            <button class="n9-btn n9-btn--ghost" id="d-load-btn" style="padding:8px 16px">🔍 تحميل</button>
          </div>
        </div>
      </div>
      <div id="d-summary-wrap" style="display:none"></div>
      <div id="d-result" style="display:none" role="status" aria-live="polite"></div>
    `;
  }

  /* ══ NEW MODULE 26 ▸ PDF CENTER ══ */
  function buildPdfPane() {
    return `
      <!-- Section 1 — Single PDF Download -->
      <div class="n9-section">
        <div class="n9-section-title teal">🖨️ تحميل بوليصة</div>
        <div class="n9-field">
          <label class="n9-label" for="pdf-tracking">رقم التتبع <span class="n9-req">*</span></label>
          <div class="n9-input-row">
            <input id="pdf-tracking" class="n9-input" placeholder="مثال: ECS1234567890" autocomplete="off" spellcheck="false">
            <button class="n9-paste-btn" id="pdf-paste" aria-label="لصق">📋</button>
          </div>
        </div>
        <div style="margin-top:10px">
          <button class="n9-btn n9-btn--validate" id="pdf-single-btn" style="min-width:180px">
            <span class="n9-btn-lbl">🖨️ تحميل البوليصة</span>
            <span class="n9-spin" style="display:none" aria-label="جاري التحميل"></span>
          </button>
        </div>
        <div id="pdf-single-result" class="n9-result" style="display:none;margin-top:10px" role="status" aria-live="polite"></div>
        <div id="pdf-order-preview" style="display:none;margin-top:8px;padding:8px;background:var(--n9-b100);border-radius:6px;font-size:12px;line-height:1.6" role="status" aria-live="polite"></div>
      </div>

      <!-- Section 2 — Batch PDF Download -->
      <div class="n9-section">
        <div class="n9-section-title teal">📚 تحميل متعدد</div>
        <div class="n9-field">
          <label class="n9-label" for="pdf-batch-input">أرقام التتبع (واحد لكل سطر، حد أقصى 10)</label>
          <textarea id="pdf-batch-input" class="n9-input" rows="4" placeholder="ECS1234567890&#10;ECS0987654321" style="font-family:monospace"></textarea>
          <div class="n9-field-hint" id="pdf-batch-counter">0/10 أرقام تتبع</div>
        </div>
        <div style="margin-top:10px">
          <button class="n9-btn n9-btn--validate" id="pdf-batch-btn" style="min-width:180px">
            <span class="n9-btn-lbl">📚 تحميل الكل</span>
            <span class="n9-spin" style="display:none" aria-label="جاري التحميل"></span>
          </button>
        </div>
        <div id="pdf-batch-progress-wrap" class="pdf-progress-wrap" style="display:none;margin-top:10px">
          <div class="pdf-batch-progress-bar" id="pdf-batch-progress-bar" style="width:0%"></div>
          <span id="pdf-batch-progress-text" style="font-size:12px;font-weight:600;color:var(--n9-teal)">جاري تحميل 0 من 0...</span>
        </div>
        <div id="pdf-batch-result" style="display:none;margin-top:10px" role="status" aria-live="polite"></div>
      </div>

      <!-- Section 3 — PDF History -->
      <div class="n9-section">
        <div class="n9-section-title teal">📋 سجل PDF</div>
        <div id="pdf-history-list" style="display:flex;flex-direction:column;gap:8px"></div>
        <div id="pdf-history-empty" style="padding:14px;text-align:center;color:var(--n9-g400);font-size:13px">لا توجد تحميلات سابقة</div>
        <div style="margin-top:10px">
          <button class="n9-settings-btn danger" id="pdf-clear-history" style="display:none">🗑️ مسح السجل</button>
        </div>
      </div>`;
  }

  function initPdfPane() {
    // [v10.2 PERF] debounce history re-render
    const renderPdfHistory = Utils.debounce(function() {
      const history = StorageManager.getPdfHistory();
      const listEl  = document.getElementById('pdf-history-list');
      const emptyEl = document.getElementById('pdf-history-empty');
      const clearEl = document.getElementById('pdf-clear-history');
      listEl.innerHTML = '';
      if (!history.length) { emptyEl.style.display = 'block'; clearEl.style.display = 'none'; return; }
      emptyEl.style.display = 'none';
      clearEl.style.display = 'inline-flex';
      history.forEach((entry, idx) => {
        const item = document.createElement('div');
        item.className = 'pdf-history-item';
        item.innerHTML = `
          <span style="font-weight:700;font-family:monospace">${Utils.escapeHtml(entry.tracking)}</span>
          <span style="font-size:11px;color:var(--n9-g400)">${new Date(entry.savedAt).toLocaleString('ar-DZ')}</span>
          <button class="pdf-history-redownload" data-idx="${idx}" title="إعادة التحميل">🔄</button>`;
        listEl.appendChild(item);
        item.querySelector('.pdf-history-redownload').addEventListener('click', () => {
          LabelService.download(entry.tracking, null,
            (m) => { Notify.success('إعادة التحميل', entry.tracking); },
            ()  => { Notify.error('فشل إعادة التحميل', entry.tracking); }
          );
        });
      });
    }, 100);

    /* ── Paste button ── */
    document.getElementById('pdf-paste').addEventListener('click', async () => {
      try { document.getElementById('pdf-tracking').value = (await navigator.clipboard.readText()).trim(); }
      catch (_) { Notify.warning('تعذّر اللصق', 'اسمح بالوصول إلى الحافظة أو الصق يدوياً'); }
    });
    /* ── [v10.2 UX] Order preview on blur ── */
    let _pdfPreviewTimeout;
    document.getElementById('pdf-tracking').addEventListener('blur', () => {
      clearTimeout(_pdfPreviewTimeout);
      _pdfPreviewTimeout = setTimeout(async () => {
        const tracking = document.getElementById('pdf-tracking').value.trim();
        const prevEl   = document.getElementById('pdf-order-preview');
        if (!tracking) { prevEl.style.display = 'none'; return; }
        prevEl.innerHTML = '<span class="n9-spin" style="width:14px;height:14px;border-width:2px;display:inline-block;vertical-align:middle;margin-left:6px" aria-label="جاري التحميل"></span> جاري تحميل المعلومات...';
        prevEl.style.display = 'block';
        try {
          const info = await OrderService.loadTracking(tracking);
          const o = info.OrderInfo || {};
          prevEl.innerHTML = `
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:4px 12px;direction:rtl">
              <span style="color:var(--n9-g500)">الزبون</span><span style="font-weight:600">${Utils.escapeHtml(o.client || '—')}</span>
              <span style="color:var(--n9-g500)">المبلغ</span><span style="font-weight:600">${Utils.formatDA(o.montant || 0)}</span>
              <span style="color:var(--n9-g500)">المنتج</span><span style="font-weight:600">${Utils.escapeHtml(o.produit || '—')}</span>
              <span style="color:var(--n9-g500)">الولاية</span><span style="font-weight:600">${Utils.escapeHtml(String(o.wilaya_id || '—'))}</span>
            </div>`;
        } catch (_) { prevEl.style.display = 'none'; }
      }, 400);
    });
    /* ── Batch counter ── */
    document.getElementById('pdf-batch-input').addEventListener('input', function() {
      const lines = this.value.split('\n').filter(l => l.trim());
      document.getElementById('pdf-batch-counter').textContent = `${Math.min(lines.length, 10)}/10 أرقام تتبع`;
      if (lines.length > 10) {
        this.value = lines.slice(0, 10).join('\n');
        document.getElementById('pdf-batch-counter').textContent = '10/10 أرقام تتبع';
        Notify.warning('الحد الأقصى', 'يمكن تحميل 10 أرقام تتبع كحد أقصى');
      }
    });
    // [v10.2 A11Y] announce counter changes
    document.getElementById('pdf-batch-counter').setAttribute('aria-live', 'polite');
    /* ── Single download ── */
    document.getElementById('pdf-single-btn').addEventListener('click', () => {
      const tracking = document.getElementById('pdf-tracking').value.trim();
      const resultEl = document.getElementById('pdf-single-result');
      const btn      = document.getElementById('pdf-single-btn');
      if (!tracking) { Notify.warning('رقم التتبع مطلوب', ''); return; }
      const lbl = btn.querySelector('.n9-btn-lbl');
      const sp  = btn.querySelector('.n9-spin');
      btn.disabled = true; lbl.style.display = 'none'; sp.style.display = 'inline-block';
      resultEl.style.display = 'none';
      LabelService.download(tracking,
        null,
        (mode) => {
          btn.disabled = false; lbl.style.display = 'inline'; sp.style.display = 'none';
          resultEl.className = 'n9-result ok';
          resultEl.innerHTML = '✅ تم تحميل البوليصة بنجاح';
          resultEl.style.display = 'block';
          StorageManager.addPdfHistory({ tracking, mode });
          renderPdfHistory();
          Notify.success('تم التحميل', `البوليصة ${tracking}`);
          // [v10.2 UX] clear and refocus for quick next entry
          document.getElementById('pdf-tracking').value = '';
          document.getElementById('pdf-tracking').focus();
        },
        () => {
          btn.disabled = false; lbl.style.display = 'inline'; sp.style.display = 'none';
          resultEl.className = 'n9-result err';
          resultEl.innerHTML = '❌ فشل تحميل البوليصة';
          resultEl.style.display = 'block';
          Notify.error('فشل التحميل', tracking);
        }
      );
    });
    document.getElementById('pdf-tracking').addEventListener('keydown', e => {
      if (e.key === 'Enter') document.getElementById('pdf-single-btn').click();
    });
    /* ── Batch download ── */
    // [v10.2 PERF] batch abort flag
    let _batchAborted = false;
    document.getElementById('pdf-batch-btn').addEventListener('click', async function() {
      const btn      = this;
      const lbl      = btn.querySelector('.n9-btn-lbl');
      const sp       = btn.querySelector('.n9-spin');
      const resultEl = document.getElementById('pdf-batch-result');
      const progWrap = document.getElementById('pdf-batch-progress-wrap');
      const progBar  = document.getElementById('pdf-batch-progress-bar');
      const progText = document.getElementById('pdf-batch-progress-text');
      const input    = document.getElementById('pdf-batch-input');
      const abortBtn = document.getElementById('pdf-batch-abort') || (() => {
        const b = document.createElement('button');
        b.id = 'pdf-batch-abort';
        b.className = 'n9-btn n9-btn--delete';
        b.textContent = '⏹ إيقاف';
        b.style.marginRight = '8px';
        progWrap.parentNode.insertBefore(b, progWrap);
        return b;
      })();
      // [v10.2 SEC] validate tracking format
      const raw = input.value.split('\n').map(l => l.trim()).filter(Boolean);
      const valid = /^[A-Z0-9\-]{5,30}$/i;
      const trackings = raw.filter(t => valid.test(t));
      const skipped = raw.length - trackings.length;
      if (!trackings.length) { Notify.warning('أرقام تتبع صالحة مطلوبة', ''); return; }
      if (skipped) Notify.warning('تم تخطي أرقام غير صالحة', `${skipped} رقم`);
      _batchAborted = false;
      abortBtn.style.display = 'inline-block';
      abortBtn.onclick = () => { _batchAborted = true; };
      btn.disabled = true; lbl.style.display = 'none'; sp.style.display = 'inline-block';
      resultEl.style.display = 'none';
      progWrap.style.display = 'block';
      let success = 0, failed = 0;
      const total = trackings.length;
      for (let i = 0; i < total; i++) {
        if (_batchAborted) { failed += total - i; break; }
        progText.textContent = `جاري تحميل ${i + 1} من ${total}...`;
        progBar.style.width = `${((i) / total) * 100}%`;
        try {
          await new Promise((resolve, reject) => {
            LabelService.download(trackings[i],
              null,
              (mode) => {
                StorageManager.addPdfHistory({ tracking: trackings[i], mode });
                success++;
                resolve();
              },
              () => { failed++; resolve(); }
            );
          });
        } catch(e) { failed++; }
        if (i < total - 1) await new Promise(r => setTimeout(r, 800));
      }
      abortBtn.style.display = 'none';
      progBar.style.width = '100%';
      progText.textContent = _batchAborted ? `تم إيقاف التحميل (${success} نجاح)` : `تم تحميل ${success} من ${total}`;
      btn.disabled = false; lbl.style.display = 'inline'; sp.style.display = 'none';
      resultEl.className = failed === 0 ? 'n9-result ok' : 'n9-result err';
      resultEl.innerHTML = _batchAborted
        ? `⏹ تم الإيقاف — ${success} نجاح، ${failed} متبقي`
        : failed === 0
          ? `✅ تم تحميل جميع البوالص (${success}/${total})`
          : `✅ تم تحميل ${success}، فشل ${failed} من ${total}`;
      resultEl.style.display = 'block';
      renderPdfHistory();
      if (_batchAborted) Notify.info('تم الإيقاف', `${success} بوليصة محملة`);
      else if (failed === 0) Notify.success('تم التحميل', `تم تحميل ${success} بوليصة`);
      else Notify.warning('اكتمل مع أخطاء', `فشل ${failed} من ${total}`);
    });
    /* ── PDF History ── */
    document.getElementById('pdf-clear-history').addEventListener('click', () => {
      StorageManager.clearPdfHistory();
      renderPdfHistory();
      Notify.info('تم المسح', 'تم مسح سجل PDF');
    });
    renderPdfHistory();
  }

  /* ══════════════════════════════════════════════════════════
   *  MODULE 22 ▸ BUILD MODAL HTML
   * ══════════════════════════════════════════════════════════ */
  function buildModal() {
    const m = document.createElement('div');
    m.id = 'n9-modal';
    m.style.pointerEvents = 'none';
    m.setAttribute('role', 'dialog');
    m.setAttribute('aria-modal', 'true');
    m.setAttribute('aria-label', 'إدارة طلبيات NOEST');
    m.innerHTML = `
      <div class="n9-header">
        <div class="n9-header-brand"><span>🚚</span><span>NOEST Orders V10.2</span></div>
        <button class="n9-close-btn" id="n9-close" aria-label="إغلاق">×</button>
      </div>
      <div class="n9-tabs" role="tablist">
        <button class="n9-tab active" id="n9-tab-create"   data-tab="create"   role="tab" aria-selected="true"  aria-controls="n9-pane-create">📦 إنشاء</button>
        <button class="n9-tab"        id="n9-tab-validate" data-tab="validate" role="tab" aria-selected="false" aria-controls="n9-pane-validate">✅ إرسال</button>
        <button class="n9-tab"        id="n9-tab-edit"     data-tab="edit"     role="tab" aria-selected="false" aria-controls="n9-pane-edit">✏️ تعديل</button>
        <button class="n9-tab"        id="n9-tab-delete"   data-tab="delete"   role="tab" aria-selected="false" aria-controls="n9-pane-delete">🗑️ حذف</button>
        <button class="n9-tab"        id="n9-tab-pdf"      data-tab="pdf"      role="tab" aria-selected="false" aria-controls="n9-pane-pdf">🖨️ PDF</button>
        <button class="n9-tab"        id="n9-tab-settings" data-tab="settings" role="tab" aria-selected="false" aria-controls="n9-pane-settings">⚙️ الإعدادات</button>
      </div>
      <div class="n9-pane" id="n9-pane-create"   style="display:flex" role="tabpanel" aria-labelledby="n9-tab-create">${buildCreatePane()}</div>
      <div class="n9-pane" id="n9-pane-validate" role="tabpanel" aria-labelledby="n9-tab-validate">${buildValidatePane()}</div>
      <div class="n9-pane" id="n9-pane-edit"     role="tabpanel" aria-labelledby="n9-tab-edit">${buildEditPane()}</div>
      <div class="n9-pane" id="n9-pane-delete"   role="tabpanel" aria-labelledby="n9-tab-delete">${buildDeletePane()}</div>
      <div class="n9-pane" id="n9-pane-pdf"      role="tabpanel" aria-labelledby="n9-tab-pdf">${buildPdfPane()}</div>
      <div class="n9-pane" id="n9-pane-settings" role="tabpanel" aria-labelledby="n9-tab-settings">${buildSettingsPane()}</div>
      <div class="n9-footer" id="n9-footer">
        <button class="n9-btn n9-btn--ghost" id="n9-btn-close-footer">✕ إغلاق</button>
      </div>`;
    return m;
  }

  /* ══════════════════════════════════════════════════════════
   *  MODULE 23 ▸ LOADING STATE HELPERS
   * ══════════════════════════════════════════════════════════ */
  function renderSkeletonCard() {
    return `<div class="n9-skeleton-card">
      <div class="n9-skeleton n9-skeleton-row" style="width:60%"></div>
      <div class="n9-skeleton n9-skeleton-row" style="width:80%"></div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;margin-top:4px">
        <div class="n9-skeleton" style="height:52px;border-radius:7px"></div>
        <div class="n9-skeleton" style="height:52px;border-radius:7px"></div>
        <div class="n9-skeleton" style="height:52px;border-radius:7px"></div>
      </div>
    </div>`;
  }

  function setBtnLoading(btn, loading, label) {
    if (!btn) return;
    const spin = btn.querySelector('.n9-spin');
    const lbl  = btn.querySelector('.n9-btn-lbl');
    btn.disabled = loading;
    if (spin) spin.style.display = loading ? 'inline-block' : 'none';
    if (lbl && label) lbl.textContent = label;
  }

  /* ══════════════════════════════════════════════════════════
   *  MODULE 24 ▸ PREFERENCES HELPERS
   * ══════════════════════════════════════════════════════════ */
  function savePrefsFromCreate(wilayaId, stationCode, clientData) {
    const p = StorageManager.getPreferences();
    if (Config.rememberWilaya && wilayaId) {
      const wilayas = StateManager.get('wilayas');
      const w = wilayas.find(x => String(x.code) === String(wilayaId));
      p.lastWilayaId   = wilayaId;
      p.lastWilayaName = w ? w.nom : '';
    }
    if (Config.rememberStation && stationCode) {
      const desk = getDesk(stationCode);
      p.lastStationCode = stationCode;
      p.lastStationName = desk ? desk.name : '';
    }
    if (Config.rememberCustomer && clientData) {
      p.lastCustomer = clientData;
    }
    StorageManager.savePreferences(p);
  }

  function renderPrefsDisplay() {
    const el = document.getElementById('s-prefs-display');
    if (!el) return;
    const p = StorageManager.getPreferences();
    const items = [
      ['آخر ولاية', p.lastWilayaName || p.lastWilayaId || '—'],
      ['آخر مكتب',  p.lastStationName || p.lastStationCode || '—'],
      ['آخر زبون',  p.lastCustomer?.client || '—'],
    ];
    el.innerHTML = items.map(([lbl, val]) => `
      <div class="n9-order-item">
        <div class="n9-order-item-label">${lbl}</div>
        <div class="n9-order-item-value">${Utils.escapeHtml(String(val))}</div>
      </div>`).join('');
  }

  /* ══════════════════════════════════════════════════════════
   *  MODULE 25 ▸ BOOTSTRAP
   * ══════════════════════════════════════════════════════════ */
  (function bootstrap() {

    /* ── Mount DOM ── */
    const fab = document.createElement('button');
    fab.id = 'n9-fab';
    fab.setAttribute('aria-label', 'فتح إدارة طلبيات NOEST');
    fab.innerHTML = '<span aria-hidden="true">🚚</span><span>NOEST Orders</span>';

    const overlay = document.createElement('div');
    overlay.id = 'n9-overlay';
    overlay.style.display = 'none';

    const modal = buildModal();

    document.body.append(fab, overlay, modal);
    ModalManager.init(overlay, modal);
    // [v10.1 FEATURE] wire PDF Center tab
    initPdfPane();

    fab.addEventListener('click', () => ModalManager.open());
    overlay.addEventListener('click', () => ModalManager.close());
    document.getElementById('n9-close').addEventListener('click', () => ModalManager.close());
    document.getElementById('n9-btn-close-footer').addEventListener('click', () => ModalManager.close());

    document.addEventListener('keydown', e => {
      if (e.key === 'Escape' && StateManager.get('modalOpen')) ModalManager.close();
    });

    // [v10.1 FEATURE] added 'pdf' tab
    ['create', 'validate', 'edit', 'delete', 'pdf', 'settings'].forEach(tab => {
      document.getElementById(`n9-tab-${tab}`).addEventListener('click', () => {
        TabManager.switchTo(tab);
        updateFooter(tab);
        if (tab === 'settings') {
          loadSettingsValues();
          renderPrefsDisplay();
        }
      });
    });

    // [v10.1 PERF] composedPath check + passive:true
    document.addEventListener('click', e => {
      const path = e.composedPath ? e.composedPath() : [e.target];
      const inside = path.some(el => el && el.classList && (el.classList.contains('n9-sw') || el.classList.contains('n9-pill')));
      if (!inside)
        document.querySelectorAll('.n9-drop.open').forEach(d => d.classList.remove('open'));
    }, { passive: true });

    /* ════════════════════════════════════════════════════════
     *  FOOTER
     * ════════════════════════════════════════════════════════ */
    function updateFooter(tab) {
      const footer   = document.getElementById('n9-footer');
      // [v10.2 BUG] stricter selector — avoid matching close-footer button
      const existing = footer.querySelectorAll('[id^="n9-submit-"]:not(#n9-btn-close-footer)');
      existing.forEach(el => el.remove());

      const configs = {
        create:   { cls: 'n9-btn--create',   label: '✔ إنشاء الطلبية',  handler: handleCreate   },
        edit:     { cls: 'n9-btn--edit',     label: '✏️ تأكيد التعديل', handler: handleEdit     },
        validate: { cls: 'n9-btn--validate', label: '✅ إرسال',          handler: handleValidate },
        delete:   { cls: 'n9-btn--delete',   label: '🗑️ تأكيد الحذف',  handler: handleDelete   },
        settings: { cls: 'n9-btn--save',     label: '💾 حفظ الإعدادات', handler: handleSaveSettings },
        pdf:      { cls: 'n9-btn--ghost',    label: '📥 تحميل',         handler: () => {} },
      };

      const cfg = configs[tab];
      if (!cfg) return;

      const btn = document.createElement('button');
      btn.id = `n9-submit-${tab}`;
      btn.className = `n9-btn ${cfg.cls}`;
      btn.innerHTML = `<span class="n9-spin" style="display:none" aria-label="جاري التحميل"></span><span class="n9-btn-lbl">${cfg.label}</span>`;
      btn.addEventListener('click', cfg.handler);
      footer.appendChild(btn);
    }
    // [v10.1 PERF] debounced to prevent race conditions
    const handleSaveSettings = Utils.debounce(function() {
      const btn = document.getElementById('n9-submit-settings');
      const token   = document.getElementById('s-token').value.trim();
      const guid    = document.getElementById('s-guid').value.trim();
      const apibase = document.getElementById('s-apibase').value.trim();
      const timeout = parseInt(document.getElementById('s-timeout').value) || 30000;
      const retry   = parseInt(document.getElementById('s-retry').value) || 0;

      const currentSettings = StorageManager.getSettings();
      const newSettings = { ...currentSettings, TOKEN: token, GUID: guid };
      if (apibase) newSettings.API_BASE = apibase;
      newSettings.REQUEST_TIMEOUT_MS = timeout;
      newSettings.AUTO_RETRY_COUNT   = retry;

      // Read toggles
      document.querySelectorAll('#n9-pane-settings input[data-key]').forEach(el => {
        newSettings[el.dataset.key] = el.checked;
      });

      StorageManager.saveSettings(newSettings);
      // [v10.2 UX] store last-save timestamp
      StorageManager.setLastSaveTs(Date.now());
      Config.reload();
      CacheService.clear();

      setBtnLoading(btn, true, 'جاري الحفظ...');
      setTimeout(() => {
        setBtnLoading(btn, false, '💾 حفظ الإعدادات');
        Notify.success('تم الحفظ', 'تم تحديث الإعدادات بنجاح');
      }, 400);
    }, 300);

    updateFooter('create');

    /* ════════════════════════════════════════════════════════
     *  SETTINGS TAB
     * ════════════════════════════════════════════════════════ */
    function loadSettingsValues() {
      const s = StorageManager.getSettings();
      const setVal = (id, val) => { const el = document.getElementById(id); if (el) el.value = val || ''; };
      setVal('s-token',   s.TOKEN);
      setVal('s-guid',    s.GUID);
      setVal('s-apibase', s.API_BASE || 'https://app.noest-dz.com/api/public');
      setVal('s-timeout', s.REQUEST_TIMEOUT_MS || 30000);
      setVal('s-retry',   s.AUTO_RETRY_COUNT || 0);

      // Sync toggles
      document.querySelectorAll('#n9-pane-settings input[data-key]').forEach(el => {
        el.checked = !!s[el.dataset.key];
      });
    }

    // Show/Hide password fields
    ['token', 'guid'].forEach(key => {
      document.getElementById(`s-${key}-eye`).addEventListener('click', () => {
        const input = document.getElementById(`s-${key}`);
        const eye   = document.getElementById(`s-${key}-eye`);
        const isPass = input.type === 'password';
        input.type  = isPass ? 'text' : 'password';
        eye.textContent = isPass ? '🙈' : '👁️';
      });
      document.getElementById(`s-${key}-copy`).addEventListener('click', () => {
        const val = document.getElementById(`s-${key}`).value;
        Utils.copyToClipboard(val).then(() => Notify.success('تم النسخ', ''));
      });
    });

    // Test connection
    document.getElementById('s-test-conn').addEventListener('click', async () => {
      const btn    = document.getElementById('s-test-conn');
      const status = document.getElementById('s-conn-status');
      const text   = document.getElementById('s-conn-text');
      btn.disabled = true; btn.textContent = '⏳ جاري الاختبار...';
      status.className = 'n9-connection-status';
      text.textContent = 'جاري الاختبار...';

      // Temporarily apply entered values
      const tmpToken = document.getElementById('s-token').value.trim();
      const tmpGuid  = document.getElementById('s-guid').value.trim();
      if (tmpToken) _settings.TOKEN = tmpToken;
      if (tmpGuid)  _settings.GUID  = tmpGuid;

      const result = await ApiService.testConnection();
      btn.disabled = false; btn.textContent = '⚡ اختبار الاتصال';

      if (result.ok) {
        status.className = 'n9-connection-status ok';
        text.textContent = `✅ الاتصال ناجح (${result.count} ولاية)`;
        Notify.success('الاتصال ناجح', `تم جلب ${result.count} ولاية`);
      } else {
        status.className = 'n9-connection-status err';
        text.textContent = `❌ فشل الاتصال`;
        Notify.error('فشل الاتصال', result.error || 'تحقق من التوكن');
      }
    });

    // Export
    document.getElementById('s-export').addEventListener('click', () => {
      const data = StorageManager.exportAll();
      const blob = new Blob([data], { type: 'application/json' });
      const url  = URL.createObjectURL(blob);
      const a    = Object.assign(document.createElement('a'), {
        href: url, download: `noest-settings-${Date.now()}.json`, style: 'display:none',
      });
      document.body.appendChild(a); a.click();
      setTimeout(() => { URL.revokeObjectURL(url); a.remove(); }, 2000);
      Notify.success('تم التصدير', 'تم تنزيل ملف الإعدادات');
    });

    // Import
    document.getElementById('s-import').addEventListener('click', () => {
      document.getElementById('s-import-file').click();
    });
    document.getElementById('s-import-file').addEventListener('change', (e) => {
      const file = e.target.files[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = (ev) => {
        try {
          StorageManager.importAll(ev.target.result);
          Config.reload();
          loadSettingsValues();
          Notify.success('تم الاستيراد', 'تم استيراد الإعدادات بنجاح');
        } catch(err) {
          Notify.error('خطأ في الاستيراد', 'الملف غير صالح');
        }
      };
      reader.readAsText(file);
      e.target.value = '';
    });

    // Clear cache
    document.getElementById('s-clear-cache').addEventListener('click', () => {
      CacheService.clear();
      StorageManager.clearCache();
      Notify.info('تم المسح', 'تم مسح الذاكرة المؤقتة');
    });

    // Reset all
    document.getElementById('s-reset-all').addEventListener('click', async () => {
      const confirmed = await ConfirmDialog.show({
        icon: '⚠️', title: 'إعادة ضبط كل الإعدادات',
        message: 'سيتم حذف جميع الإعدادات والتفضيلات المحفوظة. هل أنت متأكد؟',
        confirmLabel: '⚠️ إعادة الضبط', confirmClass: 'n9-btn--delete',
        details: [],
      });
      if (!confirmed) return;
      StorageManager.resetAll();
      Config.reload();
      loadSettingsValues();
      Notify.warning('تم الضبط', 'تمت إعادة ضبط جميع الإعدادات');
    });

    // [v10.2 API-06] show fees section + populate wilaya selector
    // [v10.2 API-07] commune autocomplete helper
    async function initCommunes(prefix, wilayaId) {
      const container = document.getElementById(`${prefix}-commune-autocomplete`);
      if (!container) return;
      container.innerHTML = '';
      try {
        const { data } = await ApiService.getCommunes(wilayaId);
        if (!Array.isArray(data)) return;
        const existing = container.querySelector('select');
        if (existing) existing.remove();
        const sel = document.createElement('select');
        sel.className = 'n9-input';
        sel.style.marginTop = '4px';
        sel.innerHTML = '<option value="">— اختر البلدية —</option>' + data.map(c => `<option value="${c.name || c.commune || c}">${c.name || c.commune || c}</option>`).join('');
        sel.addEventListener('change', function() {
          if (this.value) document.getElementById(`${prefix}-deskCommune`).value = this.value;
        });
        container.appendChild(sel);
      } catch (_) { /* silent fail */ }
    }
    ['c', 'e'].forEach(p => {
      const $id = document.getElementById(`${p}-wilayaId`);
      if (!$id) return;
      const container = document.createElement('div');
      container.id = `${p}-commune-autocomplete`;
      const communeField = document.getElementById(`${p}-deskCommune`);
      if (communeField && communeField.parentNode) communeField.parentNode.appendChild(container);
      const observer = new MutationObserver(() => {
        const val = $id.value;
        if (val) initCommunes(p, val);
      });
      observer.observe($id, { attributes: true, attributeFilter: ['value'] });
      if ($id.value) initCommunes(p, $id.value);
    });

    // [v10.2 API-06] show fees section + populate wilaya selector
    document.getElementById('s-fees-section').style.display = 'block';
    (function initFees() {
      const dd = document.getElementById('s-fees-wilaya');
      const wilayas = StateManager.get('wilayas') || [];
      dd.innerHTML = wilayas.map(w => `<option value="${w.code}">(${String(w.code).padStart(2,'0')}) ${w.nom}</option>`).join('');
    })();

    document.getElementById('s-fees-btn').addEventListener('click', async () => {
      const wilayaId = document.getElementById('s-fees-wilaya').value;
      const resEl    = document.getElementById('s-fees-result');
      const tableDiv = document.getElementById('s-fees-table');
      const tbody    = document.getElementById('s-fees-tbody');
      resEl.style.display = 'none';
      tableDiv.style.display = 'none';
      try {
        const { data } = await ApiService.getFees(wilayaId);
        if (data.success && Array.isArray(data.fees)) {
          tbody.innerHTML = data.fees.map(f => `<tr><td style="padding:6px;border:1px solid var(--n9-b300)">${Utils.escapeHtml(f.label || f.description || '—')}</td><td style="padding:6px;border:1px solid var(--n9-b300)">${Utils.formatDA(f.amount || f.montant || 0)}</td></tr>`).join('');
          tableDiv.style.display = 'block';
          resEl.style.display = 'none';
        } else {
          resEl.className = 'n9-result err';
          resEl.textContent = '❌ ' + (ErrorManager.parse(data) || 'لا توجد رسوم');
          resEl.style.display = 'block';
        }
      } catch (ex) {
        resEl.className = 'n9-result err';
        resEl.textContent = '❌ ' + ex.message;
        resEl.style.display = 'block';
      }
    });

    /* ════════════════════════════════════════════════════════
     *  CREATE TAB
     * ════════════════════════════════════════════════════════ */
    const { amountEl: cAmount, finalEl: cFinal } = initProductSection('c');
    const { wilayaDD: cWilayaDD, deskDD: cDeskDD } = initCustomerAndWilaya('c');

    async function handleCreate() {
      if (StateManager.get('createPending')) return;

      const client      = document.getElementById('c-client').value.trim();
      const phone       = document.getElementById('c-phone').value.trim();
      const phone2      = document.getElementById('c-phone2').value.trim();
      const wilayaId    = document.getElementById('c-wilayaId').value;
      const stationCode = document.getElementById('c-stationCode').value;
      const commune     = document.getElementById('c-deskCommune').value;
      const produit     = document.getElementById('c-product').value.trim();
      const montant     = Number(cFinal.value) || 0;

      const valid = ValidationService.run([
        { id: 'c-productSelect', value: produit,               label: 'المنتج',  rules: [ValidationService.rules.required] },
        { id: 'c-client',        value: client,                label: 'الاسم',   rules: [ValidationService.rules.required] },
        { id: 'c-phone',         value: phone,                 label: 'الهاتف',  rules: [ValidationService.rules.required, ValidationService.rules.algPhone] },
        { id: 'c-phone2',        value: phone2,                label: 'هاتف2',   rules: phone2 ? [ValidationService.rules.algPhone] : [] },
        { id: 'c-wSearch',       value: wilayaId,              label: 'الولاية', rules: [ValidationService.rules.required] },
        { id: 'c-dSearch',       value: stationCode,           label: 'المكتب',  rules: [ValidationService.rules.required] },
        { id: 'c-amount',        value: String(cAmount.value), label: 'السعر',   rules: [ValidationService.rules.notNaN, ValidationService.rules.positive] },
      ]);
      if (!valid) { Notify.warning('بيانات ناقصة', 'راجع الحقول المحددة بالأحمر'); return; }

      // Build confirmation details
      const wilayas    = StateManager.get('wilayas');
      const wilayaObj  = wilayas.find(w => String(w.code) === String(wilayaId));
      const wilayaName = wilayaObj ? `(${String(wilayaId).padStart(2,'0')}) ${wilayaObj.nom}` : wilayaId;

      if (Config.showConfirmCreate) {
        const confirmed = await ConfirmDialog.show({
          icon:  '📦',
          title: 'تأكيد إرسال الطلبية',
          message: 'هل أنت متأكد من إرسال هذه الطلبية؟\nبعد الإرسال لا يمكن التراجع عن العملية.',
          confirmLabel: '✔ إنشاء الطلبية',
          confirmClass: 'n9-btn--create',
          details: [
            { label: 'الزبون',   value: client },
            { label: 'الهاتف',   value: phone },
            { label: 'الولاية',  value: wilayaName },
            { label: 'المبلغ',   value: Utils.formatDA(montant) },
            { label: 'المنتج',   value: produit },
            { label: 'التوصيل',  value: stationCode ? `Stop Desk: ${stationCode}` : 'منزل' },
          ],
        });
        if (!confirmed) return;
      }

      const btn  = document.getElementById('n9-submit-create');
      StateManager.set('createPending', true);
      setBtnLoading(btn, true, 'جاري الإنشاء...');

      const note      = document.getElementById('c-note').value.trim();
      const canOpen   = document.getElementById('c-canOpen').checked ? 1 : 0;
      const remb      = document.getElementById('c-remboursement').checked ? 1 : 0;
      const reference = Utils.generateRef();

      try {
        const payload = {
          user_guid: Config.GUID, reference, client, phone,
          adresse: commune || stationCode,
          wilaya_id: Number(wilayaId), commune: commune || '',
          montant, produit, type_id: 1, poids: 0.5, stop_desk: 1, // [v10.2 QUALITY] TODO: read stop_desk from UI toggle
          station_code: stationCode, can_open: canOpen, remboursement: remb,
        };
        if (phone2) payload.phone_2  = phone2;
        if (note)   payload.remarque = note;

        const { success, data } = await OrderService.create(payload);
        if (success) {
          // Save preferences
          savePrefsFromCreate(wilayaId, stationCode, { client, phone, phone2, adresse: commune || stationCode });
          StorageManager.saveLastOrder({ tracking: data.tracking, reference, createdAt: new Date().toISOString() });

          SuccessDialog.show(data, reference);
          Notify.success('تم بنجاح!', `رقم التتبع: ${data.tracking}`, 6000);
          resetCreateForm();

          if (Config.autoDownloadPdf) {
            LabelService.download(data.tracking, null, (m) => {
              m === 'download' && Notify.info('تحميل تلقائي', 'تم حفظ البوليصة');
            });
          }
        } else {
          const msg = ErrorManager.parse(data);
          ErrorManager.log('handleCreate', data);
          renderCreateError(msg, data);
          Notify.error('فشل الإنشاء', msg);
        }
      } catch (ex) {
        ErrorManager.log('handleCreate.catch', ex);
        renderCreateError(ex.message, { message: ex.message });
        Notify.error('خطأ في الاتصال', ex.message);
      } finally {
        StateManager.set('createPending', false);
        setBtnLoading(btn, false, '✔ إنشاء الطلبية');
      }
    }

    function renderCreateError(msg, raw) {
      let panel = document.getElementById('c-errorPanel');
      if (!panel) {
        panel = document.createElement('div');
        panel.id = 'c-errorPanel';
        document.getElementById('n9-pane-create').appendChild(panel);
      }
      const icon = document.createElement('div');
      icon.className = 'n9-error-icon'; icon.textContent = '❌';
      const title = document.createElement('div');
      title.className = 'n9-error-title'; title.textContent = msg;
      const detailBtn = document.createElement('button');
      detailBtn.className = 'n9-detail-btn'; detailBtn.textContent = 'تفاصيل ▼';
      const pre = document.createElement('pre');
      pre.className = 'n9-error-pre'; pre.style.display = 'none';
      pre.textContent = JSON.stringify(raw, null, 2);
      detailBtn.addEventListener('click', () => {
        pre.style.display = pre.style.display === 'none' ? 'block' : 'none';
        detailBtn.textContent = pre.style.display === 'block' ? 'إخفاء ▲' : 'تفاصيل ▼';
      });
      const card = document.createElement('div');
      card.className = 'n9-error-card'; card.setAttribute('role', 'alert'); card.setAttribute('aria-live', 'assertive');
      card.append(icon, title, detailBtn, pre);
      panel.innerHTML = '';
      panel.appendChild(card);
      panel.style.display = 'block';
      panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function resetCreateForm() {
      ['c-client', 'c-phone', 'c-phone2', 'c-adresse', 'c-product', 'c-note', 'c-amount'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
      });
      const prodSel = document.getElementById('c-productSelect');
      if (prodSel) prodSel.value = '';
      const descWrap = document.getElementById('c-descWrap');
      if (descWrap) descWrap.style.display = 'none';
      const errPanel = document.getElementById('c-errorPanel');
      if (errPanel) { errPanel.innerHTML = ''; errPanel.style.display = 'none'; }
      cWilayaDD.reset(true);
      cDeskDD.reset(true);
    }

    /* ════════════════════════════════════════════════════════
     *  EDIT TAB
     * ════════════════════════════════════════════════════════ */
    const { amountEl: eAmount, finalEl: eFinal } = initProductSection('e');
    const { wilayaDD: eWilayaDD, deskDD: eDeskDD } = initCustomerAndWilaya('e');

    document.getElementById('e-stopdesk').addEventListener('change', function () {
      document.getElementById('f-e-stationCode').style.display = this.value === '1' ? 'flex' : 'none';
    });

    document.getElementById('e-paste').addEventListener('click', async () => {
      try { document.getElementById('e-tracking').value = (await navigator.clipboard.readText()).trim(); }
      catch (_) { Notify.warning('تعذّر اللصق', 'اسمح بالوصول إلى الحافظة أو الصق يدوياً'); }
    });

    document.getElementById('e-tracking').addEventListener('keydown', e => {
      if (e.key === 'Enter') document.getElementById('e-load-btn').click();
    });

    document.getElementById('e-mode-before').addEventListener('click', () => setEditMode('before'));
    document.getElementById('e-mode-after').addEventListener('click',  () => setEditMode('after'));

    // [v10.2 PERF] cache DOM collections for mode toggle
    const _beforeOnlyEls = document.querySelectorAll('.e-before-only');
    const _afterOnlyEls  = document.querySelectorAll('.e-after-only');

    function setEditMode(mode) {
      StateManager.set('editMode', mode);
      document.getElementById('e-mode-before').classList.toggle('active', mode === 'before');
      document.getElementById('e-mode-after').classList.toggle('active',  mode === 'after');
      document.getElementById('e-mode-hint').innerHTML = mode === 'before'
        ? 'يُعدّل الطلبية مباشرةً — يشترط أن تكون <strong>لم تُشحن بعد</strong>.'
        : 'يُنشئ طلب تعديل — مناسب للطلبيات <strong>المعتمدة وقيد التوصيل</strong>.<br><small>⚠️ لا يمكن تغيير الولاية عبر هذا الخيار.</small>';
      _beforeOnlyEls.forEach(el => { el.style.display = mode === 'before' ? 'flex' : 'none'; });
      _afterOnlyEls.forEach(el  => { el.style.display = mode === 'after'  ? 'flex' : 'none'; });
    }
    setEditMode('before');

    function setEditStep(n) {
      [1, 2, 3, 4].forEach(i => {
        const s = document.getElementById(`e-step${i}`);
        if (!s) return;
        s.classList.toggle('active', i === n);
        s.classList.toggle('done',   i < n);
      });
    }

    // [v10.2 UX] reset edit form after successful save
    function resetEditForm() {
      document.getElementById('e-tracking').value = '';
      document.getElementById('e-summary-wrap').innerHTML = '';
      document.getElementById('e-summary-wrap').style.display = 'none';
      document.getElementById('e-form-wrap').style.display = 'none';
      document.getElementById('e-diff-wrap').style.display = 'none';
      document.getElementById('e-result').style.display = 'none';
      eWilayaDD.reset(true);
      eDeskDD.reset(true);
      setEditStep(1);
      StateManager.set('editOrderData', null);
      StateManager.set('editTracking', null);
    }

    async function loadEditOrder(tracking) {
      if (!tracking) { Notify.warning('رقم التتبع مطلوب', ''); return; }
      const sumWrap  = document.getElementById('e-summary-wrap');
      const formWrap = document.getElementById('e-form-wrap');
      const diffWrap = document.getElementById('e-diff-wrap');
      const resEl    = document.getElementById('e-result');
      sumWrap.innerHTML = renderSkeletonCard();
      sumWrap.style.display = 'block';
      formWrap.style.display = 'none';
      diffWrap.style.display = 'none';
      resEl.style.display    = 'none';
      setEditStep(2);
      try {
        const info = await OrderService.loadTracking(tracking);
        StateManager.set('editOrderData', info);
        StateManager.set('editTracking', tracking);
        sumWrap.innerHTML = renderOrderSummary(info, tracking);
        formWrap.style.display = 'block';
        prefillEditForm(info);
        setEditStep(3);
        formWrap.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        document.getElementById('e-remark-section').style.display = 'block';
        document.getElementById('e-newattempt-section').style.display = 'block';
        document.getElementById('e-return-section').style.display = 'block';
      } catch (ex) {
        ErrorManager.log('e-load-btn', ex);
        const card = document.createElement('div');
        card.className = 'n9-error-card'; card.setAttribute('role', 'alert'); card.setAttribute('aria-live', 'assertive');
        const icon = document.createElement('div'); icon.className = 'n9-error-icon'; icon.textContent = '❌';
        const msg  = document.createElement('div'); msg.className  = 'n9-error-title'; msg.textContent = ex.message;
        card.append(icon, msg);
        sumWrap.innerHTML = '';
        sumWrap.appendChild(card);
        setEditStep(1);
      }
    }

    window.loadEditOrder = loadEditOrder;

    document.getElementById('e-load-btn').addEventListener('click', () => {
      loadEditOrder(document.getElementById('e-tracking').value.trim());
    });

    // [v10.2 QUALITY] findOrderDesk moved to Module 19a
    function renderOrderSummary(info, tracking) {
      const o = info.OrderInfo || {};
      const lastEvent = (info.activity || []).slice(-1)[0];
      const status = lastEvent ? lastEvent.event : '—';
      const wilayas = StateManager.get('wilayas');
      const wilayaObj = wilayas.find(w => String(w.code) === String(o.wilaya_id));
      const wilayaName = wilayaObj ? `(${String(o.wilaya_id).padStart(2, '0')}) ${wilayaObj.nom}` : String(o.wilaya_id || '—');
      const deskObj = findOrderDesk(o);
      const deskName = deskObj ? deskObj.name : (o.commune || '—');
      const wrap = document.createElement('div');
      wrap.className = 'n9-order-card';
      wrap.innerHTML = `
        <div class="n9-order-card-header">
          <div class="n9-order-card-title">📋 ملخص الطلبية
            <span class="n9-order-card-track">${Utils.escapeHtml(tracking)}</span>
          </div>
        </div>
        <div class="n9-order-grid">
          ${[
            ['الزبون',   o.client    || '—'],
            ['الهاتف',   o.phone     || '—'],
            ['الولاية',  wilayaName],
            ['المكتب',   deskName],
            ['البلدية',  o.commune   || '—'],
            ['المبلغ',   Utils.formatDA(o.montant || 0)],
            ['المنتج',   o.produit   || '—'],
            ['النوع',    Utils.typeLabel(o.type_id)],
            ['Stop Desk', o.stop_desk == 1 ? '🏢 نعم' : '🏠 لا'],
            ['الحالة',   status],
          ].map(([lbl, val]) => `
            <div class="n9-order-item">
              <div class="n9-order-item-label">${lbl}</div>
              <div class="n9-order-item-value">${Utils.escapeHtml(String(val))}</div>
            </div>`).join('')}
        </div>`;
      return wrap.outerHTML;
    }

    function prefillEditForm(info) {
      const o = info.OrderInfo || {};
      document.getElementById('e-client').value  = o.client   || '';
      document.getElementById('e-phone').value   = o.phone    || '';
      document.getElementById('e-phone2').value  = o.phone_2  || '';
      document.getElementById('e-adresse').value = o.adresse  || '';
      eAmount.value = o.montant ? parseFloat(o.montant) : '';
      if (eAmount) eAmount.dispatchEvent(new Event('input'));
      const eProdSel = document.getElementById('e-productSelect');
      const matchIdx = Products.findIndex(p => p.name === o.produit);
      eProdSel.value = matchIdx >= 0 ? matchIdx : '';
      document.getElementById('e-product').value = o.produit || '';
      const descWrap = document.getElementById('e-descWrap');
      if (descWrap) descWrap.style.display = matchIdx >= 0 ? 'block' : 'none';
      if (matchIdx >= 0) document.getElementById('e-productDesc').value = Products[matchIdx].description;
      document.getElementById('e-remarque').value = o.remarque || '';
      if (o.type_id)         document.getElementById('e-type').value     = o.type_id;
      if (o.stop_desk != null) document.getElementById('e-stopdesk').value = o.stop_desk;
      // Fill wilaya dropdown
      if (o.wilaya_id) {
        const wilayas = StateManager.get('wilayas');
        const wItem = wilayas.find(w => String(w.code) === String(o.wilaya_id));
        if (wItem) eWilayaDD.pickByValue(wItem.code, false);
        // Fill desk dropdown using commune matching
        const deskObj = findOrderDesk(o);
        if (deskObj) eDeskDD.pickByValue(deskObj.code, false);
      }
    }

    function buildEditDiff() {
      const editOrderData = StateManager.get('editOrderData');
      if (!editOrderData) return '';
      const o = editOrderData.OrderInfo || {};
      const fields = [
        { key: 'client',   label: 'الاسم',    old: o.client,   newId: 'e-client' },
        { key: 'phone',    label: 'الهاتف',   old: o.phone,    newId: 'e-phone' },
        { key: 'phone2',   label: 'هاتف2',    old: o.phone_2,  newId: 'e-phone2' },
        { key: 'montant',  label: 'المبلغ',   old: o.montant ? parseFloat(o.montant) : '', newId: 'e-amount' },
        { key: 'adresse',  label: 'العنوان',  old: o.adresse,  newId: 'e-adresse' },
        { key: 'product',  label: 'المنتج',   old: o.produit,  newId: 'e-product' },
        { key: 'type',     label: 'النوع',    old: Utils.typeLabel(o.type_id), newId: 'e-type', transform: v => Utils.typeLabel(v) || v },
        { key: 'stopdesk', label: 'Stop Desk', old: o.stop_desk == 1 ? '🏢 Stop Desk' : '🏠 منزل', newId: 'e-stopdesk', transform: v => v == 1 ? '🏢 Stop Desk' : v == 0 ? '🏠 منزل' : '' },
      ];
      const rows = fields.filter(f => {
        const el = document.getElementById(f.newId);
        const newVal = el ? el.value.trim() : '';
        return newVal !== '' && String(newVal) !== String(f.old || '');
      });
      if (!rows.length) {
        return '<p style="text-align:center;color:var(--n9-g400);font-size:13px;padding:10px">لا توجد تغييرات</p>';
      }
      const table = document.createElement('table');
      table.className = 'n9-diff-table';
      table.innerHTML = `<thead><tr><th>الحقل</th><th>القيمة الحالية</th><th></th><th>القيمة الجديدة</th></tr></thead>`;
      const tbody = document.createElement('tbody');
      rows.forEach(f => {
        const rawNew = document.getElementById(f.newId).value.trim();
        const newVal = f.transform ? f.transform(rawNew) : rawNew;
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td class="n9-diff-field">${Utils.escapeHtml(f.label)}</td>
          <td class="n9-diff-old">${Utils.escapeHtml(String(f.old || '—'))}</td>
          <td class="n9-diff-arrow">→</td>
          <td class="n9-diff-new">${Utils.escapeHtml(String(newVal))}</td>`;
        tbody.appendChild(tr);
      });
      table.appendChild(tbody);
      return table.outerHTML;
    }

    async function handleEdit() {
      const editOrderData = StateManager.get('editOrderData');
      if (!editOrderData) { Notify.warning('لا توجد طلبية محملة', 'حمّل الطلبية أولاً'); return; }
      if (StateManager.get('editPending')) return;

      const diffWrap    = document.getElementById('e-diff-wrap');
      const diffContent = document.getElementById('e-diff-content');
      const resEl       = document.getElementById('e-result');
      const btn         = document.getElementById('n9-submit-edit');

      diffContent.innerHTML = buildEditDiff();
      diffWrap.style.display = 'block';
      diffWrap.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      setEditStep(4);

      const tracking    = document.getElementById('e-tracking').value.trim();
      const client      = document.getElementById('e-client').value.trim();
      const phone       = document.getElementById('e-phone').value.trim();
      const phone2      = document.getElementById('e-phone2').value.trim();
      const montantRaw  = eFinal.value;
      const adresse     = document.getElementById('e-adresse').value.trim();
      const commune     = document.getElementById('e-deskCommune').value.trim();
      const typeVal     = document.getElementById('e-type').value;
      const stopDesk    = document.getElementById('e-stopdesk').value;
      const stationCode = document.getElementById('e-stationCode').value.trim();
      const remarque    = document.getElementById('e-remarque').value.trim();
      const product     = document.getElementById('e-product').value.trim();
      const wilayaId    = document.getElementById('e-wilayaId').value;
      const editMode    = StateManager.get('editMode');

      if (phone  && !Utils.isValidAlgerianPhone(phone))  { Notify.warning('هاتف غير صالح', '05/06/07'); return; }
      if (phone2 && !Utils.isValidAlgerianPhone(phone2)) { Notify.warning('هاتف إضافي غير صالح', '05/06/07'); return; }
      if (editMode === 'after' && stopDesk === '1' && !stationCode) {
        Notify.warning('كود المحطة مطلوب', 'أدخل كود المحطة عند اختيار Stop Desk');
        document.getElementById('e-stationCode').focus();
        return;
      }

      const confirmed = await ConfirmDialog.show({
        icon:  '✏️', title: 'تأكيد تعديل الطلبية',
        message: 'هل تريد تطبيق هذه التغييرات على الطلبية؟',
        confirmLabel: '✏️ تأكيد التعديل', confirmClass: 'n9-btn--edit',
        details: [
          { label: 'رقم التتبع',  value: tracking },
          { label: 'نوع التعديل', value: editMode === 'before' ? 'تعديل مباشر (قبل الشحن)' : 'طلب تعديل (بعد الاعتماد)' },
        ],
      });
      if (!confirmed) { diffWrap.style.display = 'none'; setEditStep(3); return; }

      let payload = { tracking };
      if (editMode === 'before') {
        if (client)          payload.client    = client;
        if (phone)           payload.tel       = phone;
        if (phone2)          payload.tel2      = phone2;
        if (montantRaw)      payload.montant   = Number(montantRaw);
        if (adresse)         payload.adresse   = adresse;
        if (commune)         payload.commune   = commune;
        if (typeVal)         payload.type      = Number(typeVal);
        if (stopDesk !== '') payload.stop_desk = Number(stopDesk);
        if (remarque)        payload.remarque  = remarque;
        if (product)         payload.product   = product;
        if (wilayaId)        payload.wilaya    = Number(wilayaId);
      } else {
        if (phone)           payload.tel         = phone;
        if (montantRaw)      payload.montant     = Number(montantRaw);
        if (adresse)         payload.adresse     = adresse;
        if (commune)         payload.commune     = commune;
        if (typeVal)         payload.type        = Number(typeVal);
        if (stopDesk !== '') payload.stop_desk   = Number(stopDesk);
        if (stopDesk === '1' && stationCode) payload.code_station = stationCode;
      }

      const editableKeys = Object.keys(payload).filter(k => k !== 'tracking');
      if (!editableKeys.length) { Notify.warning('لا توجد تغييرات', ''); return; }

      StateManager.set('editPending', true);
      setBtnLoading(btn, true, 'جاري التعديل...');
      resEl.style.display = 'none';

      try {
        const endpoint = editMode === 'before' ? '/update/order/before/expedition' : '/update/order';
        const res  = await ApiService._fetchWithTimeout(`${Config.API_BASE}${endpoint}`, {
          method: 'POST',
          headers: { Authorization: `Bearer ${Config.TOKEN}`, 'Content-Type': 'application/json' },
          body: JSON.stringify(payload),
        });
        const data = await res.json();
        const modeLabel = editMode === 'before' ? 'تعديل مباشر' : 'طلب تعديل';

        resEl.className = data.success ? 'n9-result ok' : 'n9-result err';
        if (data.success) {
          resEl.innerHTML = `✅ تم ${modeLabel} بنجاح!<br><strong>رقم التتبع:</strong> ${Utils.escapeHtml(tracking)}`;
          Notify.success('تم التعديل', `الطلبية ${tracking}`, 6000);
          StateManager.set('editOrderData', null);
          CacheService.del('/get/trackings/info');
        } else {
          const msg = ErrorManager.parse(data);
          ErrorManager.log('handleEdit', data);
          resEl.innerHTML = `❌ فشل التعديل<br><small>${Utils.escapeHtml(msg)}</small>`;
          Notify.error('فشل التعديل', msg);
        }
        resEl.style.display = 'block';
      } catch (ex) {
        ErrorManager.log('handleEdit.catch', ex);
        resEl.className = 'n9-result err';
        resEl.textContent = `❌ خطأ في الاتصال: ${ex.message}`;
        resEl.style.display = 'block';
        Notify.error('خطأ', ex.message);
      } finally {
        StateManager.set('editPending', false);
        setBtnLoading(btn, false, '✏️ تأكيد التعديل');
      }
    }

    function showEditActionResult(resEl, ok, msg) {
      resEl.className = ok ? 'n9-result ok' : 'n9-result err';
      resEl.textContent = ok ? `✅ ${msg}` : `❌ ${msg}`;
      resEl.style.display = 'block';
      setTimeout(() => { resEl.style.display = 'none'; }, 6000);
    }

    /* ── [v10.2 API-01] Add Remark ── */
    document.getElementById('e-remark-btn').addEventListener('click', async () => {
      const tracking = document.getElementById('e-tracking').value.trim();
      const content  = document.getElementById('e-remark-content').value.trim();
      const resEl    = document.getElementById('e-remark-result');
      if (!tracking) { Notify.warning('حمّل الطلبية أولاً', ''); return; }
      if (!content)  { Notify.warning('المحتوى مطلوب', ''); return; }
      try {
        const { data } = await ApiService.addRemark(tracking, content);
        showEditActionResult(resEl, data.success, data.success ? 'تمت إضافة الملاحظة' : (ErrorManager.parse(data) || ''));
        if (data.success) document.getElementById('e-remark-content').value = '';
      } catch (ex) { showEditActionResult(resEl, false, ex.message); }
    });

    /* ── [v10.2 API-02] New Attempt ── */
    document.getElementById('e-newattempt-btn').addEventListener('click', async () => {
      const tracking = document.getElementById('e-tracking').value.trim();
      const resEl    = document.getElementById('e-newattempt-result');
      if (!tracking) { Notify.warning('حمّل الطلبية أولاً', ''); return; }
      try {
        const { data } = await ApiService.askNewAttempt(tracking);
        showEditActionResult(resEl, data.success, data.success ? 'تم طلب محاولة توصيل جديدة' : (ErrorManager.parse(data) || ''));
      } catch (ex) { showEditActionResult(resEl, false, ex.message); }
    });

    /* ── [v10.2 API-03] Return ── */
    document.getElementById('e-return-btn').addEventListener('click', async () => {
      const tracking = document.getElementById('e-tracking').value.trim();
      const resEl    = document.getElementById('e-return-result');
      if (!tracking) { Notify.warning('حمّل الطلبية أولاً', ''); return; }
      try {
        const { data } = await ApiService.askReturn(tracking);
        showEditActionResult(resEl, data.success, data.success ? 'تم طلب الإرجاع' : (ErrorManager.parse(data) || ''));
      } catch (ex) { showEditActionResult(resEl, false, ex.message); }
    });

    /* ════════════════════════════════════════════════════════
     *  VALIDATE TAB
     * ════════════════════════════════════════════════════════ */
    document.getElementById('v-paste').addEventListener('click', async () => {
      try { document.getElementById('v-tracking').value = (await navigator.clipboard.readText()).trim(); }
      catch (_) { Notify.warning('تعذّر اللصق', ''); }
    });
    document.getElementById('v-tracking').addEventListener('keydown', e => {
      if (e.key === 'Enter') document.getElementById('v-load-btn').click();
    });

    document.getElementById('v-load-btn').addEventListener('click', async () => {
      const tracking = document.getElementById('v-tracking').value.trim();
      const sumWrap  = document.getElementById('v-summary-wrap');
      const resEl    = document.getElementById('v-result');
      const pdfEl    = document.getElementById('v-pdf-actions');
      if (!tracking) { Notify.warning('رقم التتبع مطلوب', ''); return; }
      sumWrap.innerHTML = renderSkeletonCard();
      sumWrap.style.display = 'block';
      resEl.style.display = 'none';
      pdfEl.style.display = 'none';
      try {
        const info = await OrderService.loadTracking(tracking);
        StateManager.set('validateOrderData', info);
        StateManager.set('validateTracking', tracking);
        sumWrap.innerHTML = renderOrderSummary(info, tracking);
        pdfEl.style.display = 'flex';
        document.getElementById('v-download-btn').onclick = () => {
          LabelService.download(tracking, null,
            (m) => { Notify.success('تم التحميل', tracking); },
            ()  => { Notify.error('فشل التحميل', tracking); }
          );
        };
      } catch (ex) {
        ErrorManager.log('v-load-btn', ex);
        const card = document.createElement('div');
        card.className = 'n9-error-card'; card.setAttribute('role', 'alert'); card.setAttribute('aria-live', 'assertive');
        const icon = document.createElement('div'); icon.className = 'n9-error-icon'; icon.textContent = '❌';
        const msg  = document.createElement('div'); msg.className  = 'n9-error-title'; msg.textContent = ex.message;
        card.append(icon, msg);
        sumWrap.innerHTML = '';
        sumWrap.appendChild(card);
        StateManager.set('validateOrderData', null);
        StateManager.set('validateTracking', null);
      }
    });

    async function handleValidate() {
      if (StateManager.get('validatePending')) return;
      const tracking = StateManager.get('validateTracking');
      const resEl    = document.getElementById('v-result');
      const btn      = document.getElementById('n9-submit-validate');
      if (!tracking) { Notify.warning('حمّل الطلبية أولاً', ''); return; }

      const confirmed = await ConfirmDialog.show({
        icon:  '✅', title: 'تأكيد الاعتماد',
        message: 'بعد الاعتماد تصبح الطلبية مرئية للوجستياً ولا يمكن تعديلها أو حذفها.',
        confirmLabel: '✅ إرسال', confirmClass: 'n9-btn--validate',
        details: [{ label: 'رقم التتبع', value: tracking }],
      });
      if (!confirmed) return;

      StateManager.set('validatePending', true);
      setBtnLoading(btn, true, 'جاري الاعتماد...');
      resEl.style.display = 'none';

      try {
        const { data } = await ApiService.validateOrder(tracking);
        if (data.success) {
          resEl.className = 'n9-result ok';
          resEl.innerHTML = `✅ تم الإرسال بنجاح!<br><strong>رقم التتبع:</strong> ${Utils.escapeHtml(tracking)}`;
          resEl.style.display = 'block';
          Notify.success('تم الاعتماد', tracking, 6000);
          document.getElementById('v-tracking').value = '';
          document.getElementById('v-summary-wrap').style.display = 'none';
          document.getElementById('v-pdf-actions').style.display = 'none';
          StateManager.set('validateOrderData', null);
          StateManager.set('validateTracking', null);
          document.getElementById('v-bulk-section').style.display = 'block';
        } else {
          const msg = ErrorManager.parse(data);
          ErrorManager.log('handleValidate', data);
          resEl.className = 'n9-result err';
          resEl.innerHTML = `❌ فشل الاعتماد<br><small>${Utils.escapeHtml(msg)}</small>`;
          resEl.style.display = 'block';
          Notify.error('فشل الاعتماد', msg);
        }
      } catch (ex) {
        ErrorManager.log('handleValidate.catch', ex);
        resEl.className = 'n9-result err';
        resEl.textContent = `❌ خطأ في الاتصال: ${ex.message}`;
        resEl.style.display = 'block';
        Notify.error('خطأ', ex.message);
      } finally {
        StateManager.set('validatePending', false);
        setBtnLoading(btn, false, '✅ إرسال');
      }
    }

    /* ── [v10.2 API-05] Bulk Validate ── */
    document.getElementById('v-bulk-btn').addEventListener('click', async () => {
      const input  = document.getElementById('v-bulk-input');
      const resEl  = document.getElementById('v-bulk-result');
      const trackings = input.value.split('\n').map(l => l.trim()).filter(Boolean).slice(0, 20);
      if (!trackings.length) { Notify.warning('أرقام التتبع مطلوبة', ''); return; }
      resEl.style.display = 'none';
      const results = [];
      for (const t of trackings) {
        await new Promise(r => setTimeout(r, 300));
        try {
          const { data } = await ApiService.validateOrder(t);
          if (data.success) results.push(`✅ ${t}`);
          else results.push(`❌ ${t}: ${ErrorManager.parse(data)}`);
        } catch (ex) { results.push(`❌ ${t}: ${ex.message}`); }
      }
      const ok = results.filter(r => r.startsWith('✅')).length;
      resEl.className = ok === trackings.length ? 'n9-result ok' : 'n9-result err';
      resEl.innerHTML = `✅ نجاح ${ok}/${trackings.length}<br><small style="font-size:11px">${results.slice(0, 5).join('<br>')}</small>`;
      resEl.style.display = 'block';
      Notify.info('الاعتماد المتعدد', `نجاح ${ok} من ${trackings.length}`);
    });

    /* ════════════════════════════════════════════════════════
     *  DELETE TAB
     * ════════════════════════════════════════════════════════ */
    document.getElementById('d-paste').addEventListener('click', async () => {
      try { document.getElementById('d-tracking').value = (await navigator.clipboard.readText()).trim(); }
      catch (_) { Notify.warning('تعذّر اللصق', ''); }
    });
    document.getElementById('d-tracking').addEventListener('keydown', e => {
      if (e.key === 'Enter') document.getElementById('d-load-btn').click();
    });

    document.getElementById('d-load-btn').addEventListener('click', async () => {
      const tracking = document.getElementById('d-tracking').value.trim();
      const sumWrap  = document.getElementById('d-summary-wrap');
      const resEl    = document.getElementById('d-result');
      if (!tracking) { Notify.warning('رقم التتبع مطلوب', ''); return; }

      sumWrap.innerHTML = renderSkeletonCard();
      sumWrap.style.display = 'block';
      resEl.style.display = 'none';

      try {
        const info = await OrderService.loadTracking(tracking);
        StateManager.set('deleteOrderData', info);
        StateManager.set('deleteTracking', tracking);
        const card = document.createElement('div');
        card.className = 'n9-order-card';
        card.style.cssText = 'border-color:#fca5a5;background:linear-gradient(135deg,#fff1f2,#fee2e2)';
        card.innerHTML = renderOrderSummary(info, tracking);
        card.querySelector('.n9-order-card-header').innerHTML = `
          <div class="n9-order-card-title" style="color:#9f1239">
            ⚠️ تأكيد الحذف
            <span class="n9-order-card-track" style="color:#9f1239">${Utils.escapeHtml(tracking)}</span>
          </div>`;
        sumWrap.innerHTML = '';
        sumWrap.appendChild(card);
      } catch (ex) {
        ErrorManager.log('d-load-btn', ex);
        const card = document.createElement('div');
        card.className = 'n9-error-card'; card.setAttribute('role', 'alert'); card.setAttribute('aria-live', 'assertive');
        const icon = document.createElement('div'); icon.className = 'n9-error-icon'; icon.textContent = '❌';
        const msg  = document.createElement('div'); msg.className  = 'n9-error-title'; msg.textContent = ex.message;
        card.append(icon, msg);
        sumWrap.innerHTML = '';
        sumWrap.appendChild(card);
        StateManager.set('deleteOrderData', null);
        StateManager.set('deleteTracking', null);
      }
    });

    async function handleDelete() {
      if (StateManager.get('deletePending')) return;
      const tracking  = document.getElementById('d-tracking').value.trim();
      const resEl     = document.getElementById('d-result');
      const btn       = document.getElementById('n9-submit-delete');
      const orderData = StateManager.get('deleteOrderData');
      if (!tracking) { Notify.warning('رقم التتبع مطلوب', ''); return; }

      const o = orderData ? (orderData.OrderInfo || {}) : {};
      const details = [{ label: 'رقم التتبع', value: tracking }];
      if (o.client)  details.push({ label: 'الزبون', value: o.client });
      if (o.produit) details.push({ label: 'المنتج', value: o.produit });

      const confirmed = await ConfirmDialog.show({
        icon: '🗑️', title: 'حذف طلبية',
        message: 'هل أنت متأكد من حذف هذه الطلبية؟ لا يمكن التراجع عن هذا الإجراء.',
        confirmLabel: '🗑️ حذف نهائي', confirmClass: 'n9-btn--delete', details,
      });
      if (!confirmed) return;

      StateManager.set('deletePending', true);
      setBtnLoading(btn, true, 'جاري الحذف...');
      resEl.style.display = 'none';

      try {
        const res  = await ApiService._fetchWithTimeout(`${Config.API_BASE}/delete/order`, {
          method: 'POST',
          headers: { Authorization: `Bearer ${Config.TOKEN}`, 'Content-Type': 'application/json' },
          body: JSON.stringify({ user_guid: Config.GUID, tracking }),
        });
        const data = await res.json();
        if (data.success) {
          resEl.className = 'n9-result ok';
          resEl.innerHTML = `✅ تم حذف الطلبية بنجاح!<br><strong>رقم التتبع:</strong> ${Utils.escapeHtml(tracking)}`;
          resEl.style.display = 'block';
          Notify.success('تم الحذف', tracking);
          document.getElementById('d-tracking').value = '';
          document.getElementById('d-summary-wrap').innerHTML = '';
          document.getElementById('d-summary-wrap').style.display = 'none';
          StateManager.set('deleteOrderData', null);
          StateManager.set('deleteTracking', null);
        } else {
          const msg = ErrorManager.parse(data);
          ErrorManager.log('handleDelete', data);
          resEl.className = 'n9-result err';
          resEl.innerHTML = `❌ فشل الحذف<br><small>${Utils.escapeHtml(msg)}</small>`;
          resEl.style.display = 'block';
          Notify.error('فشل الحذف', msg);
        }
      } catch (ex) {
        ErrorManager.log('handleDelete.catch', ex);
        resEl.className = 'n9-result err';
        resEl.textContent = `❌ خطأ في الاتصال: ${ex.message}`;
        resEl.style.display = 'block';
        Notify.error('خطأ', ex.message);
      } finally {
        StateManager.set('deletePending', false);
        setBtnLoading(btn, false, '🗑️ تأكيد الحذف');
      }
    }

    /* ════════════════════════════════════════════════════════
     *  INITIAL DATA LOAD
     * ════════════════════════════════════════════════════════ */
    async function loadData() {
      const loadingEl = document.getElementById('c-loading');
      if (loadingEl) loadingEl.style.display = 'flex';
      try {
        const [wilayas, desks] = await Promise.all([ApiService.getWilayas(), ApiService.getDesks()]);
        const activeWilayas = (wilayas || []).filter(w => w.is_active);
        StateManager.set('wilayas', activeWilayas);
        StateManager.set('desks',   desks || {});

        const wItems = activeWilayas.map(w => ({
          value: w.code,
          label: w.nom,
          badge: String(w.code).padStart(2, '0'),
        }));
        cWilayaDD.setItems(wItems);
        eWilayaDD.setItems(wItems);

        // Restore last preferences or auto-select first wilaya
        if (Config.rememberWilaya) {
          const p = StorageManager.getPreferences();
          if (p.lastWilayaId) {
            const item = wItems.find(w => String(w.value) === String(p.lastWilayaId));
            if (item) cWilayaDD.pickByValue(item.value, false);
          } else if (wItems.length) {
            cWilayaDD.pickByValue(wItems[0].value, false);
          }
        } else if (wItems.length) {
          cWilayaDD.pickByValue(wItems[0].value, false);
        }
      } catch (e) {
        ErrorManager.log('loadData', e);
        if (loadingEl) loadingEl.innerHTML = '<span>⚠️</span><span>تعذّر جلب البيانات — تحقق من الإعدادات</span>';
        Notify.warning('تنبيه', 'تعذّر جلب بيانات الولايات — تأكد من الإعدادات');
      } finally {
        if (loadingEl) setTimeout(() => { loadingEl.style.display = 'none'; }, 400);
      }
    }

    loadData();

    // Apply saved TOKEN/GUID alert if they are placeholders
    const tokenMissing = Config.TOKEN === 'TOKEN' || Config.GUID === 'GUID';
    const warnBanner = document.getElementById('c-token-warning');
    if (tokenMissing) {
      if (warnBanner) warnBanner.style.display = 'block';
      setTimeout(() => {
        Notify.warning('إعدادات مفقودة', 'يرجى إدخال TOKEN و GUID في تبويب ⚙️ الإعدادات', 8000);
      }, 1500);
    } else {
      if (warnBanner) warnBanner.style.display = 'none';
    }

  })(); // end bootstrap

})();
