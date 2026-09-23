@extends('layouts.dashboard')

@section('title', 'Noubtigo | Invoice ' . $invoice->invoice_number)
@section('header_title', 'Invoice')
@section('header_subtitle', $invoice->invoice_number)

@push('styles')
<style>
@media print {
    .sidebar, .top-navbar, .bottom-nav, .no-print { display: none !important; }
    .main-content { margin: 0 !important; }
}
</style>
@endpush

@section('content')
<div class="container-fluid px-0">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                {{-- Invoice Header with gradient --}}
                <div class="card-header p-4 d-flex justify-content-between align-items-start" style="background:var(--primary-gradient)">
                    <div>
                        <p class="text-white opacity-75 mb-1 small text-uppercase fw-semibold">Invoice</p>
                        <h3 class="fw-bold text-white mb-0">{{ $invoice->invoice_number }}</h3>
                    </div>
                    <span class="badge text-uppercase fw-semibold px-3 py-2 rounded-pill
                        {{ $invoice->status === 'paid' ? 'bg-success' : ($invoice->status === 'open' ? 'bg-warning text-dark' : 'bg-secondary') }}">
                        {{ ucfirst($invoice->status) }}
                    </span>
                </div>

                <div class="card-body p-4">
                    {{-- Billed To / Dates --}}
                    <div class="row mb-4">
                        <div class="col-6">
                            <p class="text-muted mb-1 small text-uppercase fw-semibold">Billed To</p>
                            <h6 class="fw-bold mb-0">{{ $invoice->company->name ?? '—' }}</h6>
                            <p class="text-muted small mb-0">{{ $invoice->company->email ?? '' }}</p>
                        </div>
                        <div class="col-6 text-end">
                            <p class="text-muted mb-1 small text-uppercase fw-semibold">Invoice Date</p>
                            <p class="fw-semibold mb-1">{{ $invoice->created_at->format('M d, Y') }}</p>
                            <p class="text-muted small mb-0">Due: {{ $invoice->due_date?->format('M d, Y') ?? 'N/A' }}</p>
                        </div>
                    </div>

                    {{-- Line Items --}}
                    <div class="table-responsive mb-4">
                        <table class="table table-borderless">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-muted small text-uppercase fw-semibold">Description</th>
                                    <th class="text-muted small text-uppercase fw-semibold text-center">Qty</th>
                                    <th class="text-muted small text-uppercase fw-semibold text-end">Unit Price</th>
                                    <th class="text-muted small text-uppercase fw-semibold text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoice->items as $item)
                                <tr>
                                    <td class="fw-medium">{{ $item->description }}</td>
                                    <td class="text-center text-muted">{{ $item->quantity }}</td>
                                    <td class="text-end">{{ number_format($item->unit_price, 2) }} {{ $invoice->currency }}</td>
                                    <td class="text-end fw-semibold">{{ number_format($item->total, 2) }} {{ $invoice->currency }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Totals --}}
                    <div class="d-flex justify-content-end mb-4">
                        <div style="min-width:260px">
                            <div class="d-flex justify-content-between mb-1 text-muted small">
                                <span>Subtotal</span>
                                <span>{{ number_format($invoice->subtotal, 2) }} {{ $invoice->currency }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2 text-muted small">
                                <span>Tax</span>
                                <span>{{ number_format($invoice->tax, 2) }} {{ $invoice->currency }}</span>
                            </div>
                            <div class="d-flex justify-content-between fw-bold fs-5 border-top pt-2">
                                <span>Total</span>
                                <span class="text-success">{{ number_format($invoice->total, 2) }} {{ $invoice->currency }}</span>
                            </div>
                        </div>
                    </div>

                    @if($invoice->paid_at)
                    <div class="alert alert-success border-0 rounded-3 d-flex align-items-center gap-2 no-print">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Payment received on {{ $invoice->paid_at->format('M d, Y') }}</span>
                    </div>
                    @endif

                    <div class="d-flex justify-content-between mt-3 no-print">
                        <a href="{{ route('billing.index') }}" class="btn btn-outline-secondary rounded-3 px-4">
                            <i class="bi bi-arrow-left me-1"></i> Back to Billing
                        </a>
                        <button onclick="window.print()" class="btn rounded-3 px-4 text-white fw-semibold" style="background:var(--primary-gradient)">
                            <i class="bi bi-printer me-1"></i> Print Invoice
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
