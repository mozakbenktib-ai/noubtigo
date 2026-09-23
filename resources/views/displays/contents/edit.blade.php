@extends('layouts.dashboard')

@section('title', __('ui.edit_display_content') . ' | Noubtigo')
@section('header_title', __('ui.edit_display_content'))
@section('header_subtitle', __('ui.create_display_content_desc'))

@section('content')
<div class="container-fluid px-0" style="max-width: 980px;">

    <div class="d-flex align-items-center justify-content-between mb-4">
        <a href="{{ route('displays.contents.index') }}" class="btn btn-light rounded-pill px-3 fw-semibold text-secondary">
            <i class="bi bi-arrow-left me-1"></i> {{ __('ui.back_to_contents') }}
        </a>
    </div>

    <form action="{{ route('displays.contents.update', $content->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- 1. CONTENT INFORMATION CARD -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-2 me-3">
                        <i class="bi bi-file-earmark-richtext-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0">{{ __('ui.content_details_step') }}</h5>
                        <p class="text-secondary small mb-0">{{ __('ui.content_details_desc') }}</p>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <!-- Content Type Selector -->
                <div class="mb-4">
                    <label class="form-label small fw-bold text-uppercase text-secondary">{{ __('ui.content_type') }} <span class="text-danger">*</span></label>
                    <div class="row g-3">
                        @foreach($types as $key => $t)
                            <div class="col-md-3 col-6">
                                <label class="w-100 h-100 cursor-pointer">
                                    <input type="radio" name="type" value="{{ $key }}" class="btn-check" id="type_{{ $key }}" 
                                           {{ old('type', $content->type) === $key ? 'checked' : '' }} onchange="onTypeChanged('{{ $key }}')">
                                    <div class="card border rounded-4 p-3 text-center h-100 content-type-card transition-all">
                                        <div class="mb-2">
                                            <i class="bi {{ $t['icon'] }} fs-3 text-primary"></i>
                                        </div>
                                        <div class="fw-bold text-dark mb-1">{{ $t['name'] }}</div>
                                        <div class="text-secondary x-small">{{ $t['description'] }}</div>
                                    </div>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Title -->
                <div class="mb-3">
                    <label class="form-label small fw-bold text-uppercase text-secondary">{{ __('ui.title_headline') }} <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control form-control-lg rounded-3 @error('title') is-invalid @enderror" 
                           placeholder="{{ __('ui.headline_placeholder') }}" 
                           value="{{ old('title', $content->title) }}" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Description -->
                <div class="mb-3">
                    <label class="form-label small fw-bold text-uppercase text-secondary">{{ __('ui.description_body') }}</label>
                    <textarea name="description" class="form-control rounded-3 @error('description') is-invalid @enderror" rows="3" 
                              placeholder="{{ __('ui.description_placeholder') }}">{{ old('description', $content->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Image Upload with Existing Preview -->
                <div class="mb-3" id="image-upload-section">
                    <label class="form-label small fw-bold text-uppercase text-secondary">{{ __('ui.visual_banner_image') }}</label>
                    <div class="d-flex align-items-start gap-3">
                        <div class="image-preview-box border rounded-3 d-flex align-items-center justify-content-center bg-light overflow-hidden" 
                             style="width: 140px; height: 95px;" id="previewContainer">
                            @if($content->image_url)
                                <img src="{{ $content->image_url }}" id="imagePreview" class="w-100 h-100 object-fit-cover">
                                <i class="bi bi-image text-muted fs-3 d-none" id="previewPlaceholder"></i>
                            @else
                                <i class="bi bi-image text-muted fs-3" id="previewPlaceholder"></i>
                                <img src="" id="imagePreview" class="w-100 h-100 object-fit-cover d-none">
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            <input type="file" name="image" id="imageInput" class="form-control rounded-3 @error('image') is-invalid @enderror" 
                                   accept="image/jpeg,image/png,image/webp,image/gif" onchange="previewSelectedImage(this)">
                            <div class="form-text small">{{ __('ui.image_hint') }}</div>
                            
                            @if($content->image_path)
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="remove_image" value="1" id="remove_image">
                                    <label class="form-check-label text-danger small" for="remove_image">
                                        <i class="bi bi-trash me-1"></i> {{ __('ui.remove_existing_image') }}
                                    </label>
                                </div>
                            @endif
                            <button type="button" class="btn btn-link btn-sm text-danger p-0 mt-1 d-none" id="clearImageBtn" onclick="clearSelectedImage()">
                                <i class="bi bi-x-circle me-1"></i> {{ __('ui.cancel_selection') }}
                            </button>
                            @error('image')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Optional Link -->
                <div class="mb-3" id="link-section">
                    <label class="form-label small fw-bold text-uppercase text-secondary">{{ __('ui.optional_link_url') }}</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 rounded-start-3"><i class="bi bi-link-45deg"></i></span>
                        <input type="url" name="link_url" class="form-control rounded-end-3" placeholder="https://example.com/promotion" value="{{ old('link_url', $content->link_url) }}">
                    </div>
                    <div class="form-text small">{{ __('ui.link_hint') }}</div>
                </div>

                <!-- QR / Tracking Specific URL -->
                <div class="mb-3" id="qr-section">
                    <label class="form-label small fw-bold text-uppercase text-secondary">{{ __('ui.qr_destination_url') }}</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 rounded-start-3"><i class="bi bi-qr-code"></i></span>
                        <input type="url" name="qr_url" class="form-control rounded-end-3" placeholder="https://..." value="{{ old('qr_url', $content->qr_url) }}">
                    </div>
                    <div class="form-text small text-primary">
                        <i class="bi bi-info-circle me-1"></i> {{ __('ui.qr_hint') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. DISPLAY TARGET CARD -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                <div class="d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 text-success rounded-3 p-2 me-3">
                        <i class="bi bi-tv-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0">{{ __('ui.display_target_step') }}</h5>
                        <p class="text-secondary small mb-0">{{ __('ui.display_target_desc') }}</p>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <!-- Radio Selector: All Displays vs Selected Displays -->
                <div class="mb-3">
                    <div class="d-flex gap-4">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="target_type" id="target_all" value="all" 
                                   {{ old('target_type', $content->target_type) === 'all' ? 'checked' : '' }} onchange="toggleTargetDisplays(false)">
                            <label class="form-check-label fw-bold" for="target_all">
                                <i class="bi bi-globe me-1 text-primary"></i> {{ __('ui.target_all_displays') }}
                                <div class="text-secondary small fw-normal">{{ __('ui.target_all_desc') }}</div>
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="target_type" id="target_selected" value="selected" 
                                   {{ old('target_type', $content->target_type) === 'selected' ? 'checked' : '' }} onchange="toggleTargetDisplays(true)">
                            <label class="form-check-label fw-bold" for="target_selected">
                                <i class="bi bi-pin-map-fill me-1 text-success"></i> {{ __('ui.target_selected_displays') }}
                                <div class="text-secondary small fw-normal">{{ __('ui.target_selected_desc') }}</div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Multi-select Checklist of Company's Displays -->
                <div id="selected-displays-box" class="border rounded-4 p-3 bg-light bg-opacity-50 mt-3 {{ old('target_type', $content->target_type) === 'selected' ? '' : 'd-none' }}">
                    <label class="form-label small fw-bold text-uppercase text-secondary mb-2">
                        {{ __('ui.select_target_screens') }}
                    </label>
                    @if($devices->isEmpty())
                        <div class="text-muted small py-2">
                            <i class="bi bi-exclamation-circle me-1"></i> {{ __('ui.no_displays_paired_yet') }}
                            <a href="{{ route('displays.index') }}" target="_blank">{{ __('ui.pair_display_first') }}</a>.
                        </div>
                    @else
                        <div class="row g-2">
                            @foreach($devices as $device)
                                <div class="col-md-6">
                                    <div class="form-check p-3 bg-white border rounded-3 shadow-sm d-flex align-items-center">
                                        <input class="form-check-input ms-0 me-3" type="checkbox" name="device_ids[]" 
                                               value="{{ $device->id }}" id="device_{{ $device->id }}"
                                               {{ in_array($device->id, old('device_ids', $assignedDeviceIds)) ? 'checked' : '' }}>
                                        <label class="form-check-label w-100 cursor-pointer" for="device_{{ $device->id }}">
                                            <div class="fw-bold text-dark d-flex align-items-center justify-content-between">
                                                <span>{{ $device->name }}</span>
                                                @if($device->room)
                                                    <span class="badge bg-info bg-opacity-10 text-info px-2 py-1 rounded-pill small">
                                                        {{ $device->room->name }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary bg-opacity-10 text-secondary px-2 py-1 rounded-pill small">
                                                        {{ __('ui.global_room') }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-muted x-small">Device ID: {{ substr($device->uid, 0, 8) }}...</div>
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- 3. TIMING, SCHEDULE & PRIORITY CARD -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                <div class="d-flex align-items-center">
                    <div class="bg-warning bg-opacity-10 text-warning rounded-3 p-2 me-3">
                        <i class="bi bi-clock-history fs-5"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0">{{ __('ui.display_timing_step') }}</h5>
                        <p class="text-secondary small mb-0">{{ __('ui.display_timing_desc') }}</p>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <div class="row g-3">
                    <!-- Duration -->
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-uppercase text-secondary">{{ __('ui.rotation_duration_sec') }} <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="duration" class="form-control rounded-start-3" min="3" max="300" 
                                   value="{{ old('duration', $content->duration) }}" id="durationInput" required>
                            <span class="input-group-text bg-light rounded-end-3">{{ __('ui.sec') }}</span>
                        </div>
                        <div class="d-flex gap-1 mt-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-2 py-0 x-small" onclick="setDuration(5)">5s</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-2 py-0 x-small" onclick="setDuration(10)">10s</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-2 py-0 x-small" onclick="setDuration(15)">15s</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-2 py-0 x-small" onclick="setDuration(30)">30s</button>
                        </div>
                    </div>

                    <!-- Sort Order -->
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-uppercase text-secondary">{{ __('ui.sort_order') }}</label>
                        <input type="number" name="sort_order" class="form-control rounded-3" min="0" value="{{ old('sort_order', $content->sort_order) }}">
                        <div class="form-text small">{{ __('ui.sort_order_hint') }}</div>
                    </div>

                    <!-- Priority -->
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-uppercase text-secondary">{{ __('ui.priority_level') }}</label>
                        <select name="priority" class="form-select rounded-3">
                            <option value="normal" {{ old('priority', $content->priority) === 'normal' ? 'selected' : '' }}>{{ __('ui.priority_normal') }}</option>
                            <option value="high" {{ old('priority', $content->priority) === 'high' ? 'selected' : '' }}>{{ __('ui.priority_high') }}</option>
                            <option value="emergency" {{ old('priority', $content->priority) === 'emergency' ? 'selected' : '' }}>{{ __('ui.priority_emergency') }}</option>
                        </select>
                        <div class="form-text small">{{ __('ui.priority_hint') }}</div>
                    </div>

                    <!-- Scheduling: Starts At -->
                    <div class="col-md-6 mt-3">
                        <label class="form-label small fw-bold text-uppercase text-secondary">{{ __('ui.starts_at_optional') }}</label>
                        <input type="datetime-local" name="starts_at" class="form-control rounded-3" value="{{ old('starts_at', $localStartsAt) }}">
                        <div class="form-text small">{{ __('ui.company_timezone') }}: <strong>{{ app(\App\Services\TimezoneService::class)->resolve() }}</strong>. {{ __('ui.leave_blank_immediate') }}</div>
                    </div>

                    <!-- Scheduling: Ends At -->
                    <div class="col-md-6 mt-3">
                        <label class="form-label small fw-bold text-uppercase text-secondary">{{ __('ui.ends_at_optional') }}</label>
                        <input type="datetime-local" name="ends_at" class="form-control rounded-3" value="{{ old('ends_at', $localEndsAt) }}">
                        <div class="form-text small">{{ __('ui.ends_at_hint') }}</div>
                    </div>

                    <!-- Active Toggle -->
                    <div class="col-12 mt-3">
                        <div class="form-check form-switch p-0">
                            <label class="form-check-label fw-bold text-dark d-flex align-items-center gap-3 cursor-pointer" for="is_active">
                                <input class="form-check-input ms-0 float-none" type="checkbox" name="is_active" value="1" id="is_active" 
                                       {{ old('is_active', $content->is_active) ? 'checked' : '' }} style="width: 2.5em; height: 1.3em;">
                                <span>{{ __('ui.publish_on_screens') }}</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit & Actions -->
        <div class="d-flex align-items-center justify-content-end gap-3 mb-5">
            <a href="{{ route('displays.contents.index') }}" class="btn btn-light rounded-pill px-4 py-2 fw-semibold">{{ __('ui.cancel') }}</a>
            <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow-sm">
                <i class="bi bi-check-circle-fill me-1"></i> {{ __('ui.update_display_content') }}
            </button>
        </div>
    </form>
</div>

<style>
.cursor-pointer { cursor: pointer; }
.content-type-card {
    transition: all 0.2s ease;
}
.btn-check:checked + .content-type-card {
    border-color: #22c55e !important;
    background: rgba(34, 197, 94, 0.08) !important;
    box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.3);
}
</style>

<script>
function onTypeChanged(type) {
    const qrSection = document.getElementById('qr-section');
    if (type === 'qr_tracking') {
        if (qrSection) qrSection.classList.remove('d-none');
    } else {
        if (qrSection) qrSection.classList.add('d-none');
    }
}

function toggleTargetDisplays(isSpecific) {
    const box = document.getElementById('selected-displays-box');
    if (box) {
        if (isSpecific) {
            box.classList.remove('d-none');
        } else {
            box.classList.add('d-none');
        }
    }
}

function previewSelectedImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('imagePreview');
            const placeholder = document.getElementById('previewPlaceholder');
            const clearBtn = document.getElementById('clearImageBtn');

            if (preview) {
                preview.src = e.target.result;
                preview.classList.remove('d-none');
            }
            if (placeholder) placeholder.classList.add('d-none');
            if (clearBtn) clearBtn.classList.remove('d-none');
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function clearSelectedImage() {
    const input = document.getElementById('imageInput');
    const preview = document.getElementById('imagePreview');
    const placeholder = document.getElementById('previewPlaceholder');
    const clearBtn = document.getElementById('clearImageBtn');

    if (input) input.value = '';
    // if original existed, reset
    @if($content->image_url)
        if (preview) preview.src = '{{ $content->image_url }}';
    @else
        if (preview) {
            preview.src = '';
            preview.classList.add('d-none');
        }
        if (placeholder) placeholder.classList.remove('d-none');
    @endif
    if (clearBtn) clearBtn.classList.add('d-none');
}

function setDuration(sec) {
    const input = document.getElementById('durationInput');
    if (input) input.value = sec;
}

// Initial setup
document.addEventListener('DOMContentLoaded', () => {
    const selectedType = document.querySelector('input[name="type"]:checked');
    if (selectedType) onTypeChanged(selectedType.value);
});
</script>
@endsection
