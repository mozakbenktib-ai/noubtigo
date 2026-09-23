<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="bg-light">
            <tr>
                <th class="ps-4 sortable cursor-pointer text-nowrap" data-sort="customer" style="cursor: pointer;">
                    {{ __('ui.customer') }}
                    <span class="sort-icon" id="sort-icon-customer"><i class="bi bi-arrow-down-up ms-1 text-muted small"></i></span>
                </th>
                <th class="sortable cursor-pointer text-nowrap" data-sort="identifier" style="cursor: pointer;">
                    {{ __('ui.identifier_cin') }}
                    <span class="sort-icon" id="sort-icon-identifier"><i class="bi bi-arrow-down-up ms-1 text-muted small"></i></span>
                </th>
                <th class="sortable cursor-pointer text-nowrap" data-sort="phone" style="cursor: pointer;">
                    {{ __('ui.phone_number') }}
                    <span class="sort-icon" id="sort-icon-phone"><i class="bi bi-arrow-down-up ms-1 text-muted small"></i></span>
                </th>
                <th class="sortable cursor-pointer text-nowrap" data-sort="tickets_count" style="cursor: pointer;">
                    {{ __('ui.total_visits') }}
                    <span class="sort-icon" id="sort-icon-tickets_count"><i class="bi bi-arrow-down-up ms-1 text-muted small"></i></span>
                </th>
                <th class="sortable cursor-pointer text-nowrap" data-sort="last_visit" style="cursor: pointer;">
                    {{ __('ui.last_visit') }}
                    <span class="sort-icon" id="sort-icon-last_visit"><i class="bi bi-arrow-down-up ms-1 text-muted small"></i></span>
                </th>
                <th class="text-end pe-4 text-nowrap">{{ __('ui.actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($customers as $customer)
            <tr>
                <td class="ps-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar-circle shadow-sm" style="background: linear-gradient(135deg, #22c55e 0%, #06b6d4 100%);">
                            {{ strtoupper(substr($customer->first_name, 0, 1)) }}{{ strtoupper(substr($customer->last_name ?: 'G', 0, 1)) }}
                        </div>
                        <div>
                            <h6 class="mb-0 fw-semibold">{{ $customer->full_name }}</h6>
                            <span class="text-muted small">{{ $customer->email ?? __('ui.no_email') }}</span>
                        </div>
                    </div>
                </td>
                <td><span class="badge bg-light text-dark border">{{ $customer->identifier ?: '—' }}</span></td>
                <td>{{ $customer->phone ?? __('ui.no_phone') }}</td>
                <td class="text-center">{{ $customer->tickets_count ?? 0 }}</td>
                <td class="small text-muted">
                    {{ $customer->tickets->first() ? $customer->tickets->first()->created_at->diffForHumans() : '—' }}
                </td>
                <td class="text-end pe-4">
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm rounded-3" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg p-2" style="border-radius: 12px;">
                            <li><a class="dropdown-item rounded-2" href="{{ route('customers.show', $customer) }}"><i class="bi bi-eye text-info me-2"></i> {{ __('ui.view_details') }}</a></li>
                            @permission('customers.edit')
                            <li><button class="dropdown-item rounded-2" onclick="openEditModal({{ json_encode($customer) }})"><i class="bi bi-pencil-square text-primary me-2"></i> {{ __('ui.edit') }}</button></li>
                            @endpermission
                            <div class="dropdown-divider"></div>
                            @permission('customers.delete')
                            <li><button class="dropdown-item rounded-2 text-danger" onclick="deleteCustomer({{ $customer->id }})"><i class="bi bi-trash3 me-2"></i> {{ __('ui.delete') }}</button></li>
                            @endpermission
                        </ul>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="py-5 text-center">
                    <i class="bi bi-people text-muted display-4 d-block mb-3"></i>
                    <p class="text-muted">{{ __('ui.no_customers_yet') }}</p>
                    @permission('customers.create')
                    <button class="btn btn-gradient px-4" data-bs-toggle="modal" data-bs-target="#addCustomerModal">{{ __('ui.add_first_customer') }}</button>
                    @endpermission
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="p-4 border-top" id="pagination-wrapper">
    {{ $customers->links('pagination::bootstrap-5') }}
</div>
