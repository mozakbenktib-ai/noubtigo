@extends('layouts.dashboard')

@section('title', 'Noubtigo | ' . __('ui.tickets_history'))
@section('header_title', __('ui.tickets_history'))
@section('header_subtitle', __('ui.tickets_history_subtitle'))

@push('styles')
<link rel="stylesheet" href="{{ asset('frontend/css/queue.css') }}">
<style>
/* ── History Page Styles ─────────────────────────────────────────────── */
.history-stats-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.history-stat-card {
    background: white;
    border-radius: 1rem;
    padding: 1.25rem 1.5rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    border: 1px solid rgba(0,0,0,0.05);
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all 0.3s ease;
}

.history-stat-card:hover {
    box-shadow: 0 4px 16px rgba(34,197,94,0.12);
    transform: translateY(-2px);
}

.history-stat-card .stat-icon-wrap {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    flex-shrink: 0;
}

.history-stat-card .stat-icon-wrap.active { background: rgba(34,197,94,0.1); color: #22c55e; }
.history-stat-card .stat-icon-wrap.completed { background: rgba(6,182,212,0.1); color: #06b6d4; }
.history-stat-card .stat-icon-wrap.cancelled { background: rgba(239,68,68,0.1); color: #ef4444; }

.history-stat-card .stat-value { font-size: 1.6rem; font-weight: 800; color: #1e293b; line-height: 1; }
.history-stat-card .stat-title { font-size: 0.75rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }

/* ── Tab Navigation ──────────────────────────────────────────────────── */
.history-tabs {
    background: white;
    border-radius: 1rem;
    padding: 0.5rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    display: inline-flex;
    gap: 0.25rem;
    margin-bottom: 1.5rem;
}

.history-tab {
    padding: 0.6rem 1.25rem;
    border-radius: 0.75rem;
    font-weight: 600;
    font-size: 0.85rem;
    color: #64748b;
    text-decoration: none;
    transition: all 0.25s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    border: none;
    background: none;
    cursor: pointer;
}

.history-tab:hover { background: #f1f5f9; color: #334155; }
.history-tab.active { background: var(--primary-gradient, linear-gradient(135deg, #22c55e, #06b6d4)); color: white; }
.history-tab .tab-count {
    font-size: 0.7rem;
    padding: 0.1rem 0.45rem;
    border-radius: 1rem;
    font-weight: 700;
}
.history-tab.active .tab-count { background: rgba(255,255,255,0.25); }
.history-tab:not(.active) .tab-count { background: #f1f5f9; color: #64748b; }

/* ── History Table ───────────────────────────────────────────────────── */
.history-table-wrapper {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    border: 1px solid rgba(0,0,0,0.05);
    overflow: hidden;
}

.history-table {
    width: 100%;
    border-collapse: collapse;
}

.history-table thead th {
    background: #f8fafc;
    padding: 0.85rem 1.25rem;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 700;
    color: #94a3b8;
    border-bottom: 1px solid #e2e8f0;
    white-space: nowrap;
}

.history-table tbody tr {
    border-bottom: 1px solid #f1f5f9;
    transition: all 0.2s ease;
    cursor: pointer;
}

.history-table tbody tr:hover {
    background: rgba(34,197,94,0.03);
}

.history-table tbody tr:last-child { border-bottom: none; }

.history-table tbody td {
    padding: 0.85rem 1.25rem;
    font-size: 0.9rem;
    color: #334155;
    vertical-align: middle;
}

/* ── Status Badges ───────────────────────────────────────────────────── */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.25rem 0.65rem;
    border-radius: 2rem;
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.3px;
    text-transform: uppercase;
}
.status-badge.waiting { background: #f1f5f9; color: #64748b; }
.status-badge.called { background: #dbeafe; color: #2563eb; }
.status-badge.serving { background: #ffedd5; color: #ea580c; }
.status-badge.done { background: #dcfce7; color: #16a34a; }
.status-badge.cancelled { background: #fef2f2; color: #dc2626; }
.status-badge.no_show { background: #fef9c3; color: #ca8a04; }

/* ── Search & Filters ────────────────────────────────────────────────── */
.history-filters {
    display: flex;
    gap: 0.75rem;
    align-items: center;
    flex-wrap: wrap;
    margin-bottom: 1.5rem;
}

.history-filters .form-control,
.history-filters .form-select {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 0.75rem;
    padding: 0.55rem 1rem;
    font-size: 0.85rem;
    transition: all 0.2s;
}

.history-filters .form-control:focus,
.history-filters .form-select:focus {
    border-color: #22c55e;
    box-shadow: 0 0 0 3px rgba(34,197,94,0.1);
}

@media (max-width: 768px) {
    .history-stats-row { grid-template-columns: 1fr; }
    .history-tabs { flex-wrap: wrap; }
}

[data-bs-theme="dark"] .history-stat-card,
[data-bs-theme="dark"] .history-table-wrapper,
[data-bs-theme="dark"] .history-tabs { background: #1e293b; border-color: rgba(255,255,255,0.05); }
[data-bs-theme="dark"] .history-table thead th { background: #0f172a; color: #64748b; border-color: rgba(255,255,255,0.05); }
[data-bs-theme="dark"] .history-table tbody tr { border-color: rgba(255,255,255,0.03); }
[data-bs-theme="dark"] .history-table tbody td { color: #e2e8f0; }
[data-bs-theme="dark"] .history-stat-card .stat-value { color: #f1f5f9; }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">

    {{-- ── Stats Row ─────────────────────────────────────────────────────── --}}
    <div class="history-stats-row fade-in">
        <div class="history-stat-card">
            <div class="stat-icon-wrap active"><i class="bi bi-ticket-perforated-fill"></i></div>
            <div>
                <div class="stat-value" id="stats-active-val">{{ $stats['active'] }}</div>
                <div class="stat-title">{{ __('ui.active_tickets') }}</div>
            </div>
        </div>
        <div class="history-stat-card">
            <div class="stat-icon-wrap completed"><i class="bi bi-check-circle-fill"></i></div>
            <div>
                <div class="stat-value" id="stats-completed-val">{{ $stats['completed'] }}</div>
                <div class="stat-title">{{ __('ui.completed') }}</div>
            </div>
        </div>
        <div class="history-stat-card">
            <div class="stat-icon-wrap cancelled"><i class="bi bi-x-circle-fill"></i></div>
            <div>
                <div class="stat-value" id="stats-cancelled-val">{{ $stats['cancelled'] }}</div>
                <div class="stat-title">{{ __('ui.cancelled') }}</div>
            </div>
        </div>
    </div>

    {{-- ── Tabs & Filters ────────────────────────────────────────────────── --}}
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
        <div class="history-tabs">
            <button class="history-tab tab-trigger active" data-tab="active">
                <i class="bi bi-lightning-charge-fill"></i> {{ __('ui.active') }}
                <span class="tab-count" id="tab-count-active">{{ $stats['active'] }}</span>
            </button>
            <button class="history-tab tab-trigger" data-tab="completed">
                <i class="bi bi-check-circle-fill"></i> {{ __('ui.completed') }}
                <span class="tab-count" id="tab-count-completed">{{ $stats['completed'] }}</span>
            </button>
            <button class="history-tab tab-trigger" data-tab="cancelled">
                <i class="bi bi-x-circle-fill"></i> {{ __('ui.cancelled') }}
                <span class="tab-count" id="tab-count-cancelled">{{ $stats['cancelled'] }}</span>
            </button>
        </div>

        {{-- ── Dynamic Filters ────────────────────────────────────────────── --}}
        <div class="history-filters">
            <input type="text" class="form-control" id="filter-search" placeholder="{{ __('ui.search_tickets') }}..." style="width: 200px;">
            <input type="date" class="form-control" id="filter-date" style="width: 160px;">
            
            <select class="form-select" id="filter-service" style="width: 180px;">
                <option value="">{{ __('ui.all_services') }}</option>
                @foreach($services as $service)
                    <option value="{{ $service->id }}">{{ $service->name }}</option>
                @endforeach
            </select>

            <select class="form-select" id="filter-room" style="width: 160px;">
                <option value="">{{ __('ui.all_rooms') }}</option>
                @foreach($rooms as $room)
                    <option value="{{ $room->id }}">{{ $room->name }}</option>
                @endforeach
            </select>

            <button type="button" id="clear-filters" class="btn btn-sm btn-outline-secondary px-3 py-2 rounded-3 d-none">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </div>

    {{-- ── Table Container (Replaced Dynamically via AJAX) ────────────────── --}}
    <div id="history-table-container">
        @include('queue.partials.history-table')
    </div>

</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const state = {
            tab: 'active',
            search: '',
            date: '',
            service_id: '',
            room_id: '',
            sort_by: 'created_at',
            sort_order: 'desc',
            page: 1
        };

        const tableContainer = document.getElementById('history-table-container');
        const clearBtn = document.getElementById('clear-filters');

        // Inputs
        const searchInput = document.getElementById('filter-search');
        const dateInput = document.getElementById('filter-date');
        const serviceInput = document.getElementById('filter-service');
        const roomInput = document.getElementById('filter-room');

        // Debounce helper
        let searchTimeout;

        function updateClearButtonVisibility() {
            if (state.search || state.date || state.service_id || state.room_id) {
                clearBtn.classList.remove('d-none');
            } else {
                clearBtn.classList.add('d-none');
            }
        }

        function fetchHistory() {
            // Add a visual loading state
            tableContainer.style.opacity = '0.5';

            const queryParams = new URLSearchParams(state).toString();
            
            fetch(`{{ route('tickets.index') }}?${queryParams}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                tableContainer.style.opacity = '1';
                tableContainer.innerHTML = data.html;

                // Update Stats
                document.getElementById('stats-active-val').textContent = data.stats.active;
                document.getElementById('stats-completed-val').textContent = data.stats.completed;
                document.getElementById('stats-cancelled-val').textContent = data.stats.cancelled;

                document.getElementById('tab-count-active').textContent = data.stats.active;
                document.getElementById('tab-count-completed').textContent = data.stats.completed;
                document.getElementById('tab-count-cancelled').textContent = data.stats.cancelled;

                // Update Sort Icons
                updateSortHeaders();
                updateClearButtonVisibility();
            })
            .catch(err => {
                tableContainer.style.opacity = '1';
                console.error('Error fetching history:', err);
            });
        }

        function updateSortHeaders() {
            document.querySelectorAll('.sortable').forEach(th => {
                const sortField = th.dataset.sort;
                const iconContainer = th.querySelector('.sort-icon');
                
                if (sortField === state.sort_by) {
                    if (state.sort_order === 'asc') {
                        iconContainer.innerHTML = '<i class="bi bi-sort-up ms-1 text-primary small"></i>';
                    } else {
                        iconContainer.innerHTML = '<i class="bi bi-sort-down ms-1 text-primary small"></i>';
                    }
                } else {
                    iconContainer.innerHTML = '<i class="bi bi-arrow-down-up ms-1 text-muted small"></i>';
                }
            });
        }

        // Tab selection
        document.querySelectorAll('.tab-trigger').forEach(tabBtn => {
            tabBtn.addEventListener('click', function() {
                document.querySelectorAll('.tab-trigger').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                state.tab = this.dataset.tab;
                state.page = 1;
                fetchHistory();
            });
        });

        // Search Input change
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            state.search = this.value;
            state.page = 1;
            searchTimeout = setTimeout(fetchHistory, 300);
        });

        // Date select
        dateInput.addEventListener('change', function() {
            state.date = this.value;
            state.page = 1;
            fetchHistory();
        });

        // Service Select
        serviceInput.addEventListener('change', function() {
            state.service_id = this.value;
            state.page = 1;
            fetchHistory();
        });

        // Room Select
        roomInput.addEventListener('change', function() {
            state.room_id = this.value;
            state.page = 1;
            fetchHistory();
        });

        // Clear Filters
        clearBtn.addEventListener('click', function() {
            state.search = '';
            state.date = '';
            state.service_id = '';
            state.room_id = '';
            state.page = 1;

            searchInput.value = '';
            dateInput.value = '';
            serviceInput.value = '';
            roomInput.value = '';

            fetchHistory();
        });

        // Sort header click delegation
        document.addEventListener('click', function(e) {
            const th = e.target.closest('.sortable');
            if (th) {
                const sortField = th.dataset.sort;
                if (state.sort_by === sortField) {
                    state.sort_order = state.sort_order === 'asc' ? 'desc' : 'asc';
                } else {
                    state.sort_by = sortField;
                    state.sort_order = 'desc';
                }
                state.page = 1;
                fetchHistory();
            }
        });

        // Pagination link click delegation
        document.addEventListener('click', function(e) {
            const pageLink = e.target.closest('#pagination-wrapper a');
            if (pageLink) {
                e.preventDefault();
                const url = new URL(pageLink.href);
                const pageVal = url.searchParams.get('page');
                if (pageVal) {
                    state.page = parseInt(pageVal);
                    fetchHistory();
                    
                    // Smooth scroll back to table
                    document.getElementById('history-table-container').scrollIntoView({ behavior: 'smooth' });
                }
            }
        });

        // Initial setup of icons
        updateSortHeaders();
    });
</script>
@endpush
@endsection
