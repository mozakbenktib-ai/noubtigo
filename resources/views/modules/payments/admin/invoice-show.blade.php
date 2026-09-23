@extends('layouts.dashboard')

@section('title', 'Noubtigo | Invoice ' . $invoice->invoice_number)
@section('header_title', 'Invoice Details')
@section('header_subtitle', 'Invoice ' . $invoice->invoice_number)

@section('content')
<div class="container-fluid px-0">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                {{-- Header --}}
                <div class="card-header p-4 d-flex justify-content-between align-items-start" style="background:var(--primary-gradient)">
                    <div>
                        <h4 class="fw-bold text-white mb-1">{{ $invoice->invoice_number }}</h4>
                        <p class="text-white opacity-75 mb-0 small">
                            Issued {{ $invoice->created_at->format('M d, Y') }}
                        </p>
                    </div>
                    <span class="badge text-uppercase fw-semibold px-3 py-2 rounded-pill
                        {{ $invoice->status === 'paid' ? 'bg-success' : ($invoice->status === 'open' ? 'bg-warning text-dark' : 'bg-secondary') }}">
                        {{ ucfirst($invoice->status) }}
                    </span>
                </div>

                <div class="card-body p-4">
                    {{-- Company Info --}}
                    <div class="row mb-4">
                        <div class="col-6">
                            <p class="text-muted mb-1 small text-uppercase fw-semibold">Billed To</p>
                            <h6 class="fw-bold mb-0">{{ $invoice->company->name ?? '—' }}</h6>
                            <p class="text-muted small mb-0">{{ $invoice->company->email ?? '' }}</p>
                        </div>
                        <div class="col-6 text-end">
                            <p class="text-muted mb-1 small text-uppercase fw-semibold">Due Date</p>
                            <h6 class="fw-bold mb-0">{{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'N/A' }}</h6>
                            @if($invoice->paid_at)
                                <p class="text-success small mb-0"><i class="bi bi-check-circle-fill me-1"></i>Paid {{ $invoice->paid_at->format('M d, Y') }}</p>
                            @endif
                        </div>
                    </div>

                    {{-- Items --}}
                    <div class="table-responsive mb-4">
                        <table class="table table-borderless">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-muted small text-uppercase">Description</th>
                                    <th class="text-muted small text-uppercase text-center">Qty</th>
                                    <th class="text-muted small text-uppercase text-end">Unit Price</th>
                                    <th class="text-muted small text-uppercase text-end">Total</th>
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
                    <div class="d-flex justify-content-end mb-3">
                        <div style="min-width:260px">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Subtotal</span>
                                <span>{{ number_format($invoice->subtotal, 2) }} {{ $invoice->currency }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Tax</span>
                                <span>{{ number_format($invoice->tax, 2) }} {{ $invoice->currency }}</span>
                            </div>
                            <div class="d-flex justify-content-between fw-bold border-top pt-2" style="font-size:1.1rem">
                                <span>Total</span>
                                <span class="text-success">{{ number_format($invoice->total, 2) }} {{ $invoice->currency }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 justify-content-end mt-4">
                        <a href="{{ route('admin.invoices.index') }}" class="btn btn-outline-secondary rounded-3 px-4">
                            <i class="bi bi-arrow-left me-1"></i> Back
                        </a>
                        <button onclick="window.print()" class="btn rounded-3 px-4 text-white fw-semibold" style="background:var(--primary-gradient)">
                            <i class="bi bi-printer me-1"></i> Print
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
