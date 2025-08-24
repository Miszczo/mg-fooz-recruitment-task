// Related Books frontend logic per PRD: fetch with timeout+retry, sessionStorage cache (10 min),
// aria-live status updates, and responsive grid rendering.

(() => {
  const SECTION_SELECTOR = '#related-books';
  const STATUS_SELECTOR = '.status';
  const LIST_SELECTOR = '.book-grid';
  const CACHE_TTL_MS = 10 * 60 * 1000; // 10 minutes
  const FETCH_TIMEOUT_MS = 8000; // 8s

  function setStatus(section, message) {
    const el = section.querySelector(STATUS_SELECTOR);
    if (el) el.textContent = message;
  }

  function sleep(ms) {
    return new Promise((resolve) => setTimeout(resolve, ms));
  }

  async function fetchWithTimeout(url, options = {}, timeoutMs = FETCH_TIMEOUT_MS) {
    const controller = new AbortController();
    const id = setTimeout(() => controller.abort(), timeoutMs);
    try {
      const res = await fetch(url, { ...options, signal: controller.signal });
      return res;
    } finally {
      clearTimeout(id);
    }
  }

  async function fetchWithRetry(url, options = {}, retries = 1) {
    try {
      const res = await fetchWithTimeout(url, options);
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      return res;
    } catch (err) {
      if (retries <= 0) throw err;
      const jitter = 500 + Math.floor(Math.random() * 300); // 500–800ms
      await sleep(jitter);
      return fetchWithRetry(url, options, retries - 1);
    }
  }

  function cacheKey(exclude, limit) {
    return `fooz:related-books:${exclude}:${limit}`;
  }

  function readCache(key) {
    try {
      const raw = sessionStorage.getItem(key);
      if (!raw) return null;
      const parsed = JSON.parse(raw);
      if (!parsed || typeof parsed !== 'object') return null;
      if (Date.now() - parsed.time > CACHE_TTL_MS) return null;
      return parsed.value;
    } catch {
      return null;
    }
  }

  function writeCache(key, value) {
    try {
      sessionStorage.setItem(key, JSON.stringify({ time: Date.now(), value }));
    } catch {}
  }

  function renderList(section, items) {
    const list = section.querySelector(LIST_SELECTOR);
    if (!list) return;
    list.innerHTML = '';
    for (const item of items) {
      const card = document.createElement('li');
      card.className = 'book-card';

      const link = document.createElement('a');
      link.href = item.permalink;
      link.className = 'book-card__stretched-link';
      link.setAttribute('aria-hidden', 'true');
      link.tabIndex = -1;
      card.appendChild(link);

      if (item.thumbnailUrl) {
        const img = document.createElement('img');
        img.src = item.thumbnailUrl;
        if (item.thumbnailSrcset) img.srcset = item.thumbnailSrcset;
        if (item.thumbnailSizes) img.sizes = item.thumbnailSizes;
        img.alt = item.title || '';
        img.loading = 'lazy';
        img.decoding = 'async';
        img.className = 'book-card__image';
        const imageLink = document.createElement('a');
        imageLink.href = item.permalink;
        imageLink.tabIndex = -1; // Not keyboard focusable
        imageLink.appendChild(img);
        card.appendChild(imageLink);
      }

      const content = document.createElement('div');
      content.className = 'book-card__content';

      const title = document.createElement('h3');
      title.className = 'book-card__title';
      const titleLink = document.createElement('a');
      titleLink.href = item.permalink;
      titleLink.textContent = item.title;
      // Add sr-only context for screen readers
      const sr = document.createElement('span');
      sr.className = 'sr-only';
      sr.textContent = ' – Related book';
      titleLink.appendChild(sr);
      title.appendChild(titleLink);
      content.appendChild(title);

      const meta = document.createElement('div');
      meta.className = 'book-card__meta';

      const date = new Date(item.date);
      const dateEl = document.createElement('time');
      dateEl.dateTime = date.toISOString();
      dateEl.textContent = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
      meta.appendChild(dateEl);

      if (Array.isArray(item.genres) && item.genres.length) {
        const sep = document.createElement('span');
        sep.textContent = ' · ';
        meta.appendChild(sep);
        const genresSpan = document.createElement('span');
        item.genres.forEach((g, idx) => {
          if (idx > 0) genresSpan.appendChild(document.createTextNode(', '));
          const a = document.createElement('a');
          a.href = g.url;
          a.textContent = g.name;
          genresSpan.appendChild(a);
        });
        meta.appendChild(genresSpan);
      }
      content.appendChild(meta);

      const excerpt = document.createElement('p');
      excerpt.className = 'book-card__excerpt';
      excerpt.textContent = item.excerpt || '';
      content.appendChild(excerpt);

      card.appendChild(content);
      list.appendChild(card);
    }
  }

  async function initRelatedBooks() {
    const section = document.querySelector(SECTION_SELECTOR);
    if (!section) return;
    const currentId = Number(section.getAttribute('data-current-id')) || 0;
    const limit = 20;
    const key = cacheKey(currentId, limit);

    const msg = (TT5C && TT5C.i18n) || {};
    setStatus(section, msg.loading || 'Loading related books…');

    const cached = readCache(key);
    if (cached) {
      renderList(section, cached);
      setStatus(section, '');
      return;
    }

    const url = `/wp-json/fooz/v1/related-books?exclude=${encodeURIComponent(currentId)}&limit=${limit}`;
    try {
      const res = await fetchWithRetry(url, {}, 1);
      const data = await res.json();
      if (!Array.isArray(data) || data.length === 0) {
        setStatus(section, msg.noResults || 'No related books found.');
        return;
      }
      writeCache(key, data);
      renderList(section, data);
      setStatus(section, '');
    } catch (err) {
      setStatus(section, msg.loadFailed || 'Failed to load related books.');
    }
  }

  if (document.readyState === 'complete' || document.readyState === 'interactive') {
    // Wait until after all critical assets are loaded
    window.addEventListener('load', initRelatedBooks, { once: true });
  } else {
    window.addEventListener('DOMContentLoaded', () => {
      window.addEventListener('load', initRelatedBooks, { once: true });
    });
  }
})();


// FAQ accordion behavior unified to wp-block-fooz-faq-* classes
(() => {
  function initFAQUnified() {
    const root = document.querySelector('.wp-block-fooz-faq-accordion');
    if (!root) return;
    const items = Array.from(root.querySelectorAll('.wp-block-fooz-faq-item'));
    const buttons = items.map(it => it.querySelector('.faq-item__question')).filter(Boolean);
    const panels = items.map(it => it.querySelector('.faq-item__answer')).filter(Boolean);
    if (!buttons.length) return;

    function closeAll() {
      buttons.forEach((b, i) => {
        b.setAttribute('aria-expanded', 'false');
      });
    }
    // Init: open first
    closeAll();
    buttons[0].setAttribute('aria-expanded', 'true');

    buttons.forEach((btn, index) => {
      const panel = panels[index];
      const panelId = panel && panel.id ? panel.id : `faq-${index}`;
      btn.setAttribute('aria-controls', panelId);
      if (panel && !panel.id) panel.id = panelId;
      // Set button id for deep-link targets
      if (!btn.id) btn.id = `faq-btn-${index}`;

      btn.addEventListener('click', () => {
        const isOpen = btn.getAttribute('aria-expanded') === 'true';
        closeAll();
        if (!isOpen) {
          btn.setAttribute('aria-expanded', 'true');
        }
      });

      btn.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          btn.click();
        } else if (e.key === 'ArrowDown') {
          e.preventDefault();
          const next = buttons[(index + 1) % buttons.length];
          next && next.focus();
        } else if (e.key === 'ArrowUp') {
          e.preventDefault();
          const prev = buttons[(index - 1 + buttons.length) % buttons.length];
          prev && prev.focus();
        } else if (e.key === 'Home') {
          e.preventDefault();
          buttons[0].focus();
        } else if (e.key === 'End') {
          e.preventDefault();
          buttons[buttons.length - 1].focus();
        }
      });
    });

    // Deep-link: if URL hash matches a panel or button id, open it
    function openByHash() {
      const hash = window.location.hash.replace('#', '');
      if (!hash) return;
      let targetIndex = panels.findIndex(p => p && p.id === hash);
      if (targetIndex === -1) {
        targetIndex = buttons.findIndex(b => b && b.id === hash);
      }
      if (targetIndex >= 0) {
        closeAll();
        const btn = buttons[targetIndex];
        const panel = panels[targetIndex];
        btn.setAttribute('aria-expanded', 'true');
        btn.focus();
      }
    }
    openByHash();
    window.addEventListener('hashchange', openByHash);
  }
  if (document.readyState === 'complete' || document.readyState === 'interactive') {
    initFAQUnified();
  } else {
    document.addEventListener('DOMContentLoaded', initFAQUnified);
  }
})();

// Minimal fallback for Navigation block hamburger (overlayMenu: mobile)
(() => {
  function initNavFallback() {
    const nav = document.querySelector('.wp-block-navigation');
    if (!nav) return;

    const openBtn = nav.querySelector('.wp-block-navigation__responsive-container-open');
    const container = nav.querySelector('.wp-block-navigation__responsive-container');
    if (!openBtn || !container) return;

    // If core script already wired, bail
    if (container.classList.contains('is-menu-open') || openBtn.hasAttribute('data-tt5c-bound')) return;
    openBtn.setAttribute('data-tt5c-bound', '1');

    function closeMenu() {
      container.classList.remove('is-menu-open');
      openBtn.setAttribute('aria-expanded', 'false');
    }
    function openMenu() {
      container.classList.add('is-menu-open');
      openBtn.setAttribute('aria-expanded', 'true');
    }

    openBtn.addEventListener('click', (e) => {
      const expanded = openBtn.getAttribute('aria-expanded') === 'true';
      if (expanded) {
        closeMenu();
      } else {
        openMenu();
      }
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') closeMenu();
    });
  }

  if (document.readyState === 'complete' || document.readyState === 'interactive') {
    initNavFallback();
  } else {
    document.addEventListener('DOMContentLoaded', initNavFallback);
  }
})();

// Set genre name in taxonomy headers via client-side enhancement
(function() {
  const genreNameSpan = document.querySelector('.genre-name');
  if (genreNameSpan) {
    const pageTitle = document.title;
    // Try to find "Genre: {Name}" in the document title
    const genreMatch = pageTitle.match(/Genre:\s*(.+)/);
    if (genreMatch && genreMatch[1]) {
      genreNameSpan.textContent = genreMatch[1];
    }
  }
})();

