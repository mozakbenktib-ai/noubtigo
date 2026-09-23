<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="bg-light">
            <tr>
                @foreach($cols as $col)
                    <th class="{{ $loop->first ? 'ps-4' : '' }} {{ $loop->last ? 'text-end pe-4' : '' }}">
                        <div class="skeleton-text" style="width: 80px; height: 15px;"></div>
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @for($i = 0; $i < 5; $i++)
                <tr>
                    @foreach($cols as $col)
                        <td class="{{ $loop->first ? 'ps-4' : '' }} {{ $loop->last ? 'text-end pe-4' : '' }}">
                            <div class="d-flex align-items-center {{ $loop->last ? 'justify-content-end' : '' }}">
                                @if($loop->first && ($hasAvatar ?? false))
                                    <div class="skeleton-avatar me-3"></div>
                                @endif
                                <div class="skeleton-text" style="width: {{ rand(60, 120) }}px; height: 12px;"></div>
                            </div>
                        </td>
                    @endforeach
                </tr>
            @endfor
        </tbody>
    </table>
</div>

<style>
    .skeleton-text {
        background: #eee;
        background: linear-gradient(110deg, #ececec 8%, #f5f5f5 18%, #ececec 33%);
        border-radius: 4px;
        background-size: 200% 100%;
        animation: 1.5s shine linear infinite;
    }

    .skeleton-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #eee;
        background: linear-gradient(110deg, #ececec 8%, #f5f5f5 18%, #ececec 33%);
        background-size: 200% 100%;
        animation: 1.5s shine linear infinite;
    }

    @keyframes shine {
        to {
            background-position-x: -200%;
        }
    }
</style>
