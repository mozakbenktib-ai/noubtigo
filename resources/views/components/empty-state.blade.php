@props([
    'icon' => 'bi-folder-x',
    'title' => 'No Data Found',
    'message' => 'There are no items created yet.',
    'actionUrl' => null,
    'actionText' => 'Create New',
    'helpArticleUrl' => route('help.index')
])

<div class="card border-0 shadow-sm rounded-4 text-center py-5 my-3">
    <div class="card-body p-4">
        <div class="bg-primary-subtle text-primary rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 64px; height: 64px;">
            <i class="bi {{ $icon }} fs-2"></i>
        </div>
        <h5 class="fw-bold text-dark mb-2">{{ $title }}</h5>
        <p class="text-muted small mx-auto mb-4" style="max-width: 420px; line-height: 1.5;">{{ $message }}</p>
        
        <div class="d-flex align-items-center justify-content-center gap-2">
            @if($actionUrl)
                <a href="{{ $actionUrl }}" class="btn btn-primary text-white rounded-pill px-4 fw-semibold">
                    <i class="bi bi-plus-lg me-1"></i> {{ $actionText }}
                </a>
            @endif
            @if($helpArticleUrl)
                <a href="{{ $helpArticleUrl }}" class="btn btn-light rounded-pill px-3 text-secondary fw-semibold">
                    <i class="bi bi-question-circle me-1"></i> Learn How
                </a>
            @endif
        </div>
    </div>
</div>
