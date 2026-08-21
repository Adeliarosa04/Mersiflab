/* ============================================================
   MersifLab — Homepage interactions
   ------------------------------------------------------------
   - Scroll reveal (fade-up) via IntersectionObserver
   - Animated statistics counters (run once, on enter viewport)
   - Search UX: trim/guard empty submits + loading state
   - Thumbnail load states for course preview cards
   All behaviour degrades gracefully and respects
   prefers-reduced-motion.
   ============================================================ */
(function () {
    'use strict';

    var prefersReducedMotion = window.matchMedia &&
        window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* --------------------------------------------------------
       Scroll reveal
       -------------------------------------------------------- */
    function initReveal() {
        var items = document.querySelectorAll('.ml-reveal');
        if (!items.length) return;

        // No observer support (or motion disabled): show everything at once.
        if (prefersReducedMotion || !('IntersectionObserver' in window)) {
            items.forEach(function (el) { el.classList.add('is-visible'); });
            return;
        }

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                var el = entry.target;
                var delay = parseInt(el.dataset.revealDelay || '0', 10);
                setTimeout(function () { el.classList.add('is-visible'); }, delay);
                observer.unobserve(el);
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

        items.forEach(function (el) { observer.observe(el); });
    }

    /* --------------------------------------------------------
       Animated counters
       -------------------------------------------------------- */
    function formatNumber(value, decimals) {
        return value.toLocaleString('en-US', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        });
    }

    function runCounter(el) {
        var target = parseFloat(el.dataset.count || '0');
        var decimals = parseInt(el.dataset.decimals || '0', 10);

        if (prefersReducedMotion || !isFinite(target)) {
            el.textContent = formatNumber(target, decimals);
            return;
        }

        var duration = 1600;
        var start = null;

        function step(timestamp) {
            if (start === null) start = timestamp;
            var progress = Math.min((timestamp - start) / duration, 1);
            // easeOutExpo — fast start, gentle landing
            var eased = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
            el.textContent = formatNumber(target * eased, decimals);
            if (progress < 1) window.requestAnimationFrame(step);
        }

        window.requestAnimationFrame(step);
    }

    function initCounters() {
        var counters = document.querySelectorAll('[data-count]');
        if (!counters.length) return;

        if (!('IntersectionObserver' in window)) {
            counters.forEach(runCounter);
            return;
        }

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                runCounter(entry.target);
                observer.unobserve(entry.target); // animate once only
            });
        }, { threshold: 0.4 });

        counters.forEach(function (el) { observer.observe(el); });
    }

    /* --------------------------------------------------------
       Course search
       -------------------------------------------------------- */
    function initSearch() {
        var form = document.getElementById('mlCourseSearchForm');
        if (!form) return;

        var input = form.querySelector('.ml-search-input');
        var submit = form.querySelector('.ml-search-submit');
        var list = document.getElementById('mlSearchSuggestions');
        var status = document.getElementById('mlSearchStatus');
        var suggestUrl = form.dataset.suggestUrl;

        var results = [];     // current suggestions
        var activeIndex = -1; // highlighted row; -1 = nothing chosen
        var allUrl = '';      // "see all results" target
        var debounceId = null;
        var requestToken = 0; // guards against out-of-order responses

        function escapeHtml(value) {
            return String(value).replace(/[&<>"']/g, function (ch) {
                return {
                    '&': '&amp;', '<': '&lt;', '>': '&gt;',
                    '"': '&quot;', "'": '&#39;'
                }[ch];
            });
        }

        // Wrap the typed part of a title so the match is visible
        function highlight(text, term) {
            var safe = escapeHtml(text);
            if (!term) return safe;

            var pattern = term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            return safe.replace(new RegExp('(' + pattern + ')', 'gi'), '<mark>$1</mark>');
        }

        function closeList() {
            list.hidden = true;
            list.innerHTML = '';
            input.setAttribute('aria-expanded', 'false');
            input.removeAttribute('aria-activedescendant');
            results = [];
            activeIndex = -1;
        }

        // scrollList hanya true saat pengguna menavigasi dengan panah keyboard.
        // Tanpa penjaga ini, scrollIntoView di bawah bisa menggeser halaman
        // pada saat daftar saran dirender ulang (yaitu setiap kali mengetik).
        function setActive(index, scrollList) {
            var rows = list.querySelectorAll('.ml-suggest-item');
            if (!rows.length) return;

            // Wrap around both ends
            if (index < 0) index = rows.length - 1;
            if (index >= rows.length) index = 0;
            activeIndex = index;

            rows.forEach(function (row, i) {
                var on = i === index;
                row.classList.toggle('is-active', on);
                row.setAttribute('aria-selected', on ? 'true' : 'false');
                if (on) {
                    input.setAttribute('aria-activedescendant', row.id);
                    if (scrollList && row.scrollIntoView) {
                        row.scrollIntoView({ block: 'nearest' });
                    }
                }
            });
        }

        // Where should Enter / a click take us?
        function targetUrl(index) {
            var rows = list.querySelectorAll('.ml-suggest-item');
            var row = rows[index];
            return row ? row.dataset.url : '';
        }

        function render(term) {
            var html = '';
            var optionIndex = 0;
            var matches = 0;

            // `results` is a flat list of {kind:'heading'} / {kind:'item'} rows,
            // so group headings and arrow-key navigation stay in sync.
            results.forEach(function (row) {
                if (row.kind === 'heading') {
                    html += '<li class="ml-suggest-heading" role="presentation">' +
                        escapeHtml(row.label) + '</li>';
                    return;
                }

                matches++;
                var thumb = row.image
                    ? '<img src="' + escapeHtml(row.image) + '" alt="" loading="lazy">'
                    : '<i class="fas ' + escapeHtml(row.icon || 'fa-file-lines') + '" aria-hidden="true"></i>';

                html += '<li class="ml-suggest-item" id="ml-suggest-' + optionIndex + '" role="option"' +
                    ' aria-selected="false" data-url="' + escapeHtml(row.url) + '">' +
                    '<span class="ml-suggest-thumb ml-suggest-thumb--' + escapeHtml(row.type) + '">' +
                    thumb + '</span>' +
                    '<span class="ml-suggest-text">' +
                    '<span class="ml-suggest-title">' + highlight(row.title, term) + '</span>' +
                    '<span class="ml-suggest-meta">' + escapeHtml(row.subtitle || '') + '</span>' +
                    '</span></li>';
                optionIndex++;
            });

            if (!matches) {
                html = '<li class="ml-suggest-empty">Nothing matches "' + escapeHtml(term) + '"</li>';
            }

            // Always offer the full results page as the last option
            html += '<li class="ml-suggest-item ml-suggest-all" id="ml-suggest-' + optionIndex +
                '" role="option" aria-selected="false" data-url="' + escapeHtml(allUrl) + '">' +
                '<i class="fas fa-magnifying-glass" aria-hidden="true"></i>' +
                '<span>' + (matches ? 'See all results for' : 'Search the whole site for') +
                ' "' + escapeHtml(term) + '"</span></li>';

            list.innerHTML = html;
            list.hidden = false;
            input.setAttribute('aria-expanded', 'true');

            // JANGAN pra-pilih saran apa pun.
            //
            // Sebelumnya di sini ada setActive(0), dan itu punya dua efek yang
            // tidak diinginkan setiap kali pengguna mengetik:
            //   1. setActive() memanggil row.scrollIntoView(), sehingga halaman
            //      ikut ter-scroll/melompat pada tiap ketukan tombol.
            //   2. activeIndex jadi 0, sehingga handler submit menganggap ada
            //      saran yang "dipilih" lalu langsung window.location.href ke
            //      saran pertama - bukan ke halaman hasil pencarian.
            //
            // Dengan activeIndex tetap -1, mengetik hanya menampilkan daftar
            // saran. Navigasi baru terjadi kalau pengguna benar-benar memilih
            // (panah atas/bawah lalu Enter, atau klik) atau menekan Search.
            activeIndex = -1;
            input.removeAttribute('aria-activedescendant');

            if (status) {
                status.textContent = matches
                    ? matches + ' suggestions available'
                    : 'No results for ' + term;
            }
        }

        function fetchSuggestions(term) {
            var token = ++requestToken;
            form.classList.add('is-searching');

            fetch(suggestUrl + '?q=' + encodeURIComponent(term), {
                headers: { 'Accept': 'application/json' }
            })
                .then(function (response) {
                    if (!response.ok) throw new Error('Request failed');
                    return response.json();
                })
                .then(function (data) {
                    if (token !== requestToken) return; // a newer keystroke won
                    form.classList.remove('is-searching');
                    results = data.items || [];
                    allUrl = data.all_url || '';
                    render(term);
                })
                .catch(function () {
                    if (token !== requestToken) return;
                    // Suggestions are an enhancement — on failure just fall back
                    // to submitting the form normally.
                    form.classList.remove('is-searching');
                    closeList();
                });
        }

        input.addEventListener('input', function () {
            input.classList.remove('is-empty');
            var term = input.value.trim();

            window.clearTimeout(debounceId);

            if (term.length < 2) {
                closeList();
                return;
            }

            debounceId = window.setTimeout(function () {
                fetchSuggestions(term);
            }, 220);
        });

        input.addEventListener('keydown', function (event) {
            if (list.hidden) return;

            if (event.key === 'ArrowDown') {
                event.preventDefault();
                // true = boleh scroll, karena ini navigasi keyboard yang disengaja.
                setActive(activeIndex + 1, true);
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                setActive(activeIndex - 1, true);
            } else if (event.key === 'Escape') {
                closeList();
            }
        });

        // Mousedown, not click: it fires before the input's blur closes the list
        list.addEventListener('mousedown', function (event) {
            var row = event.target.closest('.ml-suggest-item');
            if (!row || !row.dataset.url) return;
            event.preventDefault();
            window.location.href = row.dataset.url;
        });

        list.addEventListener('mousemove', function (event) {
            var row = event.target.closest('.ml-suggest-item');
            if (!row) return;
            var rows = Array.prototype.slice.call(list.querySelectorAll('.ml-suggest-item'));
            setActive(rows.indexOf(row));
        });

        document.addEventListener('click', function (event) {
            if (!form.contains(event.target)) closeList();
        });

        form.addEventListener('submit', function (event) {
            var value = input.value.trim();
            input.value = value;

            // An empty query would just reload the catalogue with a blank
            // filter — nudge the user instead of navigating.
            if (!value) {
                event.preventDefault();
                input.focus();
                input.classList.add('is-empty');
                return;
            }

            // A suggestion is highlighted -> go straight to that course
            // rather than the filtered catalogue page.
            var url = list.hidden ? '' : targetUrl(activeIndex);
            if (url) {
                event.preventDefault();
                if (submit) submit.classList.add('is-loading');
                window.location.href = url;
                return;
            }

            if (submit) submit.classList.add('is-loading');
        });
    }

    /* --------------------------------------------------------
       Course preview: category filter
       -------------------------------------------------------- */
    function initCategoryFilter() {
        var select = document.getElementById('courseCategorySelect');
        var container = document.getElementById('coursePreviewTabContent');
        if (!select || !container) return;

        var panes = container.querySelectorAll('.tab-pane');

        select.addEventListener('change', function () {
            panes.forEach(function (pane) {
                pane.classList.remove('show', 'active');
            });

            var selected = document.getElementById(select.value);
            if (!selected || !container.contains(selected)) return;

            selected.classList.add('show', 'active');

            // Cards in a previously hidden pane never tripped the reveal
            // observer, so show them straight away.
            selected.querySelectorAll('.ml-reveal').forEach(function (el) {
                el.classList.add('is-visible');
            });
        });
    }

    /* --------------------------------------------------------
       Preview thumbnails: skeleton until the image resolves
       -------------------------------------------------------- */
    function initThumbnails() {
        document.querySelectorAll('.ml-preview-thumb img').forEach(function (img) {
            var wrap = img.closest('.ml-preview-thumb');

            function settle() {
                img.classList.add('is-loaded');
                if (wrap) wrap.classList.remove('is-loading');
            }

            if (img.complete && img.naturalWidth > 0) {
                settle();
            } else {
                img.addEventListener('load', settle);
                img.addEventListener('error', function () {
                    if (wrap) wrap.classList.remove('is-loading');
                });
            }
        });
    }

    function init() {
        initReveal();
        initCounters();
        initSearch();
        initCategoryFilter();
        initThumbnails();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
