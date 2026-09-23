@extends('layouts.dashboard')

@section('title', __('ui.whatsapp_chat') . ' | Noubtigo')
@section('header_title', __('ui.whatsapp_chat'))
@section('header_subtitle', __('ui.whatsapp_subtitle'))

@section('content')
<div class="row g-0 rounded-4 overflow-hidden border shadow-sm" style="height: calc(100vh - 250px); min-height: 500px; background: white;">
    <!-- Sidebar: Conversation List -->
    <div class="col-md-4 col-lg-3 border-end d-flex flex-column" style="background: #f8f9fa;">
        <div class="p-3 border-bottom bg-white d-flex align-items-center justify-content-between">
            <h5 class="mb-0 fw-bold">{{ __('ui.conversations') }}</h5>
            <span class="badge bg-success rounded-pill">{{ $conversations->count() }}</span>
        </div>
        
        <div class="flex-grow-1 overflow-y-auto overflow-x-hidden">
            @forelse($conversations as $conv)
                <a href="{{ route('whatsapp.chat.show', $conv->phone) }}" 
                   class="d-flex align-items-center p-3 text-decoration-none transition-all hover-light border-bottom {{ isset($phone) && $phone == $conv->phone ? 'bg-light border-start border-4 border-success' : '' }}">
                    <div class="me-3 position-relative">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($conv->customer->full_name ?? $conv->phone) }}&background=22c55e&color=fff" 
                             class="rounded-circle" width="45" alt="Avatar">
                    </div>
                    <div class="flex-grow-1 min-width-0">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <h6 class="mb-0 fw-bold text-dark text-truncate">{{ $conv->customer->full_name ?? $conv->phone }}</h6>
                            <small class="text-secondary x-small">{{ $conv->last_activity->diffForHumans(null, true) }}</small>
                        </div>
                        <p class="mb-0 text-secondary text-truncate small">
                            @if($conv->last_message->direction == 'outbound')
                                <i class="bi bi-check2-all text-info me-1"></i>
                            @endif
                            {{ $conv->last_message->message }}
                        </p>
                    </div>
                </a>
            @empty
                <div class="text-center p-5">
                    <i class="bi bi-chat-dots fs-1 text-secondary opacity-25"></i>
                    <p class="text-secondary mt-2">{{ __('ui.no_conversations') }}</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Chat Area -->
    <div class="col-md-8 col-lg-9 d-flex flex-column bg-white h-100">
        @yield('chat_content')
        
        @if(!View::hasSection('chat_content'))
            <div class="flex-grow-1 d-flex flex-column align-items-center justify-content-center text-center p-5 bg-light-subtle">
                <div class="rounded-circle bg-white shadow-sm p-4 mb-4" style="width: 120px; height: 120px; display: flex; align-items: center; justify-content: center;">
                    <i class="bi bi-whatsapp text-success display-4"></i>
                </div>
                <h3 class="fw-bold">{{ __('ui.whatsapp_chat') }}</h3>
                <p class="text-secondary max-width-400 mx-auto">
                    {{ __('ui.select_conversation') }}<br>
                    {{ __('ui.window_expired_notice') }}
                </p>
                <div class="mt-4 p-3 rounded-3 border bg-white d-inline-flex align-items-center gap-3">
                    <div class="text-start">
                        <small class="text-secondary d-block">{{ __('ui.company_code_label') }}</small>
                        <span class="fw-bold fs-5 text-dark">{{ auth()->user()->company->code ?? 'N/A' }}</span>
                    </div>
                    <button class="btn btn-sm btn-light border" onclick="navigator.clipboard.writeText('{{ auth()->user()->company->code ?? '' }}')">
                        <i class="bi bi-copy"></i>
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>

<style>
    .hover-light:hover {
        background-color: #f1f3f5 !important;
    }
    .transition-all {
        transition: all 0.2s ease-in-out;
    }
    .x-small {
        font-size: 0.75rem;
    }
    .max-width-400 {
        max-width: 400px;
    }
    .border-success {
        border-color: #22c55e !important;
    }
    /* Simple custom scrollbar */
    ::-webkit-scrollbar {
        width: 6px;
    }
    ::-webkit-scrollbar-track {
        background: transparent;
    }
    ::-webkit-scrollbar-thumb {
        background: #dee2e6;
        border-radius: 10px;
    }
    ::-webkit-scrollbar-thumb:hover {
        background: #ced4da;
    }
</style>

@push('scripts')
<script>
    // Auto refresh every 60 seconds
    setInterval(() => {
        location.reload();
    }, 60000);
</script>
@endpush
@endsection
