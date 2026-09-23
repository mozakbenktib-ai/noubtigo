@extends('layouts.dashboard')

@section('title', 'Noubtigo | Invoices')
@section('header_title', 'Invoice Management')
@section('header_subtitle', 'View all generated invoices across tenants')

@push('styles')
<style>
.inv-table-wrapper {
    background: white; border-radius: 1rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    border: 1px solid rgba(0,0,0,0.05); overflow: hidden;
}
.inv-table { width: 100%; border-collapse: collapse; }
.inv-table thead th {
    background: #f8fafc; padding: 0.85rem 1.25rem;
    font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;
    font-weight: 700; color: #94a3b8; border-bottom: 1px solid #e2e8f0;
}
.inv-table tbody tr { border-bottom: 1px solid #f1f5f9; }
.inv-table tbody tr:hover { background: rgba(34,197,94,0.02); }
.inv-table tbody tr:last-child { border-bottom: none; }
.inv-table tbody td { padding: 0.75rem 1.25rem; font-size: 0.85rem; color: #334155; vertical-align: middle; }
.inv-status { display:inline-flex;align-items:center;padding:.25rem .65rem;border-radius:2rem;font-size:.7rem;font-weight:600;text-transform:uppercase; }
.inv-status.paid { background:#dcfce7;color:#16a34a; }
.inv-status.open { background:#fef9c3;color:#854d0e; }
.inv-status.draft { background:#f1f5f9;color:#64748b; }
.inv-status.void { background:#fef2f2;color:#dc2626; }
[data-bs-theme="dark"] .inv-table-wrapper { background:#1e293b; }
[data-bs-theme="dark"] .inv-table thead th { background:#0f172a;color:#64748b; }
[data-bs-theme="dark"] .inv-table tbody td { color:#e2e8f0; }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">

    <form class="d-flex flex-wrap gap-2 mb-4" method="GET" action="{{ route('admin.invoices.index') }}">
        <select class="form-select form-select-sm rounded-3" name="status" style="width:160px">
            <option value="">All Statuses</option>
            @foreach(['draft','open','paid','void','uncollectible'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-sm px-3 text-white fw-semibold rounded-3" style="background:var(--primary-gradient)">
            <i class="bi bi-funnel-fill me-1"></i> Filter
        </button>
    </form>

    <div class="inv-table-wrapper">
        @if($invoices->isEmpty())
            <div class="text-center py-5">
                <i class="bi bi-receipt d-block mb-3" style="font-size:2.5rem;opacity:.3"></i>
                <p class="text-muted mb-0">No invoices found</p>
            </div>
        @else
            <table class="inv-table">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Company</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Due Date</th>
                        <th>Paid At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoices as $invoice)
                    <tr>
                        <td><span class="fw-semibold text-primary small font-monospace">{{ $invoice->invoice_number }}</span></td>
                        <td>{{ $invoice->company->name ?? '—' }}</td>
                        <td>
                            <span class="fw-bold">{{ number_format($invoice->total, 2) }}</span>
                            <span class="text-muted small">{{ $invoice->currency }}</span>
                        </td>
                        <td><span class="inv-status {{ $invoice->status }}">{{ ucfirst($invoice->status) }}</span></td>
                        <td>{{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : '—' }}</td>
                        <td>{{ $invoice->paid_at ? $invoice->paid_at->format('M d, Y') : '—' }}</td>
                        <td>
                            <a href="{{ route('admin.invoices.show', $invoice) }}" class="btn btn-sm btn-outline-primary rounded-3 px-2 py-1">
                                <i class="bi bi-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    @if($invoices->hasPages())
        <div class="d-flex justify-content-center mt-4">{{ $invoices->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
