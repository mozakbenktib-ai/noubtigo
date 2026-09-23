<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="bg-light">
            <tr>
                <th class="ps-4 sortable cursor-pointer text-nowrap" data-sort="full_name" style="cursor: pointer;">
                    {{ __('ui.full_name') }}
                    <span class="sort-icon" id="sort-icon-full_name"><i class="bi bi-arrow-down-up ms-1 text-muted small"></i></span>
                </th>
                <th class="sortable cursor-pointer text-nowrap" data-sort="email" style="cursor: pointer;">
                    {{ __('ui.email') }}
                    <span class="sort-icon" id="sort-icon-email"><i class="bi bi-arrow-down-up ms-1 text-muted small"></i></span>
                </th>
                <th class="text-nowrap">{{ __('ui.role') }}</th>
                <th class="sortable cursor-pointer text-nowrap" data-sort="is_active" style="cursor: pointer;">
                    {{ __('ui.status') }}
                    <span class="sort-icon" id="sort-icon-is_active"><i class="bi bi-arrow-down-up ms-1 text-muted small"></i></span>
                </th>
                <th class="sortable cursor-pointer text-nowrap" data-sort="updated_at" style="cursor: pointer;">
                    {{ __('ui.last_activity') }}
                    <span class="sort-icon" id="sort-icon-updated_at"><i class="bi bi-arrow-down-up ms-1 text-muted small"></i></span>
                </th>
                <th class="text-end pe-4 text-nowrap">{{ __('ui.actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
            <tr>
                <td class="ps-4">
                    <div class="d-flex align-items-center gap-3">
                        <img src="{{ $user->avatar ?: 'https://ui-avatars.com/api/?name='.urlencode($user->full_name).'&background=22c55e&color=fff' }}" 
                             class="rounded-circle shadow-sm" width="40" height="40" alt="Avatar">
                        <div>
                            <h6 class="mb-0 fw-semibold">{{ $user->full_name }}</h6>
                            <span class="text-muted small">ID: #{{ $user->uuid }}</span>
                        </div>
                    </div>
                </td>
                <td>{{ $user->email }}</td>
                <td>
                    @foreach($user->roles as $role)
                        <span class="badge rounded-pill px-3 py-2 small fw-medium" 
                              style="background: rgba(34, 197, 94, 0.1); color: var(--primary-color); border: 1px solid var(--primary-color);">
                            {{ $role->name }}
                        </span>
                    @endforeach
                </td>
                <td>
                    @if($user->is_active)
                        <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill"><i class="bi bi-check-circle-fill me-1"></i> {{ __('ui.active') }}</span>
                    @else
                        <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill"><i class="bi bi-x-circle-fill me-1"></i> {{ __('ui.inactive') }}</span>
                    @endif
                </td>
                <td class="small text-muted">
                    {{ $user->updated_at->diffForHumans() }}
                </td>
                <td class="text-end pe-4">
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm rounded-3" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg p-2" style="border-radius: 12px;">
                            <li><a class="dropdown-item rounded-2" href="{{ route('rbac.users.show', $user) }}"><i class="bi bi-eye text-info me-2"></i> {{ __('ui.view_profile') }}</a></li>
                            <li><button class="dropdown-item rounded-2" onclick="openEditModal('{{ $user->id }}')"><i class="bi bi-pencil-square text-primary me-2"></i> {{ __('ui.edit') }}</button></li>
                            <li><a class="dropdown-item rounded-2" href="{{ route('activity-logs.index', ['user' => $user->uuid]) }}"><i class="bi bi-clock-history text-secondary me-2"></i> {{ __('ui.activity_logs') }}</a></li>
                            <div class="dropdown-divider"></div>
                            @if($user->is_active)
                                <li><button class="dropdown-item rounded-2 text-warning" onclick="toggleStatus('{{ $user->uuid }}', 0)"><i class="bi bi-person-x me-2"></i> {{ __('ui.deactivate') }}</button></li>
                            @else
                                <li><button class="dropdown-item rounded-2 text-success" onclick="toggleStatus('{{ $user->uuid }}', 1)"><i class="bi bi-person-check me-2"></i> {{ __('ui.active') }}</button></li>
                            @endif
                            @if(auth()->id() !== $user->id)
                                <li><button class="dropdown-item rounded-2 text-danger" onclick="confirmDelete('{{ $user->uuid }}')"><i class="bi bi-trash3 me-2"></i> {{ __('ui.delete') }}</button></li>
                            @endif
                        </ul>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="py-5 text-center">
                    <i class="bi bi-people text-muted display-4 d-block mb-3"></i>
                    <p class="text-muted">{{ __('ui.no_logs_found') }}</p>
                    <button class="btn btn-gradient px-4" data-bs-toggle="modal" data-bs-target="#addStaffModal">{{ __('ui.add_new_staff') }}</button>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="p-4 border-top" id="pagination-wrapper">
    {{ $users->links('pagination::bootstrap-5') }}
</div>
