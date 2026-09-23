<!-- Modal: Add Customer -->
<div class="modal fade" id="addCustomerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="fw-bold mb-0">{{ __('ui.add_customer') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form onsubmit="handleCustomerStore(event)">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label small fw-bold">{{ __('ui.first_name') }}</label>
                            <input type="text" class="form-control rounded-3" name="first_name" required placeholder="John">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label small fw-bold">{{ __('ui.last_name') }}</label>
                            <input type="text" class="form-control rounded-3" name="last_name" placeholder="Doe">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">{{ __('ui.identifier_cin') }}</label>
                        <input type="text" class="form-control rounded-3" name="identifier" placeholder="CIN / File Number">
                    </div>
                    <div class="mb-3 d-flex flex-column">
                        <label class="form-label small fw-bold">{{ __('ui.phone_number') }}</label>
                        <input type="tel" class="form-control rounded-3 w-100" id="add_phone" name="phone">
                        <div class="invalid-feedback text-danger small mt-1" id="add_phone_error" style="display: none;"></div>
                    </div>
                    
                    <div class="alert alert-warning border-0 rounded-3 small p-2 mb-4 d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-triangle-fill flex-shrink-0 text-warning"></i>
                        <span>{{ __('ui.no_phone_warning') }}</span>
                    </div>

                    <button type="submit" class="btn btn-success w-100 fw-bold py-2 shadow-sm rounded-3">{{ __('ui.save_customer') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Edit Customer -->
<div class="modal fade" id="editCustomerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="fw-bold mb-0">{{ __('ui.edit_customer') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form onsubmit="handleCustomerUpdate(event)">
                    <input type="hidden" id="edit_customer_id">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label small fw-bold">{{ __('ui.first_name') }}</label>
                            <input type="text" class="form-control rounded-3" id="edit_first_name" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label small fw-bold">{{ __('ui.last_name') }}</label>
                            <input type="text" class="form-control rounded-3" id="edit_last_name">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">{{ __('ui.identifier_cin') }}</label>
                        <input type="text" class="form-control rounded-3" id="edit_identifier">
                    </div>
                    <div class="mb-3 d-flex flex-column">
                        <label class="form-label small fw-bold">{{ __('ui.phone_number') }}</label>
                        <input type="tel" class="form-control rounded-3 w-100" id="edit_phone">
                        <div class="invalid-feedback text-danger small mt-1" id="edit_phone_error" style="display: none;"></div>
                    </div>

                    <div class="alert alert-warning border-0 rounded-3 small p-2 mb-4 d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-triangle-fill flex-shrink-0 text-warning"></i>
                        <span>{{ __('ui.no_phone_warning') }}</span>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-bold py-2 shadow-sm rounded-3">{{ __('ui.update_customer') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
