/*!
 * PRISMA Launcher Widget — bundle IIFE, sem dependências externas.
 * Embutido via: <script src="https://prisma.app/launcher/embed" data-user-id="UUID" data-api-key="TOKEN" async></script>
 * Shadow DOM (mode: closed) isola completamente o CSS do host — sem vazamento em nenhuma direção.
 */
(function () {
    'use strict';

    var CURRENT_SCRIPT = document.currentScript;
    if (!CURRENT_SCRIPT) return;

    var USER_ID  = CURRENT_SCRIPT.getAttribute('data-user-id') || '';
    var API_KEY  = CURRENT_SCRIPT.getAttribute('data-api-key') || '';
    var API_BASE = (function () {
        // Deriva a base da API a partir do próprio <script src>, preservando eventual
        // subpasta de instalação (ex: http://host/prisma/launcher/embed -> http://host/prisma).
        try {
            var u = new URL(CURRENT_SCRIPT.src);
            return u.origin + u.pathname.replace(/\/launcher\/embed\/?$/, '');
        } catch (e) { return ''; }
    })();

    if (!USER_ID || !API_KEY || !API_BASE) return;

    var INDEX_URL = API_BASE + '/launcher/index?uid=' + encodeURIComponent(USER_ID) + '&key=' + encodeURIComponent(API_KEY);
    var TRACK_URL = API_BASE + '/launcher/track';

    // ── Estado ──────────────────────────────────────────────────────────
    var indexCache = null;   // { generated_at, links: [...] }
    var indexPromise = null;
    var isOpen = false;

    // ── Bitap fuzzy search (máx. 1 erro) ───────────────────────────────
    // Fast-path: Shift-Or exato (bitap clássico). Fallback: distância de edição
    // limitada em janela deslizante para tolerar até `maxErrors` erros.
    function bitapExact(text, pattern) {
        var m = pattern.length;
        if (m === 0 || m > 31) return text.indexOf(pattern) !== -1;

        var mask = {};
        for (var i = 0; i < m; i++) {
            var c = pattern[i];
            mask[c] = (mask[c] === undefined ? ~0 : mask[c]) & ~(1 << i);
        }

        var R = ~1;
        var matchBit = 1 << (m - 1);

        for (var j = 0; j < text.length; j++) {
            var cm = mask[text[j]];
            if (cm === undefined) cm = ~0;
            R = (R | cm) << 1;
            if ((R & matchBit) === 0) return true;
        }
        return false;
    }

    function editDistance(a, b, cutoff) {
        var al = a.length, bl = b.length;
        if (Math.abs(al - bl) > cutoff) return cutoff + 1;

        var prev = [];
        for (var j = 0; j <= bl; j++) prev[j] = j;

        for (var i = 1; i <= al; i++) {
            var cur = [i];
            for (var jj = 1; jj <= bl; jj++) {
                var cost = a[i - 1] === b[jj - 1] ? 0 : 1;
                cur[jj] = Math.min(prev[jj] + 1, cur[jj - 1] + 1, prev[jj - 1] + cost);
            }
            prev = cur;
        }
        return prev[bl];
    }

    function bitapSearch(text, pattern, maxErrors) {
        maxErrors = maxErrors === undefined ? 1 : maxErrors;
        text = text.toLowerCase();
        pattern = pattern.toLowerCase();

        if (pattern.length === 0) return { matched: true, score: 0.05 };

        if (bitapExact(text, pattern)) {
            var idx = text.indexOf(pattern);
            var prefixBonus = idx === 0 ? 0.15 : 0;
            return { matched: true, score: Math.min(1, 0.8 + prefixBonus) };
        }

        if (maxErrors <= 0 || pattern.length < 3) return { matched: false, score: 0 };

        var n = text.length, m = pattern.length, best = maxErrors + 1;
        var minLen = Math.max(1, m - maxErrors);
        var maxLen = m + maxErrors;

        for (var start = 0; start <= n - minLen && best > 0; start++) {
            for (var len = minLen; len <= maxLen && start + len <= n; len++) {
                var d = editDistance(text.substr(start, len), pattern, best);
                if (d < best) best = d;
                if (best === 0) break;
            }
        }

        if (best <= maxErrors) {
            return { matched: true, score: Math.max(0.15, 0.6 - best * 0.2) };
        }
        return { matched: false, score: 0 };
    }

    function rankResults(items, query) {
        if (!query) {
            return items.slice().sort(function (a, b) {
                return b.use_count - a.use_count;
            }).slice(0, 8);
        }

        var scored = [];
        for (var i = 0; i < items.length; i++) {
            var item = items[i];
            var titleMatch = bitapSearch(item.title || '', query, 1);
            var urlMatch = bitapSearch(item.url || '', query, 1);
            var tagMatch = { matched: false, score: 0 };
            if (item.tags && item.tags.length) {
                for (var t = 0; t < item.tags.length; t++) {
                    var m = bitapSearch(String(item.tags[t]), query, 1);
                    if (m.score > tagMatch.score) tagMatch = m;
                }
            }

            var best = Math.max(titleMatch.score, urlMatch.score * 0.6, tagMatch.score * 0.8);
            if (best <= 0) continue;

            var recencyBoost = 0;
            if (item.last_used_at) {
                var hoursAgo = (Date.now() - new Date(item.last_used_at).getTime()) / 3600000;
                if (hoursAgo >= 0 && hoursAgo <= 2) recencyBoost = 0.1;
            }
            var useBoost = Math.min(0.2, (item.use_count || 0) * 0.01);

            scored.push({ item: item, rank: best + recencyBoost + useBoost });
        }

        scored.sort(function (a, b) { return b.rank - a.rank; });
        return scored.slice(0, 8).map(function (s) { return s.item; });
    }

    // ── Rede ────────────────────────────────────────────────────────────
    function loadIndex() {
        if (indexPromise) return indexPromise;
        indexPromise = fetch(INDEX_URL)
            .then(function (r) { if (!r.ok) throw new Error('http ' + r.status); return r.json(); })
            .then(function (data) { indexCache = data; return data; })
            .catch(function (err) { indexPromise = null; throw err; });
        return indexPromise;
    }

    function track(item) {
        var body = new URLSearchParams({
            uid: USER_ID,
            key: API_KEY,
            source: item.source || 'manual',
            source_id: item.source_id != null ? String(item.source_id) : '',
            title: item.title || '',
            url: item.url || '',
            icon: item.icon || '',
        });
        fetch(TRACK_URL, { method: 'POST', body: body }).catch(function () {});
    }

    // ── UI (Shadow DOM) ─────────────────────────────────────────────────
    var host = document.createElement('div');
    host.id = 'prisma-launcher-host';
    host.style.all = 'initial';
    document.addEventListener('DOMContentLoaded', mount);
    if (document.readyState === 'interactive' || document.readyState === 'complete') mount();

    function mount() {
        if (host.isConnected) return;
        document.body.appendChild(host);
    }

    var shadow = host.attachShadow({ mode: 'closed' });

    var style = document.createElement('style');
    style.textContent = [
        ':host, *{ box-sizing: border-box; }',
        '.pl-trigger {',
        '  position: fixed; bottom: 24px; right: 24px; z-index: 2147483000;',
        '  width: 52px; height: 52px; border-radius: 50%; border: none; cursor: pointer;',
        '  background: linear-gradient(135deg, #2E86AB 0%, #1A5276 100%);',
        '  box-shadow: 0 4px 16px rgba(0,0,0,.4); display: flex; align-items: center; justify-content: center;',
        '  transition: transform .15s ease;',
        '}',
        '.pl-trigger:hover { transform: scale(1.06); }',
        '.pl-trigger svg { width: 24px; height: 24px; fill: #E8F4F8; }',
        '.pl-overlay {',
        '  position: fixed; inset: 0; z-index: 2147483001; background: rgba(13,27,42,.55);',
        '  display: none; align-items: flex-start; justify-content: center; padding-top: 10vh;',
        '  font-family: system-ui, -apple-system, sans-serif;',
        '}',
        '.pl-overlay.open { display: flex; }',
        '.pl-panel {',
        '  width: min(520px, 92vw); max-height: 60vh; display: flex; flex-direction: column;',
        '  background: #152233; border: 1px solid #1E3A5F; border-radius: 16px;',
        '  box-shadow: 0 8px 32px rgba(0,0,0,.5); overflow: hidden;',
        '}',
        '.pl-search {',
        '  padding: 14px 16px; border-bottom: 1px solid #1E3A5F; display: flex; align-items: center; gap: 8px;',
        '}',
        '.pl-search svg { width: 18px; height: 18px; fill: #4E6B87; flex-shrink: 0; }',
        '.pl-search input {',
        '  flex: 1; background: transparent; border: none; outline: none; color: #E8F4F8; font-size: 15px;',
        '}',
        '.pl-search input::placeholder { color: #4E6B87; }',
        '.pl-close { background: none; border: none; color: #4E6B87; cursor: pointer; font-size: 18px; line-height: 1; padding: 4px; }',
        '.pl-results { overflow-y: auto; padding: 8px; }',
        '.pl-item {',
        '  display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 8px;',
        '  cursor: pointer; color: #E8F4F8; text-decoration: none;',
        '}',
        '.pl-item:hover, .pl-item.active { background: rgba(46,134,171,.15); }',
        '.pl-item-icon { width: 28px; height: 28px; border-radius: 6px; background: #1A2E44; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }',
        '.pl-item-icon svg { width: 14px; height: 14px; fill: #2E86AB; }',
        '.pl-item-body { min-width: 0; flex: 1; }',
        '.pl-item-title { font-size: 13.5px; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }',
        '.pl-item-url { font-size: 11.5px; color: #94A3B8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }',
        '.pl-empty { padding: 32px 16px; text-align: center; color: #4E6B87; font-size: 13px; }',
        '.pl-footer { padding: 8px 16px; border-top: 1px solid #1E3A5F; color: #4E6B87; font-size: 11px; display: flex; justify-content: space-between; }',
    ].join('\n');
    shadow.appendChild(style);

    var ICON_BOLT = '<svg viewBox="0 0 24 24"><path d="M13 2 3 14h7l-1 8 10-12h-7l1-8z"/></svg>';
    var ICON_SEARCH = '<svg viewBox="0 0 24 24"><path d="M15.5 14h-.79l-.28-.27a6.5 6.5 0 1 0-.7.7l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0A4.5 4.5 0 1 1 14 9.5 4.5 4.5 0 0 1 9.5 14z"/></svg>';
    var ICON_LINK = '<svg viewBox="0 0 24 24"><path d="M3.9 12a5 5 0 0 1 5-5H13v2H8.9a3 3 0 0 0 0 6H13v2H8.9a5 5 0 0 1-5-5zm6.1 1h4v-2h-4v2zm5.1-6H11v2h4.1a3 3 0 0 1 0 6H11v2h4.1a5 5 0 0 0 0-10z"/></svg>';

    var trigger = document.createElement('button');
    trigger.className = 'pl-trigger';
    trigger.type = 'button';
    trigger.title = 'Buscar (Ctrl+K)';
    trigger.innerHTML = ICON_BOLT;
    shadow.appendChild(trigger);

    var overlay = document.createElement('div');
    overlay.className = 'pl-overlay';
    overlay.innerHTML =
        '<div class="pl-panel">' +
        '  <div class="pl-search">' + ICON_SEARCH +
        '    <input type="text" placeholder="Buscar links, favoritos, QR codes..." autocomplete="off">' +
        '    <button class="pl-close" type="button">&times;</button>' +
        '  </div>' +
        '  <div class="pl-results"></div>' +
        '  <div class="pl-footer"><span>PRISMA Launcher</span><span>Ctrl+K</span></div>' +
        '</div>';
    shadow.appendChild(overlay);

    var searchInput = overlay.querySelector('input');
    var resultsEl = overlay.querySelector('.pl-results');
    var closeBtn = overlay.querySelector('.pl-close');
    var activeIndex = -1;
    var currentItems = [];

    function open() {
        isOpen = true;
        overlay.classList.add('open');
        searchInput.value = '';
        searchInput.focus();
        renderLoading();

        loadIndex().then(function (data) {
            currentItems = rankResults(data.links || [], '');
            renderResults(currentItems);
        }).catch(function () {
            resultsEl.innerHTML = '<div class="pl-empty">Não foi possível carregar o índice.</div>';
        });
    }

    function close() {
        isOpen = false;
        overlay.classList.remove('open');
    }

    function renderLoading() {
        resultsEl.innerHTML = '<div class="pl-empty">Carregando…</div>';
    }

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function renderResults(items) {
        activeIndex = items.length ? 0 : -1;
        if (!items.length) {
            resultsEl.innerHTML = '<div class="pl-empty">Nenhum resultado encontrado.</div>';
            return;
        }
        resultsEl.innerHTML = items.map(function (item, i) {
            return '<a class="pl-item' + (i === 0 ? ' active' : '') + '" data-idx="' + i + '" href="' + escapeHtml(item.url) + '" target="_blank" rel="noopener">' +
                '<span class="pl-item-icon">' + ICON_LINK + '</span>' +
                '<span class="pl-item-body">' +
                '<span class="pl-item-title">' + escapeHtml(item.title) + '</span><br>' +
                '<span class="pl-item-url">' + escapeHtml(item.url) + '</span>' +
                '</span></a>';
        }).join('');

        Array.prototype.forEach.call(resultsEl.querySelectorAll('.pl-item'), function (el) {
            el.addEventListener('click', function () {
                var idx = parseInt(el.getAttribute('data-idx'), 10);
                track(currentItems[idx]);
                close();
            });
        });
    }

    var debounceTimer = null;
    searchInput.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        var query = searchInput.value.trim();
        debounceTimer = setTimeout(function () {
            var source = (indexCache && indexCache.links) || [];
            currentItems = rankResults(source, query);
            renderResults(currentItems);
        }, 120);
    });

    searchInput.addEventListener('keydown', function (e) {
        var items = resultsEl.querySelectorAll('.pl-item');
        if (!items.length) return;

        if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
            e.preventDefault();
            items[activeIndex] && items[activeIndex].classList.remove('active');
            activeIndex = e.key === 'ArrowDown'
                ? Math.min(items.length - 1, activeIndex + 1)
                : Math.max(0, activeIndex - 1);
            items[activeIndex].classList.add('active');
            items[activeIndex].scrollIntoView({ block: 'nearest' });
        } else if (e.key === 'Enter') {
            e.preventDefault();
            items[activeIndex] && items[activeIndex].click();
        } else if (e.key === 'Escape') {
            close();
        }
    });

    trigger.addEventListener('click', function () { isOpen ? close() : open(); });
    closeBtn.addEventListener('click', close);
    overlay.addEventListener('click', function (e) { if (e.target === overlay) close(); });

    document.addEventListener('keydown', function (e) {
        var isK = e.key === 'k' || e.key === 'K';
        if ((e.ctrlKey || e.metaKey) && isK) {
            e.preventDefault();
            isOpen ? close() : open();
        } else if (e.key === 'Escape' && isOpen) {
            close();
        }
    });
})();
