(() => {
    const DEFAULT_SUGGESTIONS = [
        "Romance Kerajaan",
        "Cewek Kuat",
        "Isekai Ringan",
        "Cute",
        "Overpower",
    ];

    const debounce = (fn, delay = 250) => {
        let timeoutId = null;

        return (...args) => {
            window.clearTimeout(timeoutId);
            timeoutId = window.setTimeout(() => fn(...args), delay);
        };
    };

    const escapeHtml = (value) =>
        String(value)
            .replaceAll("&", "&amp;")
            .replaceAll("<", "&lt;")
            .replaceAll(">", "&gt;")
            .replaceAll('"', "&quot;")
            .replaceAll("'", "&#39;");

    const createChipMarkup = (query) =>
        `<button type="button" class="search-ai-dropdown__chip" data-search-chip="${escapeHtml(query)}">${escapeHtml(query)}</button>`;

    const createResultMarkup = (item) => {
        const tags = Array.isArray(item.genres) ? item.genres.slice(0, 2) : [];
        const metaStatus = item.status ? escapeHtml(item.status) : "";
        const metaChapter = `${Number(item.chapter_count || 0)} chapter`;

        return `
            <a class="search-ai-dropdown__result" href="${escapeHtml(item.url)}">
                <span class="search-ai-dropdown__thumb">
                    <img src="${escapeHtml(item.cover_url)}" alt="${escapeHtml(item.judul)}" loading="lazy">
                </span>
                <span class="search-ai-dropdown__result-copy">
                    <strong>${escapeHtml(item.judul)}</strong>
                    <span class="search-ai-dropdown__tags">
                        ${tags.map((tag) => `<span>${escapeHtml(tag)}</span>`).join("")}
                    </span>
                    <span class="search-ai-dropdown__result-bottom">
                        <span>${escapeHtml(metaChapter)}</span>
                        ${metaStatus ? `<span>${metaStatus}</span>` : ""}
                    </span>
                </span>
                <span class="link-button button--small search-ai-dropdown__open">Buka</span>
            </a>
        `;
    };

    const initSearchAi = (form) => {
        const input = form.querySelector("[data-search-ai-input]");
        const panel = form.querySelector("[data-search-ai-panel]");
        const status = form.querySelector("[data-search-ai-status]");
        const provider = form.querySelector("[data-search-ai-provider]");
        const loading = form.querySelector("[data-search-ai-loading]");
        const results = form.querySelector("[data-search-ai-results]");
        const empty = form.querySelector("[data-search-ai-empty]");
        const chips = form.querySelector("[data-search-ai-chips]");
        const endpoint = form.dataset.searchEndpoint || "";

        if (!input || !panel || !status || !provider || !loading || !results || !empty || !chips || !endpoint) {
            return;
        }

        let controller = null;
        let activeRequestId = 0;
        const resultCache = new Map();

        const openPanel = () => {
            panel.hidden = false;
            form.classList.add("is-active");
        };

        const closePanel = () => {
            panel.hidden = true;
            form.classList.remove("is-active");
        };

        const renderSuggestions = (items) => {
            chips.innerHTML = items.map(createChipMarkup).join("");
        };

        const setLoading = (isLoading) => {
            loading.hidden = !isLoading;
        };

        const renderResults = (items) => {
            results.innerHTML = items.map(createResultMarkup).join("");
        };

        const renderEmpty = (visible) => {
            empty.hidden = !visible;
        };

        const renderIdle = () => {
            status.textContent = 'Coba cari dengan bahasa bebas, misalnya "romance kerajaan cewek kuat".';
            provider.textContent = "AI";
            renderSuggestions(DEFAULT_SUGGESTIONS);
            renderResults([]);
            renderEmpty(false);
            setLoading(false);
        };

        const applyPayload = (payload, query) => {
            status.textContent = payload.status_text || `Memahami: ${query}`;
            provider.textContent = String(payload.provider || "AI").toUpperCase();
            renderSuggestions(Array.isArray(payload.suggestions) && payload.suggestions.length > 0 ? payload.suggestions.slice(0, 5) : DEFAULT_SUGGESTIONS);

            if (Array.isArray(payload.items) && payload.items.length > 0) {
                renderResults(payload.items);
                renderEmpty(false);
            } else {
                renderResults([]);
                renderEmpty(true);
            }
        };

        const fetchIdleSuggestions = async () => {
            if (controller) {
                controller.abort();
            }

            controller = new AbortController();
            const requestId = ++activeRequestId;

            try {
                const response = await fetch(`${endpoint}&q=`, {
                    headers: {
                        Accept: "application/json",
                    },
                    signal: controller.signal,
                });

                if (!response.ok) {
                    throw new Error("Idle suggestion request failed");
                }

                const payload = await response.json();
                if (requestId !== activeRequestId) {
                    return;
                }

                status.textContent = payload.status_text || 'Coba cari dengan bahasa bebas, misalnya "romance kerajaan cewek kuat".';
                provider.textContent = String(payload.provider || "AI").toUpperCase();
                renderSuggestions(Array.isArray(payload.suggestions) && payload.suggestions.length > 0 ? payload.suggestions.slice(0, 5) : DEFAULT_SUGGESTIONS);
            } catch (error) {
                if (error.name !== "AbortError" && requestId === activeRequestId) {
                    renderSuggestions(DEFAULT_SUGGESTIONS);
                }
            }
        };

        const fetchResults = async (query) => {
            const normalizedQuery = query.trim();
            if (normalizedQuery.length < 2) {
                renderIdle();
                return;
            }

            if (controller) {
                controller.abort();
            }

            controller = new AbortController();
            const requestId = ++activeRequestId;
            setLoading(true);
            renderEmpty(false);
            status.textContent = `Memahami: ${normalizedQuery}`;

            if (resultCache.has(normalizedQuery)) {
                applyPayload(resultCache.get(normalizedQuery), normalizedQuery);
                setLoading(false);
                return;
            }

            results.innerHTML = "";

            try {
                const response = await fetch(`${endpoint}&q=${encodeURIComponent(normalizedQuery)}`, {
                    headers: {
                        Accept: "application/json",
                    },
                    signal: controller.signal,
                });

                if (!response.ok) {
                    throw new Error("Search request failed");
                }

                const payload = await response.json();
                if (requestId !== activeRequestId) {
                    return;
                }

                resultCache.set(normalizedQuery, payload);
                applyPayload(payload, normalizedQuery);
            } catch (error) {
                if (error.name !== "AbortError" && requestId === activeRequestId) {
                    status.textContent = "AI search sedang tidak tersedia. Coba lagi sebentar.";
                    provider.textContent = "LOCAL";
                    renderResults([]);
                    renderEmpty(true);
                    renderSuggestions(DEFAULT_SUGGESTIONS);
                }
            } finally {
                if (requestId === activeRequestId) {
                    setLoading(false);
                }
            }
        };

        const debouncedFetch = debounce((value) => {
            if (value.trim().length < 2) {
                renderIdle();
                return;
            }

            fetchResults(value.trim());
        }, 250);

        renderIdle();

        input.addEventListener("focus", () => {
            openPanel();
            if (input.value.trim().length >= 2) {
                fetchResults(input.value);
            } else {
                renderIdle();
                fetchIdleSuggestions();
            }
        });

        input.addEventListener("input", () => {
            openPanel();
            debouncedFetch(input.value);
        });

        form.addEventListener("click", (event) => {
            const chip = event.target.closest("[data-search-chip]");
            if (!chip) {
                return;
            }

            input.value = chip.dataset.searchChip || "";
            openPanel();
            fetchResults(input.value);
            input.focus();
        });

        document.addEventListener("click", (event) => {
            if (!form.contains(event.target)) {
                closePanel();
            }
        });
    };

    document.addEventListener("DOMContentLoaded", () => {
        document.querySelectorAll("[data-search-ai]").forEach(initSearchAi);
    });
})();
