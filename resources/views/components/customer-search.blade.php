@props([
    'id' => 'customer_search',
    'name' => 'customer_id',
    'value' => '',
    'placeholder' => '',
    'required' => false,
    'prefillName' => '',
    'prefillPhone' => '',
    'prefillEmail' => '',
    'onchange' => ''
])

@php
    $placeholder = $placeholder ?: __('ui.search_customer_placeholder');
@endphp

<div class="position-relative customer-search-wrapper" id="{{ $id }}-wrapper">
    <input type="hidden" name="{{ $name }}" id="{{ $id }}" value="{{ $value }}" @if($required) required @endif>
    
    <div class="input-group">
        <span class="input-group-text bg-light border-end-0 text-secondary">
            <i class="bi bi-search"></i>
        </span>
        <input type="text" 
               class="form-control dispenser-input border-start-0 shadow-none customer-search-input" 
               id="{{ $id }}-input" 
               placeholder="{{ $placeholder }}" 
               value="{{ $prefillName }}" 
               autocomplete="off">
        <button class="btn btn-outline-secondary border-start-0 d-none clear-search-btn" type="button" id="{{ $id }}-clear">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <!-- Dropdown list -->
    <div class="dropdown-menu shadow-lg w-100 p-0 overflow-hidden customer-search-dropdown border" 
         id="{{ $id }}-dropdown" 
         style="max-height: 300px; overflow-y: auto; display: none; z-index: 1050; position: absolute; margin-top: 4px;">
        <div class="dropdown-items-list" id="{{ $id }}-results">
            <!-- Results will be loaded here dynamically -->
        </div>
        <div class="dropdown-loading text-center py-3 text-muted d-none" id="{{ $id }}-loading">
            <span class="spinner-border spinner-border-sm me-2" role="status"></span>
            <small>{{ __('ui.loading') ?? 'Loading...' }}</small>
        </div>
        <div class="p-2 border-top bg-light text-center quick-create-trigger-wrapper d-none" id="{{ $id }}-quick-create-wrap">
            <button class="btn btn-sm btn-outline-primary w-100 py-2 border-dashed quick-create-btn" type="button">
                <i class="bi bi-plus-lg me-1"></i> {{ __('ui.create_new_customer') }}
            </button>
        </div>
    </div>
</div>

<style>
    .customer-search-wrapper {
        font-family: inherit;
    }
    .customer-search-dropdown {
        border-radius: 12px;
        background: var(--bs-body-bg, #fff);
    }
    .customer-search-item {
        padding: 10px 16px;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        cursor: pointer;
        transition: background-color 0.2s ease;
    }
    .customer-search-item:last-child {
        border-bottom: none;
    }
    .customer-search-item:hover, .customer-search-item.active {
        background-color: rgba(59, 130, 246, 0.08);
    }
    [data-bs-theme="dark"] .customer-search-item:hover, [data-bs-theme="dark"] .customer-search-item.active {
        background-color: rgba(59, 130, 246, 0.2);
    }
    [data-bs-theme="dark"] .customer-search-dropdown {
        background-color: #1e293b;
        border-color: #334155;
    }
    .customer-badge {
        font-size: 0.7rem;
        padding: 3px 6px;
        border-radius: 4px;
        font-weight: 600;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const wrapper = document.getElementById('{{ $id }}-wrapper');
    const input = document.getElementById('{{ $id }}-input');
    const hidden = document.getElementById('{{ $id }}');
    const dropdown = document.getElementById('{{ $id }}-dropdown');
    const resultsContainer = document.getElementById('{{ $id }}-results');
    const loadingEl = document.getElementById('{{ $id }}-loading');
    const clearBtn = document.getElementById('{{ $id }}-clear');
    const quickCreateWrap = document.getElementById('{{ $id }}-quick-create-wrap');
    
    let searchTimeout = null;
    let currentPage = 1;
    let hasMore = false;
    let isLoading = false;
    let query = '';

    // Track state for click-outside
    document.addEventListener('click', function(e) {
        if (!wrapper.contains(e.target)) {
            closeDropdown();
        }
    });

    input.addEventListener('focus', function() {
        if (query.length > 0 || resultsContainer.children.length > 0) {
            openDropdown();
        } else {
            // Load initial list
            currentPage = 1;
            fetchResults();
        }
    });

    input.addEventListener('input', function() {
        query = this.value.trim();
        currentPage = 1;
        hidden.value = ''; // Clear selected ID if user starts typing
        
        if (query.length > 0) {
            clearBtn.classList.remove('d-none');
        } else {
            clearBtn.classList.add('d-none');
        }

        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            fetchResults();
        }, 300);
    });

    clearBtn.addEventListener('click', function() {
        input.value = '';
        hidden.value = '';
        query = '';
        resultsContainer.innerHTML = '';
        clearBtn.classList.add('d-none');
        closeDropdown();
        triggerChange('', null);
    });

    // Infinite scroll loading
    dropdown.addEventListener('scroll', function() {
        if (dropdown.scrollTop + dropdown.clientHeight >= dropdown.scrollHeight - 20) {
            if (hasMore && !isLoading) {
                currentPage++;
                fetchResults(true);
            }
        }
    });

    // Trigger quick create modal
    quickCreateWrap.querySelector('.quick-create-btn').addEventListener('click', function() {
        closeDropdown();
        if (window.openQuickCreateModal) {
            window.openQuickCreateModal(function(newCustomer) {
                selectCustomer(newCustomer);
            });
        } else {
            console.error('Quick create callback not loaded.');
        }
    });

    function openDropdown() {
        dropdown.style.display = 'block';
        quickCreateWrap.classList.remove('d-none');
    }

    function closeDropdown() {
        dropdown.style.display = 'none';
    }

    function fetchResults(append = false) {
        isLoading = true;
        loadingEl.classList.remove('d-none');
        openDropdown();

        const searchUrl = `{{ route('customers.ajax-search') }}?q=${encodeURIComponent(query)}&page=${currentPage}&limit=10`;

        fetch(searchUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            isLoading = false;
            loadingEl.classList.add('d-none');
            
            if (!append) {
                resultsContainer.innerHTML = '';
            }

            hasMore = data.pagination.more;

            if (data.results.length === 0 && !append) {
                resultsContainer.innerHTML = `
                    <div class="text-center py-4 text-muted small">
                        <i class="bi bi-person-x fs-4 d-block mb-1"></i>
                        {{ __('ui.no_customers_found') }}
                    </div>`;
            } else {
                data.results.forEach(customer => {
                    const item = document.createElement('div');
                    item.className = 'customer-search-item';
                    
                    // Format additional details if available
                    let details = [];
                    if (customer.phone) details.push(`<i class="bi bi-phone me-1"></i>${customer.phone}`);
                    if (customer.identifier) details.push(`<span class="customer-badge bg-secondary-subtle text-secondary border me-1">ID: ${customer.identifier}</span>`);
                    if (customer.cin) details.push(`<span class="customer-badge bg-primary-subtle text-primary border me-1">CIN: ${customer.cin}</span>`);
                    if (customer.file_number) details.push(`<span class="customer-badge bg-info-subtle text-info border me-1">File: ${customer.file_number}</span>`);
                    if (customer.plate_number) details.push(`<span class="customer-badge bg-warning-subtle text-warning border me-1">Plate: ${customer.plate_number}</span>`);

                    item.innerHTML = `
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="fw-bold text-dark customer-name-lbl">${customer.full_name}</div>
                        </div>
                        <div class="text-secondary small mt-1 d-flex flex-wrap align-items-center gap-2">
                            ${details.join(' ')}
                        </div>`;
                    
                    item.addEventListener('click', function() {
                        selectCustomer(customer);
                    });

                    resultsContainer.appendChild(item);
                });
            }
        })
        .catch(err => {
            isLoading = false;
            loadingEl.classList.add('d-none');
            console.error('Customer search error:', err);
        });
    }

    function selectCustomer(customer) {
        if (customer && !customer.full_name) {
            customer.full_name = (customer.first_name + ' ' + (customer.last_name || '')).trim();
        }
        input.value = customer ? customer.full_name : '';
        hidden.value = customer ? customer.id : '';
        clearBtn.classList.remove('d-none');
        closeDropdown();
        triggerChange(customer ? customer.id : '', customer);
    }

    function triggerChange(val, customer) {
        // Run standard onchange callback if provided
        const callbackStr = '{{ $onchange }}';
        if (callbackStr) {
            // Create a fake element or pass the customer values directly
            const fakeSelect = {
                value: val,
                options: [
                    {
                        dataset: {
                            name: customer ? customer.full_name : '',
                            phone: customer ? (customer.phone || '') : '',
                            email: customer ? (customer.email || '') : ''
                        }
                    }
                ],
                selectedIndex: 0
            };
            
            // Execute function by name if it exists in scope
            if (typeof window[callbackStr] === 'function') {
                window[callbackStr](fakeSelect);
            } else {
                try {
                    // Try to evaluate inline code
                    const fn = new Function('el', callbackStr);
                    fn(fakeSelect);
                } catch(e) {
                    console.error('Failed to run onchange callback:', e);
                }
            }
        }

        // Dispatch native input & change events on the hidden input for any listener
        hidden.dispatchEvent(new Event('input', { bubbles: true }));
        hidden.dispatchEvent(new Event('change', { bubbles: true }));
    }

    // Expose select method globally or bound to wrapper for external programmatical selection
    wrapper.selectCustomer = selectCustomer;
});
</script>
