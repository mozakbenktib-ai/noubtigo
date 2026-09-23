{{-- Contextual Help Modal Component --}}
<div class="modal fade" id="helpCenterModal" tabindex="-1" aria-labelledby="helpCenterModalLabel" aria-hidden="true" style="z-index: 1085;">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-2xl rounded-4 overflow-hidden">
            
            {{-- Modal Header --}}
            <div class="modal-header border-bottom px-4 py-3 text-white" style="background: var(--primary-gradient);">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-question-circle-fill fs-4"></i>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="helpCenterModalLabel">{{ __('ui.help_center') ?? 'Help & Quick Guide' }}</h5>
                        <small class="text-white-50" style="font-size: 0.75rem;">{{ __('ui.help_center_subtitle') ?? 'Contextual Assistance System' }}</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white shadow-none opacity-75 hover-opacity-100" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- Modal Body --}}
            <div class="modal-body p-4 bg-body text-body">
                
                {{-- Top Search Bar & Filters --}}
                <div class="row g-3 mb-4 align-items-center">
                    <div class="col-md-8 position-relative">
                        <div class="input-group rounded-pill overflow-hidden border shadow-sm">
                            <span class="input-group-text bg-body border-0 text-muted ps-3"><i class="bi bi-search fs-5"></i></span>
                            <input type="text" id="modalSearchInput" class="form-control border-0 shadow-none ps-2 bg-body text-body" placeholder="{{ __('ui.search_help') ?? 'Search guides & FAQs...' }}" autocomplete="off">
                        </div>
                        <div id="modalSearchResults" class="mt-2 list-group list-group-flush rounded-3 shadow-lg border position-absolute w-100" style="display: none; max-height: 280px; overflow-y: auto; z-index: 1050;"></div>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <a href="{{ route('help.index') }}" class="btn btn-outline-primary rounded-pill px-4 btn-sm fw-semibold">
                            <i class="bi bi-book {{ app()->getLocale() == 'ar' ? 'ms-1' : 'me-1' }}"></i> {{ __('ui.all_documentation') ?? 'Open Full Help Center' }}
                        </a>
                    </div>
                </div>

                {{-- Dynamic Article Content --}}
                <div id="modalArticleContainer" class="p-2">
                    <div class="text-center py-5" id="modalHelpSpinner">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading guide...</span>
                        </div>
                        <p class="text-muted small mt-2">Loading page guide...</p>
                    </div>

                    <div id="modalHelpContent" style="display: none;">
                        <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3">
                            <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 font-monospace fs-7" id="modalHelpCategory"></span>
                            <span class="text-muted small" id="modalHelpRoles"></span>
                        </div>
                        <h3 class="fw-bold text-body mb-3" id="modalHelpTitle"></h3>
                        <div class="markdown-body help-modal-markdown text-body" id="modalHelpBodyContent" style="line-height: 1.7; font-size: 0.98rem;"></div>
                    </div>
                </div>

            </div>

            {{-- Modal Footer --}}
            <div class="modal-footer border-top px-4 py-3 bg-body-tertiary d-flex align-items-center justify-content-between">
                <span class="text-muted small"><i class="bi bi-info-circle me-1"></i> Press ESC to close</span>
                <button type="button" class="btn btn-secondary rounded-pill px-4 btn-sm" data-bs-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>

<style>
/* Responsive Markdown styling for Large Help Modal */
.help-modal-markdown img {
    max-width: 100% !important;
    height: auto !important;
    border-radius: 14px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    margin: 1.25rem 0;
}
.help-modal-markdown table {
    width: 100% !important;
    margin: 1.25rem 0;
    border-collapse: collapse;
}
.help-modal-markdown table th,
.help-modal-markdown table td {
    padding: 0.75rem 1rem;
    border: 1px solid rgba(0,0,0,0.08);
}
[data-bs-theme="dark"] .help-modal-markdown table th,
[data-bs-theme="dark"] .help-modal-markdown table td {
    border-color: rgba(255,255,255,0.1);
}
.help-modal-markdown pre {
    background: rgba(0,0,0,0.04);
    padding: 1rem 1.25rem;
    border-radius: 10px;
    font-size: 0.85rem;
    overflow-x: auto;
}
[data-bs-theme="dark"] .ticket-stage,
[data-bs-theme="dark"] .lifecycle-branch {
    background: rgba(30, 41, 59, 0.8);
    border-color: rgba(255,255,255,0.1);
}
.ticket-lifecycle {
    margin: 1.25rem 0;
    padding: 1.4rem;
    border-radius: 18px;
    background: linear-gradient(135deg, rgba(6, 182, 212, 0.08), rgba(34, 197, 94, 0.08));
    border: 1px solid rgba(6, 182, 212, 0.2);
}
.ticket-lifecycle-main {
    display: flex;
    align-items: stretch;
    justify-content: center;
    gap: 0.45rem;
}
.ticket-stage {
    position: relative;
    flex: 1;
    min-width: 105px;
    padding: 1rem 0.55rem 0.75rem;
    text-align: center;
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 14px;
    background: #fff;
    box-shadow: 0 6px 16px rgba(15, 23, 42, 0.05);
    animation: lifecycleLift 0.55s ease both;
}
.ticket-stage:nth-of-type(3) { animation-delay: 0.1s; }
.ticket-stage:nth-of-type(5) { animation-delay: 0.2s; }
.ticket-stage:nth-of-type(7) { animation-delay: 0.3s; }
.ticket-stage:nth-of-type(9) { animation-delay: 0.4s; }
.ticket-stage > i { display: block; font-size: 1.45rem; margin-bottom: 0.35rem; }
.ticket-stage strong, .ticket-stage small { display: block; line-height: 1.25; }
.ticket-stage strong { font-size: 0.82rem; color: #0f172a; }
.ticket-stage small { margin-top: 0.2rem; color: #64748b; font-size: 0.67rem; }
.stage-number { position: absolute; top: -0.48rem; left: 0.45rem; width: 1.25rem; height: 1.25rem; display: grid; place-items: center; border-radius: 50%; background: #0ea5e9; color: #fff; font-size: 0.67rem; font-weight: 800; }
.stage-arrival > i { color: #0ea5e9; }.stage-waiting > i { color: #f59e0b; }.stage-called > i { color: #8b5cf6; }.stage-serving > i { color: #06b6d4; }.stage-complete > i { color: #22c55e; }
.stage-arrow { display: flex; align-items: center; color: #38bdf8; animation: lifecyclePulse 1.6s ease-in-out infinite; }
.ticket-lifecycle-branches { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-top: 1rem; }
.lifecycle-branch { display: flex; align-items: center; gap: 0.65rem; padding: 0.75rem; border-radius: 12px; background: rgba(255,255,255,0.75); border: 1px solid rgba(15,23,42,0.07); font-size: 0.75rem; }
.lifecycle-branch > i { font-size: 1.25rem; }.branch-absent > i { color: #f97316; }.branch-hold > i { color: #6366f1; }
.lifecycle-branch strong, .lifecycle-branch span { display: block; line-height: 1.35; }.lifecycle-branch span { color: #64748b; }
@keyframes lifecycleLift { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
@keyframes lifecyclePulse { 50% { transform: translateX(4px); color: #0ea5e9; } }
@media (max-width: 767px) { .ticket-lifecycle-main { flex-wrap: wrap; } .ticket-stage { flex: 0 1 calc(33.333% - 0.5rem); } .stage-arrow { display: none; } .ticket-lifecycle-branches { grid-template-columns: 1fr; } }
@media (prefers-reduced-motion: reduce) { .ticket-stage, .stage-arrow { animation: none; } }
[data-bs-theme="dark"] .help-modal-markdown pre {
    background: rgba(255,255,255,0.06);
}
.help-modal-markdown blockquote {
    border-left: 4px solid var(--primary-color, #22c55e);
    padding-left: 1rem;
    color: #64748b;
    margin: 1.25rem 0;
}
[dir="rtl"] .help-modal-markdown blockquote {
    border-left: none;
    border-right: 4px solid var(--primary-color, #22c55e);
    padding-left: 0;
    padding-right: 1rem;
}
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalEl = document.getElementById('helpCenterModal');
    if (!modalEl) return;

    const currentRouteName = "{{ Route::currentRouteName() ?? 'dashboard' }}";

    modalEl.addEventListener('show.bs.modal', function () {
        const spinner = document.getElementById('modalHelpSpinner');
        const content = document.getElementById('modalHelpContent');
        
        if (spinner) spinner.style.display = 'block';
        if (content) content.style.display = 'none';

        fetch(`/help/api/contextual?route_name=${encodeURIComponent(currentRouteName)}`)
            .then(res => res.json())
            .then(data => {
                if (spinner) spinner.style.display = 'none';
                if (data.success && data.article) {
                    document.getElementById('modalHelpCategory').innerText = data.article.category;
                    document.getElementById('modalHelpTitle').innerText = data.article.title;
                    document.getElementById('modalHelpBodyContent').innerHTML = data.article.content_html;
                    if (data.article.roles && data.article.roles.length) {
                        document.getElementById('modalHelpRoles').innerText = "For: " + data.article.roles.join(', ');
                    }
                    if (content) content.style.display = 'block';
                }
            })
            .catch(err => {
                if (spinner) spinner.style.display = 'none';
            });
    });

    // Ajax Search inside Modal
    const modalSearchInput = document.getElementById('modalSearchInput');
    const modalSearchResults = document.getElementById('modalSearchResults');

    if (modalSearchInput) {
        let debounceTimer;
        modalSearchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            const query = this.value.trim();
            if (query.length < 2) {
                modalSearchResults.style.display = 'none';
                return;
            }

            debounceTimer = setTimeout(() => {
                fetch(`/help/api/search?q=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success && data.results.length > 0) {
                            modalSearchResults.innerHTML = data.results.map(art => `
                                <a href="/help/${art.category_slug}/${art.slug}" class="list-group-item list-group-item-action py-2 px-3">
                                    <div class="fw-bold text-body">${art.title}</div>
                                    <div class="text-muted small" style="font-size: 0.75rem;">${art.category}</div>
                                </a>
                            `).join('');
                            modalSearchResults.style.display = 'block';
                        } else {
                            modalSearchResults.innerHTML = `<div class="p-2 text-center text-muted small">No guides found</div>`;
                            modalSearchResults.style.display = 'block';
                        }
                    });
            }, 300);
        });
    }
});
</script>
@endpush
