{{-- Peak Hours Heatmap (7 days × 24 hours) --}}
@php
    $dayLabels = [
        1 => __('ui.day_label_sun'),
        2 => __('ui.day_label_mon'),
        3 => __('ui.day_label_tue'),
        4 => __('ui.day_label_wed'),
        5 => __('ui.day_label_thu'),
        6 => __('ui.day_label_fri'),
        7 => __('ui.day_label_sat'),
    ];

    // Find max value for color scaling
    $maxVal = 0;
    foreach ($heatmap as $dayData) {
        $maxVal = max($maxVal, max($dayData));
    }
@endphp

<div class="heatmap-grid py-2">
    {{-- Header row: hour labels --}}
    <div class="heatmap-label"></div>
    @for($h = 0; $h < 24; $h++)
        <div class="heatmap-header">{{ $h }}</div>
    @endfor

    {{-- Data rows: one per day --}}
    @foreach($dayLabels as $dow => $label)
        <div class="heatmap-label">{{ $label }}</div>
        @for($h = 0; $h < 24; $h++)
            @php
                $val = $heatmap[$dow][$h] ?? 0;
                $intensity = $maxVal > 0 ? $val / $maxVal : 0;
                // Color gradient: transparent → green → yellow → red
                if ($intensity == 0) {
                    $bg = 'rgba(241,245,249,0.5)';
                    $textColor = '#cbd5e1';
                } elseif ($intensity < 0.33) {
                    $bg = "rgba(34,197,94," . (0.15 + $intensity) . ")";
                    $textColor = '#166534';
                } elseif ($intensity < 0.66) {
                    $bg = "rgba(245,158,11," . (0.2 + $intensity * 0.6) . ")";
                    $textColor = '#92400e';
                } else {
                    $bg = "rgba(239,68,68," . (0.3 + $intensity * 0.5) . ")";
                    $textColor = '#fff';
                }
            @endphp
            <div class="heatmap-cell" style="background:{{ $bg }};color:{{ $textColor }}" title="{{ $label }} {{ $h }}:00 — {{ $val }} {{ __('ui.total_tickets') }}">
                {{ $val > 0 ? $val : '' }}
            </div>
        @endfor
    @endforeach
</div>

<div class="d-flex align-items-center gap-2 mt-2 justify-content-end" style="font-size:.7rem">
    <span class="text-secondary">{{ __('ui.queue_is_empty') }}</span>
    <div style="width:14px;height:14px;border-radius:3px;background:rgba(34,197,94,.25)"></div>
    <div style="width:14px;height:14px;border-radius:3px;background:rgba(245,158,11,.5)"></div>
    <div style="width:14px;height:14px;border-radius:3px;background:rgba(239,68,68,.7)"></div>
    <span class="text-secondary">{{ __('ui.peak_hours') }}</span>
</div>
