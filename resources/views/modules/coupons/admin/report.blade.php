@php
    $usages = $usages ?? collect();
    $coupons = $coupons ?? collect();
    $stats = $stats ?? [
        'total' => $coupons->count(),
        'active' => $coupons->where('status.value', 'active')->count(),
        'total_redemptions' => $usages->count(),
        'total_discounts' => $usages->sum('discount_amount'),
    ];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Coupons Performance Report</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            line-height: 1.5;
            margin: 0;
            padding: 40px;
        }
        .header {
            border-bottom: 2px solid #eaebed;
            padding-bottom: 20px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #1a1f36;
        }
        .header .meta {
            text-align: right;
            font-size: 12px;
            color: #697386;
        }
        .stats-grid {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            flex: 1;
            border: 1px solid #e3e8ee;
            border-radius: 8px;
            padding: 15px;
            background: #f7fafc;
        }
        .stat-title {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #697386;
            margin-bottom: 5px;
        }
        .stat-value {
            font-size: 20px;
            font-weight: bold;
            color: #1a1f36;
        }
        h2 {
            font-size: 16px;
            color: #1a1f36;
            margin-top: 30px;
            margin-bottom: 15px;
            border-bottom: 1px solid #e3e8ee;
            padding-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            font-size: 12px;
        }
        th, td {
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid #e3e8ee;
        }
        th {
            background-color: #f7fafc;
            color: #4f566b;
            font-weight: 600;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-active { background-color: #e3fcf1; color: #0a8554; }
        .badge-disabled { background-color: #f4f6f8; color: #637381; }
        .badge-archived { background-color: #e9ecef; color: #495057; }
        @media print {
            body { padding: 0; }
            button { display: none; }
        }
    </style>
</head>
<body>
    <div style="text-align: right; margin-bottom: 20px;">
        <button onclick="window.print();" style="padding: 8px 16px; font-weight: bold; border-radius: 4px; border: 1px solid #d9d9d9; background: #fff; cursor: pointer;">Print Report</button>
    </div>

    <div class="header">
        <div>
            <h1>Coupons Performance Report</h1>
            <div style="font-size: 14px; color: #4f566b; margin-top: 5px;">Noubtigo SaaS Platform</div>
        </div>
        <div class="meta">
            <div>Generated: {{ now()->format('Y-m-d H:i') }} UTC</div>
            <div>Target: System Administrator</div>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-title">Total Coupons</div>
            <div class="stat-value">{{ $stats['total'] ?? $coupons->count() }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-title">Active Coupons</div>
            <div class="stat-value">{{ $stats['active'] ?? 0 }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-title">Total Redemptions</div>
            <div class="stat-value">{{ $stats['total_redemptions'] ?? $usages->count() }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-title">Total Discounts Given</div>
            <div class="stat-value">{{ number_format($stats['total_discounts'] ?? 0, 2) }} MAD</div>
        </div>
    </div>

    <h2>Coupon Performance Metrics</h2>
    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Name</th>
                <th>Type</th>
                <th>Value</th>
                <th>Status</th>
                <th>Uses</th>
                <th>Expires</th>
            </tr>
        </thead>
        <tbody>
            @foreach($coupons as $coupon)
            <tr>
                <td><strong>{{ $coupon->code }}</strong></td>
                <td>{{ $coupon->name }}</td>
                <td>{{ $coupon->type?->label() ?? '—' }}</td>
                <td>
                    @if(($coupon->type?->value ?? '') === 'percentage' || ($coupon->type?->value ?? '') === 'lifetime')
                        {{ $coupon->value }}%
                    @elseif(($coupon->type?->value ?? '') === 'fixed' || ($coupon->type?->value ?? '') === 'custom_price')
                        {{ number_format($coupon->value, 2) }} MAD
                    @elseif(($coupon->type?->value ?? '') === 'free_subscription')
                        {{ (int)$coupon->value }} mo free
                    @elseif(($coupon->type?->value ?? '') === 'trial_extension')
                        +{{ (int)$coupon->value }} days
                    @else
                        —
                    @endif
                </td>
                <td>
                    <span class="badge badge-{{ $coupon->status?->value ?? 'active' }}">
                        {{ $coupon->status?->label() ?? 'Active' }}
                    </span>
                </td>
                <td>{{ $coupon->current_uses }} / {{ $coupon->is_unlimited ? '∞' : ($coupon->max_total_uses ?? '∞') }}</td>
                <td>{{ $coupon->expires_at ? \Carbon\Carbon::parse($coupon->expires_at)->format('Y-m-d') : 'Never' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Recent Redemptions & Usages</h2>
    <table>
        <thead>
            <tr>
                <th>Coupon Code</th>
                <th>Company (Tenant)</th>
                <th>Billing Cycle</th>
                <th>Discount Amount</th>
                <th>Date / Time</th>
            </tr>
        </thead>
        <tbody>
            @forelse($usages as $usage)
            <tr>
                <td><strong>{{ $usage->coupon?->code ?? 'Deleted Coupon' }}</strong></td>
                <td>{{ $usage->company?->name ?? 'Unknown Company' }}</td>
                <td>{{ ucfirst($usage->billing_cycle ?? 'both') }}</td>
                <td>{{ number_format($usage->discount_amount ?? 0, 2) }} MAD</td>
                <td>{{ $usage->created_at ? \Carbon\Carbon::parse($usage->created_at)->format('Y-m-d H:i') : '—' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; color: #697386;">No redemptions found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
