/*
 * PHASE 37-K.2 - dashboard chart interaction regression.
 *
 * Drives a real headless Chrome session against the merchant dashboard and
 * clicks every filter the way an owner does, asserting the two Chart.js
 * canvases survive every mutation of the Livewire tree:
 *
 *   - re-clicking the already-active period pill must be a no-op (no request)
 *   - any same-filter roundtrip that still reaches the server must not strip
 *     the width/height/style attributes Chart.js needs (wire:ignore safety net)
 *   - A -> B -> A, rapid triple clicks and double reset must redraw cleanly
 *   - the no-data placeholder and the theme toggle keep working
 *   - zero console errors / JS exceptions across the whole run
 *   - one Chart.js instance per canvas, always "live" when the canvas exists
 *
 * Run:
 *   php artisan serve --host=127.0.0.1 --port=8000           (one terminal)
 *   npm run test:charts                                      (another)
 *
 * Environment:
 *   EDZEERY_BASE_URL   dashboard app base URL (default http://127.0.0.1:8000)
 *   EDZEERY_EMAIL      login email            (default browser-test@edzeery.com)
 *   EDZEERY_PASSWORD   login password         (default secret123)
 *   EDZEERY_STORE_SLUG store slug             (default demo)
 *   EDZEERY_CHROME     Chrome binary          (default Windows path)
 *   EDZEERY_EXPECT_DATA=0  tolerate empty datasets ("no data") but still check
 *                          structure (default 1 = require demo-like data)
 */

import { spawn } from 'node:child_process';
import { mkdtempSync, rmSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import WebSocket from 'ws';

const BASE = process.env.EDZEERY_BASE_URL?.replace(/\/$/, '') ?? 'http://127.0.0.1:8000';
const EMAIL = process.env.EDZEERY_EMAIL ?? 'browser-test@edzeery.com';
const PASSWORD = process.env.EDZEERY_PASSWORD ?? 'secret123';
const SLUG = process.env.EDZEERY_STORE_SLUG ?? 'demo';
const CHROME = process.env.EDZEERY_CHROME
  ?? (process.platform === 'win32' ? 'C:/Program Files/Google/Chrome/Application/chrome.exe' : 'google-chrome');
const EXPECT_DATA = (process.env.EDZEERY_EXPECT_DATA ?? '1') !== '0';

const failures = [];
const stepLog = [];
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

function launchChrome(profile) {
  const port = 9300 + Math.floor(Math.random() * 400);
  const child = spawn(CHROME, [
    '--headless=new', '--disable-gpu', '--no-first-run', '--no-default-browser-check',
    `--remote-debugging-port=${port}`, `--user-data-dir=${profile}`,
    '--window-size=1440,900', '--hide-scrollbars', 'about:blank',
  ], { stdio: 'ignore' });
  return { child, port };
}

async function cdpTarget(port) {
  for (let i = 0; i < 60; i++) {
    try {
      const list = await (await fetch(`http://127.0.0.1:${port}/json/list`)).json();
      const page = list.find((t) => t.type === 'page');
      if (page) return page;
    } catch { /* still booting */ }
    await sleep(250);
  }
  throw new Error('no CDP target');
}

class CDP {
  constructor(url) {
    this.ws = new WebSocket(url);
    this.next = 0;
    this.pending = new Map();
    this.onEvent = null;
  }
  async open() {
    await new Promise((res, rej) => { this.ws.on('open', res); this.ws.on('error', rej); });
    this.ws.on('message', (raw) => {
      const msg = JSON.parse(raw.toString());
      if (msg.id && this.pending.has(msg.id)) {
        const { resolve: r, reject } = this.pending.get(msg.id);
        this.pending.delete(msg.id);
        if (msg.error) reject(new Error(msg.error.message)); else r(msg.result);
      } else if (msg.method) this.onEvent?.(msg);
    });
  }
  send(method, params = {}) {
    const id = ++this.next;
    return new Promise((resolve, reject) => {
      this.pending.set(id, { resolve, reject });
      this.ws.send(JSON.stringify({ id, method, params }));
    });
  }
  close() { this.ws.close(); }
}

async function login() {
  const jar = {};
  const resp = await fetch(`${BASE}/login`, { redirect: 'manual' });
  const html = await resp.text();
  const token = html.match(/name="_token" value="([^"]+)"/)?.[1];
  if (!token) throw new Error('login page did not expose a CSRF token (is artisan serve running?)');
  for (const c of resp.headers.getSetCookie()) {
    const [pair] = c.split(';');
    const i = pair.indexOf('=');
    jar[pair.slice(0, i)] = pair.slice(i + 1);
  }
  const body = new URLSearchParams({ email: EMAIL, password: PASSWORD, _token: token });
  const p = await fetch(`${BASE}/login`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded', Cookie: Object.entries(jar).map(([k, v]) => `${k}=${v}`).join('; ') },
    body, redirect: 'manual',
  });
  for (const c of p.headers.getSetCookie()) {
    const [pair] = c.split(';');
    const i = pair.indexOf('=');
    jar[pair.slice(0, i)] = pair.slice(i + 1);
  }
  if (p.status !== 302) throw new Error(`login POST returned ${p.status}`);
  return jar;
}

const PILL_JS = `[...document.querySelectorAll('button')].filter(b => (b.getAttribute('wire:click') || '').startsWith('setPeriod('))`;

const PROBE = `(() => {
  try {
    const sc = () => document.getElementById('salesChart');
    const st = () => document.getElementById('statusChart');
    const state = (c) => c ? { w: c.getAttribute('width'), h: c.getAttribute('height'), style: c.getAttribute('style'), live: !!(window.Chart && window.Chart.getChart(c)) } : null;
    const pills = ${PILL_JS}.map(b => ({ key: (b.getAttribute('wire:click') || '').replace(/^setPeriod\\('|'\\)$/g, ''), disabled: b.disabled, active: (b.className || '').includes('bg-brand-600') }));
    const active = pills.find(p => p.active);
    return {
      view: (new URLSearchParams(location.search).get('md') || 'confirmation'),
      activeKey: active ? active.key : null,
      activeDisabled: active ? active.disabled : null,
      salesCanvas: !!sc(),
      statusCanvas: !!st(),
      sales: state(sc()),
      status: state(st()),
      sLabels: window.__dashCharts?.s?.data?.labels?.length ?? null,
      stSlices: window.__dashCharts?.st?.data?.datasets?.[0]?.data ?? null,
      placeholders: [...document.querySelectorAll('p')].filter(p => (p.textContent || '').trim()).map(p => p.textContent.trim()).filter(t => t.includes('لا توجد')).length,
    };
  } catch (e) { return { err: String(e) }; }
})()`;

async function runView(cdp, md) {
  const evalJs = async (expression) => {
    const r = await cdp.send('Runtime.evaluate', { expression, returnByValue: true });
    return r.result.value;
  };
  const click = (code) => evalJs(`(() => { ${code}; return true; })()`);

  await cdp.send('Page.navigate', { url: `${BASE}/merchant/${SLUG}/dashboard?md=${md}` });
  await sleep(6000);
  for (let i = 0; i < 60; i++) {
    if (await evalJs(`location.search.includes('md=${md}') && !!window.Chart && !!document.getElementById('salesChart') && !!window.__dashCharts?.s`)) break;
    await sleep(500);
  }
  await sleep(1200);

  const visit = async (step) => {
    stepLog.push(step);
    const probe = await evalJs(PROBE);
    const check = (cond, msg) => { if (!cond) failures.push({ step, msg, probe }); };
    if (probe.err) { failures.push({ step, msg: `probe error: ${probe.err}` }); return probe; }

    if (probe.salesCanvas) {
      check(probe.sales.w && probe.sales.h && probe.sales.style, `trend canvas lost its size attributes (w=${probe.sales.w} h=${probe.sales.h} style=${probe.sales.style})`);
      check(probe.sales.live, 'trend canvas has no live Chart.js instance');
    }
    if (probe.statusCanvas) {
      check(probe.status.w && probe.status.h && probe.status.style, `doughnut canvas lost its size attributes (w=${probe.status.w} h=${probe.status.h} style=${probe.status.style})`);
      check(probe.status.live, 'doughnut canvas has no live Chart.js instance');
    }
    check(probe.activeDisabled === true, `active pill is not disabled (key=${probe.activeKey})`);
    if (EXPECT_DATA && probe.salesCanvas) check(probe.sLabels > 0, 'trend drawn without labels');
    if (EXPECT_DATA && probe.statusCanvas) {
      const sliceCount = Array.isArray(probe.stSlices) ? probe.stSlices.length : Object.keys(probe.stSlices || {}).length;
      check(sliceCount > 0, 'doughnut drawn with no slices');
    }
    return probe;
  };

  const clickPill = async (key) => {
    await click(`[...document.querySelectorAll('button')].filter(b => (b.getAttribute('wire:click') || '').includes('${key}')).find(b => (b.getAttribute('wire:click') || '').includes('setPeriod(')).click()`);
    await sleep(1200);
  };
  const waitActive = async (key) => {
    for (let i = 0; i < 15; i++) {
      const now = await evalJs(`${PILL_JS}.find(b => (b.className || '').includes('bg-brand-600'))?.getAttribute('wire:click') || ''`);
      if ((now || '').includes(`'${key}'`)) return;
      await sleep(400);
    }
    failures.push({ step: `set period ${key}`, msg: 'period never became active' });
  };

  await clickPill('all');
  await waitActive('all');
  let ok = false;
  for (let i = 0; i < 25; i++) {
    if (await evalJs(`!!document.getElementById('statusChart') && !!window.__dashCharts?.st && !!window.__dashCharts?.s && (window.__dashCharts.st.data.datasets[0].data.length > 0 || !${EXPECT_DATA})`)) { ok = true; break; }
    await sleep(400);
  }
  if (!ok) { failures.push({ step: 'establish all', msg: 'charts never presented data on period "all"' }); }
  await visit('all with data');

  // The reported bug: click the active period twice. Must be a no-op.
  let requests = 0;
  cdp.onEvent = (e) => {
    if (e.method === 'Network.responseReceived' && e.params.response.url.includes('/livewire/update')) requests++;
  };
  await click(`((${PILL_JS}.find(b => (b.className || '').includes('bg-brand-600'))).click())`);
  await click(`((${PILL_JS}.find(b => (b.className || '').includes('bg-brand-600'))).click())`);
  await sleep(800);
  const fired = requests;
  cdp.onEvent = null;
  stepLog.push('active pill clicked twice');
  if (fired > 0) {
    failures.push({ step: 'active pill clicked twice', msg: `a livewire request fired (${fired})`, probe: await evalJs(PROBE) });
  }
  await visit('active pill clicked twice');

  // Same-filter roundtrip that still reaches the server (e.g. a select): the
  // in-place morph must leave the canvases untouched (wire:ignore).
  const idJs = `window.Livewire.find(document.querySelector('[wire\\\\:id]').getAttribute('wire:id'))`;
  for (let i = 0; i < 2; i++) {
    await click(`${idJs}.set('period', 'all')`);
    await sleep(1400);
    await visit(`same-filter via server roundtrip #${i + 1}`);
  }

  await clickPill('week');
  await waitActive('week');
  await visit('week (B in A->B->A)');
  await clickPill('all');
  await waitActive('all');
  await visit('back to all (A in A->B->A)');

  await clickPill('today');
  await waitActive('today');
  await sleep(1200);
  const today = await visit('today (no-data placeholder expected if the store is empty today)');
  const placeholderLingering = today.statusCanvas && today.placeholders > 0;
  if (placeholderLingering) {
    failures.push({ step: 'today placeholder', msg: 'doughnut canvas and its "no data" placeholder co-exist — wire:ignore is blocking the placeholder swap', probe: today });
  }

  await click(`[${PILL_JS}.find(b => (b.getAttribute('wire:click') || '').includes('month')).click(), ${PILL_JS}.find(b => (b.getAttribute('wire:click') || '').includes('month')).click(), ${PILL_JS}.find(b => (b.getAttribute('wire:click') || '').includes('month')).click()]`);
  await sleep(1800);
  await visit('rapid triple click on month');

  await clickPill('all');
  await waitActive('all');
  await click(`document.querySelector('button[wire\\\\:click="resetFilters"]')?.click()`);
  await sleep(1400);
  await click(`document.querySelector('button[wire\\\\:click="resetFilters"]')?.click()`);
  await sleep(1400);
  await visit('reset twice');

  await click(`document.documentElement.classList.toggle('dark')`);
  await sleep(700);
  await visit('theme toggled dark');
  await click(`document.documentElement.classList.toggle('dark')`);
  await sleep(700);
  await visit('theme back to light');
}

async function main() {
  const jar = await login();
  const profile = mkdtempSync(join(tmpdir(), 'edz-charts-'));
  const { child, port } = launchChrome(profile);

  let cdp;
  try {
    const target = await cdpTarget(port);
    cdp = new CDP(target.webSocketDebuggerUrl);
    await cdp.open();
    const jsErrors = [];
    cdp.onEvent = (e) => {
      if (e.method === 'Runtime.consoleAPICalled' && e.params.type === 'error') {
        jsErrors.push(e.params.args?.map(a => a.value ?? a.description ?? '').join(' ').slice(0, 200));
      }
      if (e.method === 'Runtime.exceptionThrown') {
        jsErrors.push((e.params.exceptionDetails.exception?.description ?? e.params.exceptionDetails.text).slice(0, 200));
      }
    };
    await cdp.send('Runtime.enable');
    await cdp.send('Page.enable');
    await cdp.send('Log.enable');
    await cdp.send('Network.enable');

    for (const [k, v] of Object.entries(jar)) {
      await cdp.send('Network.setCookie', { name: k, value: v, domain: new URL(BASE).hostname, path: '/', httpOnly: k === 'edzeery-session', secure: false });
    }

    for (const md of ['confirmation', 'delivery']) {
      stepLog.push(`=== view ${md} ===`);
      await runView(cdp, md);
    }

    if (jsErrors.length) failures.push({ step: 'console', msg: `JS errors: ${JSON.stringify(jsErrors.slice(0, 10))}` });

    console.log(JSON.stringify({
      base: BASE, store: SLUG, expectData: EXPECT_DATA,
      checks: stepLog, passed: failures.length === 0,
      failures,
    }, null, 2));

    cdp.close();
    process.exit(failures.length === 0 ? 0 : 1);
  } catch (e) {
    console.error('FATAL', e);
    process.exit(1);
  } finally {
    try { child.kill(); } catch { /* already gone */ }
    try { rmSync(profile, { recursive: true, force: true }); } catch { /* cleanup best effort */ }
  }
}

main();