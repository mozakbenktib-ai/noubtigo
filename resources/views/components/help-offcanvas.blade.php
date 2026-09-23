{{-- Contextual Help Offcanvas Drawer --}}
<div class="offcanvas offcanvas-end border-0 shadow-2xl" 
     tabindex="-1" 
     id="helpCenterOffcanvas" 
     aria-labelledby="helpCenterOffcanvasLabel" 
     style="width: 540px; max-width: 92vw; z-index: 1080;">
    
    <div class="offcanvas-header border-bottom px-4 py-3 text-white" style="background: var(--primary-gradient);">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-question-circle-fill fs-4"></i>
            <div>
                <h6 class="offcanvas-title fw-bold mb-0" id="helpCenterOffcanvasLabel">Help & Quick Guide</h6>
                <small class="text-white-50" style="font-size: 0.72rem;">Contextual Guidance System</small>
            </div>
        </div>
        <button type="button" class="btn-close btn-close-white shadow-none opacity-75 hover-opacity-100" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body p-4 d-flex flex-column" id="offcanvasHelpBody" style="background-color: var(--bs-body-bg);">
        
        {{-- Search Input inside Offcanvas --}}
        <div class="mb-4 position-relative">
            <div class="input-group input-group-sm rounded-pill overflow-hidden border shadow-sm">
                <span class="input-group-text bg-body border-0 text-muted ps-3"><i class="bi bi-search"></i></span>
                <input type="text" id="offcanvasSearchInput" class="form-control border-0 shadow-none ps-1 bg-body text-body" placeholder="Search guides & FAQs..." autocomplete="off">
            </div>
            <div id="offcanvasSearchResults" class="mt-2 list-group list-group-flush rounded-3 shadow-lg border position-absolute w-100" style="display: none; max-height: 220px; overflow-y: auto; z-index: 1050;"></div>
        </div>

        {{-- Dynamic Article Content --}}
        <div id="offcanvasArticleContainer" class="flex-grow-1 overflow-y-auto pe-1">
            <div class="text-center py-5" id="offcanvasHelpSpinner">
                <div class="spinner-border text-primary spinner-border-sm" role="status">
                    <span class="visually-hidden">Loading guide...</span>
                </div>
                <p class="text-muted small mt-2">Loading page guide...</p>
            </div>

            <div id="offcanvasHelpContent" style="display: none;">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 font-monospace" id="offcanvasHelpCategory" style="font-size: 0.7rem;"></span>
                </div>
                <h5 class="fw-bold text-body mb-3" id="offcanvasHelpTitle"></h5>
                <div class="markdown-body help-drawer-markdown small text-body" id="offcanvasHelpBodyContent" style="line-height: 1.6;"></div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="pt-3 border-top mt-auto text-center">
            <a href="{{ route('help.index') }}" class="btn btn-outline-primary rounded-pill btn-sm w-100 fw-semibold">
                <i class="bi bi-book me-1"></i> Open Full Help Center
            </a>
        </div>
    </div>
</div>

<style>
/* Custom responsive scaling & media embedding inside Help Offcanvas Drawer */
.help-drawer-markdown img {
    max-width: 100% !important;
    height: auto !important;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    margin: 1rem 0;
}
.help-drawer-markdown pre {
    background: rgba(0,0,0,0.04);
    padding: 0.75rem 1rem;
    border-radius: 8px;
    font-size: 0.8rem;
    overflow-x: auto;
}
[data-bs-theme="dark"] .help-drawer-markdown pre {
    background: rgba(255,255,255,0.06);
}
.help-drawer-markdown blockquote {
    border-left: 4px solid var(--primary-color, #22c55e);
    padding-left: 1rem;
    color: #64748b;
    margin: 1rem 0;
}
.help-drawer-markdown .ratio-16x9 {
    border-radius: 12px;
    overflow: hidden;
}
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const offcanvasEl = document.getElementById('helpCenterOffcanvas');
    if (!offcanvasEl) return;

    const currentRouteName = "{{ Route::currentRouteName() ?? 'dashboard' }}";

    offcanvasEl.addEventListener('show.bs.offcanvas', function () {
        const spinner = document.getElementById('offcanvasHelpSpinner');
        const content = document.getElementById('offcanvasHelpContent');
        
        if (spinner) spinner.style.display = 'block';
        if (content) content.style.display = 'none';

        fetch(`/help/api/contextual?route_name=${encodeURIComponent(currentRouteName)}`)
            .then(res => res.json())
            .then(data => {
                if (spinner) spinner.style.display = 'none';
                if (data.success && data.article) {
                    document.getElementById('offcanvasHelpCategory').innerText = data.article.category;
                    document.getElementById('offcanvasHelpTitle').innerText = data.article.title;
                    document.getElementById('offcanvasHelpBodyContent').innerHTML = data.article.content_html;
                    if (content) content.style.display = 'block';
                }
            })
            .catch(err => {
                if (spinner) spinner.style.display = 'none';
            });
    });

    // Ajax Search inside Offcanvas
    const offcanvasSearchInput = document.getElementById('offcanvasSearchInput');
    const offcanvasSearchResults = document.getElementById('offcanvasSearchResults');

    if (offcanvasSearchInput) {
        let debounceTimer;
        offcanvasSearchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            const query = this.value.trim();
            if (query.length < 2) {
                offcanvasSearchResults.style.display = 'none';
                return;
            }

            debounceTimer = setTimeout(() => {
                fetch(`/help/api/search?q=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success && data.results.length > 0) {
                            offcanvasSearchResults.innerHTML = data.results.map(art => `
                                <a href="/help/${art.category_slug}/${art.slug}" class="list-group-item list-group-item-action py-2 px-3 small">
                                    <div class="fw-bold text-dark">${art.title}</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">${art.category}</div>
                                </a>
                            `).join('');
                            offcanvasSearchResults.style.display = 'block';
                        } else {
                            offcanvasSearchResults.innerHTML = `<div class="p-2 text-center text-muted small">No guides found</div>`;
                            offcanvasSearchResults.style.display = 'block';
                        }
                    });
            }, 300);
        });
    }
});
</script>
@endpush
